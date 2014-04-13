<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

/*
在模型设计上我思考了很久，最终确定参考xiunophp的设计：统一 cache+db 接口，并设计了二级缓存，进一步减轻数据库压力。 2013.06.27
开启 cache 时：
1、读取：先读cache，缓存没有时读db，并写入cache。
2、写入：同时写入 cache 和 db。

对外开放的最常用的15个方法
cache + db + model:
 	$this->create();
 	$this->update();
 	$this->read();
 	$this->delete();

	$this->get();
	$this->set();
 	$this->truncate();
 	$this->maxid();
 	$this->count();

 	$this->find_fetch(); // 支持二级缓存
 	$this->find_fetch_key(); // 支持二级缓存
 	$this->find_update();
 	$this->find_delete();
 	$this->find_maxid();
 	$this->find_count();
*/

class model{
	// 每个模型都可以有自己的 db、cache 服务器
	//public $db_conf = array();
	//public $cache_conf = array();

	// 必须指定这三项
	public $table;			// 表名
	public $pri = array();	// 主键字段，如 ('cid'), ('cid', 'id')
	public $maxid;			// 自增字段

	// 避免重复链接
	static $dbs = array();
	static $caches = array();
	private $unique = array();	// 防止重复查询

	/**
	 * 创建一次 db/cache 对象
	 * @param string $var 只能是 db cache
	 * @return object
	 */
	function __get($var) {
		switch ($var) {
			case 'db':
				return $this->db = $this->load_db();
			case 'cache':
				return $this->cache = $this->load_cache();
			case 'db_conf':
				return $this->db_conf = &$_ENV['_config']['db'];
			case 'cache_conf':
				return $this->cache_conf = &$_ENV['_config']['cache'];
			default:
				return $this->$var = core::model($var);
		}
	}

	/**
	 * 未定义的模型方法抛出异常
	 * @param string $method 不存在的方法名
	 */
	function __call($method, $args) {
		throw new Exception("方法 $method 不存在");
	}

	/**
	 * 加载 db 对象
	 * @return object
	 */
	public function load_db() {
		$type = $this->db_conf['type'];
		if(isset($this->db_conf['master'])) {
			$m = $this->db_conf['master'];
			$id = $type.'-'.$m['host'].'-'.$m['user'].'-'.$m['password'].'-'.$m['name'].'-'.$m['tablepre'];
		}else{
			$id = $type;
		}

		if(isset(self::$dbs[$id])) {
			return self::$dbs[$id];
		}else{
			$db = 'db_'.$type;
			self::$dbs[$id] = new $db($this->db_conf);
			return self::$dbs[$id];
		}
	}

	/**
	 * 加载 cache 对象
	 * @return object
	 */
	public function load_cache() {
		$type = $this->cache_conf['type'];

		if(isset($this->cache_conf[$type])) {
			$c = $this->cache_conf[$type];
			$id = $type.'-'.$c['host'].'-'.$c['port'];
		}else{
			$id = $type;
		}

		if(isset(self::$caches[$id])) {
			return self::$caches[$id];
		}else{
			$cache = 'cache_'.$type;
			self::$caches[$id] = new $cache($this->cache_conf);
			return self::$caches[$id];
		}
	}

	// +---------------------------------------------------------------
	// | cache + db + 模型 封装方法，所有符合标准的表结构都可以使用以下方法
	// +---------------------------------------------------------------
	/**
	 * 创建一条数据
	 * @param array $data	数据 (注意：不要包含自增字段)
	 * @return boot
	 */
	public function create($data) {
		// 如果没有自增字段，则不统计 count() maxid()
		if(empty($this->maxid)) {
			$key = $this->pri2key($data);
			return $this->cache_db_set($key, $data);
		}else{
			// 注意：因为考虑了多种情况，更换顺序或简化代码会造成问题
			$data[$this->maxid] = $this->maxid('+1');
			$key = $this->pri2key($data);
			$this->count('+1');
			if($this->cache_db_set($key, $data)) {
				return $data[$this->maxid];
			}else{
				$this->maxid('-1');
				$this->count('-1');
				return FALSE;
			}
		}
	}

	/**
	 * 写入一条数据
	 * @param array $key	键名数组
	 * @param array $data	数据
	 * @param int  $life	缓存时间 (默认为永久)
	 * @return bool
	 */
	/*
		此接口中的 $key 参数格式不同于 cache, db 中的 set()
		例子：
		$this->user->set(1, array('username'=>'2b', 'password'=>'123'));
		$this->user->set(array(1, 2), array('username'=>'2b', 'password'=>'123'));
	*/
	public function set($key, $data, $life = 0) {
		$key = $this->arr2key($key);
		$this->unique[$key] = $data;
		return $this->cache_db_set($key, $data, $life);
	}

	/**
	 * 读取一条数据 (简化数组, 如: read(1,2,3,4) 表示 get(array(1,2,3,4)) 最多支持4个参数)
	 * @param string $arg1-$arg4 参数1-参数4
	 * @return array
	 */
	public function read($arg1, $arg2 = FALSE, $arg3 = FALSE, $arg4 = FALSE) {
		$arr = ($arg2 !== FALSE) ? $this->arg2arr($arg1, $arg2, $arg3, $arg4) : (array)$arg1;
		return $this->get($arr);
	}

	/**
	 * 读取一条数据
	 * @param array $arr 键名数组  提示:主键一列:array(1) 主键多列：array(1, 2)
	 * @return array
	 */
	// 技巧：可以简写成 1 程序会自动转换成 array(1)
	public function get($arr) {
		$key = $this->arr2key($arr);
		if(!isset($this->unique[$key])) {
			$this->unique[$key] = $this->cache_db_get($key);
		}
		return $this->unique[$key];
	}

	/**
	 * 读取多条数据 （multi_get 简写成 mget）
	 * @param array $arr	多列键名数组 (提示: 主键一列时使用一维数组，主键多列时使用二维数组)
	 * @return array
	 */
	// 主键一列：mget(array(1, 2, 3));
	// 主键多列：mget(array(array(1, 1), array(1, 2), array(1, 3)));
	public function mget($arr) {
		$data = array();
		foreach($arr as $k=>&$key) {
			$key = $this->arr2key($key);
			if(isset($this->unique[$key])) {
				$data[$key] = $this->unique[$key];
				unset($arr[$k]);
			}else{
				$this->unique[$key] = $data[$key] = NULL;	// 占位 保证返回数组顺序
			}
		}
		$data2 = $this->cache_db_multi_get($arr);
		return array_merge($data, $data2);
	}

	/**
	 * 更新一条数据
	 * @param array $data	数据 (必须包含主键)
	 * @param int  $life	缓存时间 (默认为永久)
	 * @return bool
	 */
	public function update($data, $life = 0) {
		$key = $this->pri2key($data);
		$this->unique[$key] = $data;
		return $this->cache_db_update($key, $data, $life);
	}

	/**
	 * 删除一条数据 (简化数组, 如: delete(1,2,3,4) 表示 del(array(1,2,3,4)) 最多支持4个参数)
	 * @param string $arg1-$arg4 参数1-参数4
	 * @return array
	 */
	public function delete($arg1, $arg2 = FALSE, $arg3 = FALSE, $arg4 = FALSE) {
		$arr = ($arg2 !== FALSE) ? $this->arg2arr($arg1, $arg2, $arg3, $arg4) : (array)$arg1;
		return $this->del($arr);
	}

	/**
	 * 删除一条数据
	 * @param string $arr	键名数组
	 * @return bool
	 */
	public function del($arr) {
		$key = $this->arr2key($arr);
		$ret = $this->cache_db_delete($key);
		if($ret) {
			unset($this->unique[$key]);
			$this->count('-1');
		}
		return $ret;
	}

	/**
	 * cache+db 清空
	 * @return boot
	 */
	public function truncate() {
		return $this->cache_db_truncate();
	}

	/**
	 * cache+db 读取/设置 表最大ID
	 * @param string $val	设置值 有三种情况 1.不填为读取(默认) 2.基础上增加 如：'+1' 3.设置指定值
	 * @return int	返回最大ID
	 */
	public function maxid($val = FALSE) {
		return $this->cache_db_maxid($val);
	}

	/**
	 * cache+db 读取/设置 表的总行数
	 * @param string $val	设置值 如：count() 读取、 count(100) 设置为100、 count('+1') 设置加1、 count('-1') 设置减1
	 * @return int
	 */
	public function count($val = FALSE) {
		return $this->cache_db_count($val);
	}

	/**
	 * 根据条件读取数据
	 * @param array $where	条件
	 * @param array $order	排序
	 * @param int $start	开始位置
	 * @param int $limit	读取几条
	 * @param int $life		二级缓存时间 (默认为永久)
	 * @return array
	 */
	public function find_fetch($where = array(), $order = array(), $start = 0, $limit = 0, $life = 0) {
		return $this->cache_db_find_fetch($this->table, $this->pri, $where, $order, $start, $limit, $life);
	}

	/**
	 * 根据条件返回 key 数组
	 * @param array $where	条件
	 * @param array $order	排序
	 * @param int $start	开始位置
	 * @param int $limit	读取几条
	 * @param int $life		二级缓存时间 (默认为永久)
	 * @return array
	 */
	public function find_fetch_key($where = array(), $order = array(), $start = 0, $limit = 0, $life = 0) {
		return $this->cache_db_find_fetch_key($this->table, $this->pri, $where, $order, $start, $limit, $life);
	}

	/**
	 * 根据条件批量更新数据 (不建议用来更新大量数据，太暴力了)
	 * @param array $where	条件
	 * @param array $lowprority	是否开启不锁定表
	 * @return int	返回影响的记录行数
	 */
	public function find_update($where, $data, $lowprority = FALSE) {
		$this->unique = array();
		if($this->cache_conf['enable']) {
			$n = $this->find_count($where);
			if($n > 2000) {
				$this->cache->truncate($this->table);
			}else{
				$keys = $this->find_fetch_key($where);
				foreach($keys as $key) {
					$this->cache->delete($key);
				}
			}
		}
		return $this->db->find_update($this->table, $where, $data, $lowprority);
	}

	/**
	 * 根据条件批量删除数据 (不建议用来删除大量数据，太暴力了)
	 * @param array $where	条件
	 * @param array $lowprority	是否开启不锁定表
	 * @return int	返回影响的记录行数
	 */
	public function find_delete($where, $lowprority = FALSE) {
		$this->unique = array();
		if($this->cache_conf['enable']) {
			$n = $this->find_count($where);
			if($n > 2000) {
				$this->cache->truncate($this->table);
			}else{
				$keys = $this->find_fetch_key($where);
				foreach($keys as $key) {
					$this->cache_db_delete($key);
				}
			}
		}
		$num = $this->db->find_delete($this->table, $where, $lowprority);

		if(!empty($this->maxid) && $num > 0) {
			$this->count('-'.$num);
		}
		return $num;
	}

	/**
	 * 准确获取最大ID (速度慢)
	 * @param string $key	键名
	 * @return int	返回ID
	 */
	public function find_maxid() {
		return isset($this->maxid) ? $this->db->find_maxid($this->table.'-'.$this->maxid) : 0;
	}

	/**
	 * 准确获取总条数 (速度慢)
	 * @param array $where	条件
	 * @return int	返回条数
	 */
	public function find_count($where = array()) {
		return $this->db->find_count($this->table, $where);
	}

	/**
	 * 创建索引
	 * @param array $index	键名数组	// array('uid'=>1, 'dateline'=>-1, 'unique'=>TRUE, 'dropDups'=>TRUE) 为了配合 mongodb 的索引才这样设计的
	 * @return boot	返回ID
	 */
	public function index_create($index) {
		return $this->db->index_create($this->table, $index);
	}

	/**
	 * 删除索引
	 * @param array $index	键名数组
	 * @return boot	返回ID
	 */
	public function index_drop($index) {
		return $this->db->index_drop($this->table, $index);
	}

	/**
	 * 主键 转 key
	 * @param array $arr	数组 (关联数组)
	 * @return string 返回标准KEY
	 */
	public function pri2key($arr) {
		$s = $this->table;
		foreach($this->pri as $v) {
			$s .= "-$v-".$arr[$v];
		}
		return $s;
	}

	/**
	 * 数组 转 key
	 * @param array $arr	数组 (数字数组)
	 * @return string 返回标准KEY
	 */
	public function arr2key($arr) {
		$arr = (array)$arr;
		$s = $this->table;
		foreach($this->pri as $k=>$v) {
			if(!isset($arr[$k])) {
				$err = array();
				foreach($this->pri as $pk=>$pv) {
					$var = isset($arr[$pk]) ? $arr[$pk] : 'null';
					$err[] = "'$pv => $var";
				}
				throw new Exception('非法键名数组: array('.implode(', ', $err).');');
			}
			$s .= "-$v-".$arr[$k];
		}
		return $s;
	}

	/**
	 * 多参数 转 数组
	 * @param int $arg1-$arg4 参数1-参数4
	 * @return array
	 */
	public function arg2arr($arg1, $arg2, $arg3 = FALSE, $arg4 = FALSE) {
		$arr = (array)$arg1;
		array_push($arr, $arg2);
		$arg3 !== FALSE && array_push($arr, $arg3);
		$arg4 !== FALSE && array_push($arr, $arg4);
		return $arr;
	}

	// +------------------------------------------------------------------------------
	// | cache + db 封装方法 (启用cache时优先读缓存) 不推荐外部使用
	// +------------------------------------------------------------------------------
	/**
	 * cache+db 读取一条数据
	 * @param string $key	键名
	 * @return mixed
	 */
	public function cache_db_get($key) {
		if($this->cache_conf['enable']) {
			$data = $this->cache->get($key);
			if(empty($data)) {
				$data = $this->db->get($key);
				$this->cache->set($key, $data);
			}
			return $data;
		}else{
			return $this->db->get($key);
		}
	}

	/**
	 * cache+db 读取多条数据
	 * @param array $keys	键名数组
	 * @return array
	 */
	public function cache_db_multi_get($keys) {
		if($this->cache_conf['enable']) {
			$data = $this->cache->multi_get($keys);
			if(empty($data)) {
				$data = $this->db->multi_get($keys);
				foreach((array)$data as $k=>$v) {
					$this->cache->set($k, $v);
				}
			}else{
				foreach($data as $k=>&$v) {
					if($v === FALSE) {	// 等于 FALSE 时表示缓存不存在
						$v = $this->db->get($k);
						$this->cache->set($k, $v);
					}
				}
			}
			return $data;
		}else{
			return $this->db->multi_get($keys);
		}
	}

	/**
	 * cache+db 写入一条数据
	 * @param string $key	键名
	 * @param mixed $data	数据
	 * @param int $life		缓存时间 (默认为永久)
	 * @return boot
	 */
	public function cache_db_set($key, $data, $life = 0) {
		$this->cache_conf['enable'] && $this->cache->set($key, $data, $life);
		return $this->db->set($key, $data);
	}

	/**
	 * cache+db 更新一条数据
	 * @param string $key	键名
	 * @param array $data	数据
	 * @param int $life		缓存时间 (默认为永久)
	 * @return boot
	 */
	public function cache_db_update($key, $data, $life = 0) {
		$this->cache_conf['enable'] && $this->cache->update($key, $data, $life);
		return $this->db->update($key, $data);
	}

	/**
	 * cache+db 删除一条数据
	 * @param string $key	键名
	 * @return boot
	 */
	public function cache_db_delete($key) {
		$this->cache_conf['enable'] && $this->cache->delete($key);
		return $this->db->delete($key);
	}

	/**
	 * cache+db 清空数据
	 * @return boot
	 */
	public function cache_db_truncate() {
		$this->cache_conf['enable'] && $this->cache->truncate($this->table);
		return $this->db->truncate($this->table);
	}

	/**
	 * cache+db 读取/设置 表最大ID
	 * @param string $val	设置值 有三种情况 1.不填为读取(默认) 2.基础上增加 如：'+1' 3.设置指定值
	 * @return int	返回最大ID
	 */
	public function cache_db_maxid($val = FALSE) {
		$key = $this->table.'-'.$this->maxid;
		if($this->cache_conf['enable']) {
			if($val === FALSE) {
				$maxid = $this->cache->maxid($key, $val);
				if(empty($maxid)) {
					$maxid = $this->db->maxid($key, $val);
					$this->cache->maxid($key, $maxid);
				}
				return $maxid;
			}else{
				$maxid = $this->db->maxid($key, $val);
				return $this->cache->maxid($key, $maxid);
			}
		}else{
			return $this->db->maxid($key, $val);
		}
	}

	/**
	 * cache+db 读取/设置 表的总行数
	 * @param string $val	设置值 有四种情况 1.不填为读取(默认) 2.基础上增加 如：'+1' 3.基础上减少 如：'-1' 4.设置指定值
	 * @return int
	 */
	public function cache_db_count($val = FALSE) {
		$key = $this->table;
		if($this->cache_conf['enable']) {
			if($val === FALSE) {
				$rows = $this->cache->count($key, $val);
				if(empty($rows)) {
					$rows = $this->db->count($key, $val);
					$this->cache->count($key, $rows);
				}
				return $rows;
			}else{
				$rows = $this->db->count($key, $val);
				return $this->cache->count($key, $rows);
			}
		}else{
			return $this->db->count($key, $val);
		}
	}

	/**
	 * cache+db 根据条件读取数据
	 * @param string $table	表名
	 * @param array $pri	主键
	 * @param array $where	条件
	 * @param array $order	排序
	 * @param int $start	开始位置
	 * @param int $limit	读取几条
	 * @param int $life		二级缓存时间 (默认为永久)
	 * @return array
	 */
	public function cache_db_find_fetch($table, $pri, $where = array(), $order = array(), $start = 0, $limit = 0, $life = 0) {
		// 如果是 mongodb 就直接取数据，不支持缓存
		if($this->db_conf['type'] == 'mongodb') {
			return $this->db->find_fetch($table, $pri, $where, $order, $start, $limit);
		}else{
			$keys = $this->cache_db_find_fetch_key($table, $pri, $where, $order, $start, $limit, $life);
			return $this->cache_db_multi_get($keys);
		}
	}

	/**
	 * cache+db 根据条件返回 key 数组
	 * @param string $table	表名
	 * @param array $pri	主键
	 * @param array $where	条件
	 * @param array $order	排序
	 * @param int $start	开始位置
	 * @param int $limit	读取几条
	 * @param int $life		二级缓存时间 (默认为永久)
	 * @return array
	 */
	public function cache_db_find_fetch_key($table, $pri, $where = array(), $order = array(), $start = 0, $limit = 0, $life = 0) {
		if($this->cache_conf['enable'] && $this->cache_conf['l2_cache'] === 1) {
			$key = $table.'_'.md5(serialize(array($pri, $where, $order, $start, $limit)));
			$keys = $this->cache->l2_cache_get($key);
			if(empty($keys)) {
				$keys = $this->db->find_fetch_key($table, $pri, $where, $order, $start, $limit);
				$this->cache->l2_cache_set($key, $keys, $life);
			}
		}else{
			$keys = $this->db->find_fetch_key($table, $pri, $where, $order, $start, $limit);
		}
		return $keys;
	}
}
