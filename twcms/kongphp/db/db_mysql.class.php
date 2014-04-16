<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('KONG_PATH') || exit;
class db_mysql implements db_interface {
	private $conf;
	public $tablepre;		// 数据表前缀
	//private $wlink;		// 写(主)数据库
	//private $rlink;		// 读(从)数据库
	//private $xlink;		// 分发数据库

	public function __construct(&$conf) {
		$this->conf = &$conf;
		$this->tablepre = $conf['master']['tablepre'];
	}

	/**
	 * 创建 MySQL 连接
	 * @param string $var 数据库链接名 只能是 wlink rlink xlink
	 * @return resource
	 */
	public function __get($var) {
		// 主数据库 (写)
		if($var == 'wlink') {
			$cfg = $this->conf['master'];
			empty($cfg['engine']) && $cfg['engine'] = '';
			$this->wlink = $this->connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['name'], $cfg['charset'], $cfg['engine']);
			return $this->wlink;

		// 从数据库群 (读)
		}elseif($var == 'rlink') {
			if(empty($this->conf['slaves'])) {
				$this->rlink = $this->wlink;
				return $this->rlink;
			}
			$n = rand(0, count($this->conf['slaves']) - 1);
			$cfg = $this->conf['slaves'][$n];
			empty($cfg['engine']) && $cfg['engine'] = '';
			$this->rlink = $this->connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['name'], $cfg['charset'], $cfg['engine']);
			return $this->rlink;

		// 单点分发数据库 (负责所有表的 maxid count 读写)
		}elseif($var == 'xlink') {
			if(empty($this->conf['arbiter'])) {
				$this->xlink = $this->wlink;
				return $this->xlink;
			}
			$cfg = $this->conf['arbiter'];
			empty($cfg['engine']) && $cfg['engine'] = '';
			$this->xlink = $this->connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['name'], $cfg['charset'], $cfg['engine']);
			return $this->xlink;
		}
	}

	/**
	 * 读取一条数据
	 * @param string $key	键名 (高性能需求，键名必须使用索引字段)
	 * @return array
	 */
	// string
	// 	in: 'user-uid-2'
	// 	out: array('uid'=>2, 'username'=>'two')
	public function get($key) {
		list($table, $keyarr, $keystr) = $this->key2arr($key);
		$query = $this->query("SELECT * FROM {$this->tablepre}$table WHERE $keystr LIMIT 1", $this->rlink);
		return mysql_fetch_assoc($query);
	}

	/**
	 * 读取多条数据
	 * @param array $keys	键名数组 (高性能需求，键名必须使用索引字段)
	 * @return array
	 */
	// array
	// 	in: array(
	// 		'article-cid-1-aid-1',
	// 		'article-cid-1-aid-2',
	// 	)
	// 	out: array(
	// 		'article-cid-1-aid-1'=>array('cid'=>1,'cid'=>1, 'title'=>'abc')
	// 		'article-cid-1-aid-2'=>array('cid'=>1,'cid'=>2, 'title'=>'bcd')
	// 	)
	public function multi_get($keys) {
		// 下面这种方式读取比遍历读取效率高
		$sql = '';
		$ret = array();
		foreach($keys as $k) {
			$ret[$k] = array();	// 按原来的顺序赋值，避免后面的 OR 条件取出时顺序混乱
			list($table, $keyarr, $keystr) = $this->key2arr($k);
			$sql .= "$keystr OR ";
		}
		$sql = substr($sql, 0, -4);
		if($sql) {
			$query = $this->query("SELECT * FROM {$this->tablepre}$table WHERE $sql", $this->rlink);
			while($row = mysql_fetch_assoc($query)) {
				$keyname = $table;
				foreach($keyarr as $k=>$v) {
					$keyname .= "-$k-".$row[$k];
				}
				$ret[$keyname] = $row;
			}
		}
		return $ret;
	}

	/**
	 * 写入一条数据 (包含了 insert 和 update)
	 * @param string $key	键名
	 * @param array $data	数据
	 * @return bool
	 */
	public function set($key, $data) {
		if(!is_array($data)) return FALSE;

		list($table, $keyarr) = $this->key2arr($key);
		$data += $keyarr;
		$s = $this->arr2sql($data);

		$exists = $this->get($key);
		if(empty($exists)) {
			return $this->query("INSERT INTO {$this->tablepre}$table SET $s", $this->wlink);
		} else {
			return $this->update($key, $data);
		}
	}

	/**
	 * 更新一条数据 (相比 $this->set() 可以修改主键)
	 * @param string $key	键名
	 * @param array $data	数据
	 * @return bool
	 */
	public function update($key, $data) {
		list($table, $keyarr, $keystr) = $this->key2arr($key);
		$s = $this->arr2sql($data);
		return $this->query("UPDATE {$this->tablepre}$table SET $s WHERE $keystr LIMIT 1", $this->wlink);
	}

	/**
	 * 删除一条数据
	 * @param string $key	键名
	 * @return bool
	 */
	public function delete($key) {
		list($table, $keyarr, $keystr) = $this->key2arr($key);
		return $this->query("DELETE FROM {$this->tablepre}$table WHERE $keystr LIMIT 1", $this->wlink);
	}

	/**
	 * 读取/设置 表最大ID
	 * @param string $key	键名 只能是表名+一个字段 如：'user-uid'(uid为自增字段)
	 * @param boot/int $val	设置值 有三种情况 1.不填为读取(默认) 2.基础上增加 如：'+1' 3.设置指定值
	 * @return int
	 */
	// maxid('user-uid') 读取 user 表最大 uid
	// maxid('user-uid', '+1') 设置 maxid + 1, 用于占位，保证 key 不会重复
	// maxid('user-uid', 10000) 设置 maxid 为 10000
	public function maxid($key, $val = FALSE) {
		list($table, $col) = explode('-', $key);
		$maxid = $this->table_maxid($key);
		if($val === FALSE) {
			return $maxid;
		}elseif(is_string($val)) {
			$val = max(0, $maxid + intval($val));
		}
		$this->query("UPDATE {$this->tablepre}framework_maxid SET maxid='$val' WHERE name='$table' LIMIT 1", $this->xlink);
		return $val;
	}

	/**
	 * 读取表最大ID (如果不存在自动创建表和设置最大ID)
	 * @param string $key	键名 只能是表名+一个字段 如：'user-uid'(uid一般为主键)
	 * @return int
	 */
	public function table_maxid($key) {
		list($table, $col) = explode('-', $key);

		$maxid = FALSE;
		$query = $this->query("SELECT maxid FROM {$this->tablepre}framework_maxid WHERE name='$table' LIMIT 1", $this->xlink, FALSE);

		if($query) {
			$maxid = $this->result($query, 0);
		}elseif(mysql_errno($this->xlink) == 1146) {
			$sql = "CREATE TABLE `{$this->tablepre}framework_maxid` (";
			$sql .= "`name` char(32) NOT NULL default '',";
			$sql .= "`maxid` int(10) unsigned NOT NULL default '0',";
			$sql .= "PRIMARY KEY (`name`)";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
			$this->query($sql, $this->xlink);
		}else{
			throw new Exception('framework_maxid error, mysql_error:'.mysql_error());
		}
		if($maxid === FALSE) {
			$query = $this->query("SELECT MAX($col) FROM {$this->tablepre}$table", $this->wlink);
			$maxid = $this->result($query, 0);
			$this->query("INSERT INTO {$this->tablepre}framework_maxid SET name='$table', maxid='$maxid'", $this->xlink);
		}
		return $maxid;
	}

	/**
	 * 读取/设置 表的总行数
	 * @param string $table	表名
	 * @param boot/int $val	设置值 有四种情况 1.不填为读取(默认) 2.基础上增加 如：'+1' 3.基础上减少 如：'-1' 4.设置指定值
	 * @return int
	 */
	public function count($table, $val = FALSE) {
		$count = $this->table_count($table);
		if($val === FALSE) {
			return $count;
		}elseif(is_string($val)) {
			if($val[0] == '+') {
				$val = $count + intval($val);
			}elseif($val[0] == '-') {
				$val = max(0, $count + intval($val));
			}
		}
		$this->query("UPDATE {$this->tablepre}framework_count SET count='$val' WHERE name='$table' LIMIT 1", $this->xlink);
		return $val;
	}

	/**
	 * 读取表的总行数 (如果不存在自动创建表和设置总行数)
	 * @param string $table	表名
	 * @return int
	 */
	public function table_count($table) {
		$count = FALSE;
		$query = $this->query("SELECT count FROM {$this->tablepre}framework_count WHERE name='$table' LIMIT 1", $this->xlink, FALSE);

		if($query) {
			$count = $this->result($query, 0);
		}elseif(mysql_errno($this->xlink) == 1146) {
			$sql = "CREATE TABLE {$this->tablepre}framework_count (";
			$sql .= "`name` char(32) NOT NULL default '',";
			$sql .= "`count` int(10) unsigned NOT NULL default '0',";
			$sql .= "PRIMARY KEY (`name`)";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
			$this->query($sql, $this->xlink);
		}else{
			throw new Exception('framework_cout error, mysql_error:'.mysql_error());
		}
		if($count === FALSE) {
			$query = $this->query("SELECT COUNT(*) FROM {$this->tablepre}$table", $this->wlink);
			$count = $this->result($query, 0);
			$this->query("INSERT INTO {$this->tablepre}framework_count SET name='$table', count='$count'", $this->xlink);
		}
		return $count;
	}

	/**
	 * 清空表
	 * @param string $table	表名 (表不存在会报错，但无关紧要)
	 * @return int
	 */
	public function truncate($table) {
		try {
			$this->query("TRUNCATE {$this->tablepre}$table");
			return TRUE;
		} catch(Exception $e) {
			return FALSE;
		}
	}

	/**
	 * 根据条件读取数据 (返回数组)
	 * @param string $table	表名
	 * @param array $pri	主键
	 * @param array $where	条件
	 * @param array $order	排序
	 * @param int $start	开始位置
	 * @param int $limit	读取几条
	 * @return array
	 */
	// in:
	// 	find_fetch('user', 'uid', array('uid'=> 100), array('uid'=>1), 0, 10);
	// 	find_fetch('user', 'uid', array('uid'=> array('>'=>'100', '<'=>'200')), array('uid'=>1), 0, 10);
	// 	find_fetch('user', 'uid', array('username'=> array('LIKE'=>'abc'), array('uid'=>1), 0, 10);

	// out:
	// 	array(
	// 		'user-uid-1'=>array('uid'=>1, 'username'=>'zhangsan'),
	// 		'user-uid-2'=>array('uid'=>2, 'username'=>'lisi'),
	// 		'user-uid-3'=>array('uid'=>3, 'username'=>'wangwu'),
	// 	)
	public function find_fetch($table, $pri, $where = array(), $order = array(), $start = 0, $limit = 0) {
		$key_arr = $this->find_fetch_key($table, $pri, $where, $order, $start, $limit);
		if(empty($key_arr)) return array();
		return $this->multi_get($key_arr);
	}

	/**
	 * 根据条件返回 key 数组
	 * @param string $table	表名
	 * @param array $pri	主键
	 * @param array $where	条件
	 * @param array $order	排序
	 * @param int $start	开始位置
	 * @param int $limit	读取几条
	 * @return array
	 */
	// out:
	// 	array (
	// 		'user-uid-1',
	// 		'user-uid-2',
	// 		'user-uid-3',
	// 	)
	public function find_fetch_key($table, $pri, $where = array(), $order = array(), $start = 0, $limit = 0) {
		$pris = implode(',', $pri);
		$s = "SELECT $pris FROM {$this->tablepre}$table";
		$s .= $this->arr2where($where);
		if(!empty($order)) {
			$s .= ' ORDER BY ';
			$comma = '';
			foreach($order as $k=>$v) {
				$s .= $comma."$k ".($v == 1 ? ' ASC ' : ' DESC ');
				$comma = ',';
			}
		}
		$s .= ($limit ? " LIMIT $start,$limit" : '');

		$ret = array();
		$query = $this->query($s, $this->rlink);
		while($row = mysql_fetch_assoc($query)) {
			$keystr = '';
			foreach($pri as $k) {
				$keystr .= "-$k-".$row[$k];
			}
			$ret[] = $table.$keystr;
		}
		return $ret;
	}

	/**
	 * 根据条件批量更新数据
	 * @param string $table	表名
	 * @param array $where	条件
	 * @param array $lowprority	是否开启不锁定表
	 * @return int	返回影响的记录行数
	 */
	public function find_update($table, $where, $data, $lowprority = FALSE) {
		$where = $this->arr2where($where);
		$data = $this->arr2sql($data);
		$lpy = $lowprority ? 'LOW_PRIORITY' : '';
		$this->query("UPDATE $lpy {$this->tablepre}$table SET $data $where", $this->wlink);
		return mysql_affected_rows($this->wlink);
	}

	/**
	 * 根据条件批量删除数据
	 * @param string $table	表名
	 * @param array $where	条件
	 * @param array $lowprority	是否开启不锁定表
	 * @return int	返回影响的记录行数
	 */
	public function find_delete($table, $where, $lowprority = FALSE) {
		$where = $this->arr2where($where);
		$lpy = $lowprority ? 'LOW_PRIORITY' : '';
		$this->query("DELETE $lpy FROM {$this->tablepre}$table $where", $this->wlink);
		return mysql_affected_rows($this->wlink);
	}

	/**
	 * 准确获取最大ID
	 * @param string $key	键名
	 * @return int
	 */
	public function find_maxid($key) {
		list($table, $maxid) = explode('-', $key);
		$arr = $this->fetch_first("SELECT MAX($maxid) AS num FROM {$this->tablepre}$table");
		return isset($arr['num']) ? intval($arr['num']) : 0;
	}

	/**
	 * 准确获取总条数
	 * @param string $table	表名
	 * @param array $where	条件
	 * @return int
	 */
	public function find_count($table, $where = array()) {
		$where = $this->arr2where($where);
		$arr = $this->fetch_first("SELECT COUNT(*) AS num FROM {$this->tablepre}$table $where");
		return isset($arr['num']) ? intval($arr['num']) : 0;
	}

	/**
	 * 创建索引
	 * @param string $table	表名
	 * @param array $index	键名数组	// array('uid'=>1, 'dateline'=>-1, 'unique'=>TRUE, 'dropDups'=>TRUE) 为了配合 mongodb 的索引才这样设计的
	 * @return boot
	 */
	public function index_create($table, $index) {
		$keys = implode(',', array_keys($index));
		$keyname = implode('_', array_keys($index));
		return $this->query("ALTER TABLE {$this->tablepre}$table ADD INDEX $keyname($keys)", $this->wlink);
	}

	/**
	 * 删除索引
	 * @param string $table	表名
	 * @param array $index	键名数组
	 * @return boot
	 */
	public function index_drop($table, $index) {
		$keys = implode(',', array_keys($index));
		$keyname = implode('_', array_keys($index));
		return $this->query("ALTER TABLE {$this->tablepre}$table DROP INDEX $keyname", $this->wlink);
	}

	// +------------------------------------------------------------------------------
	// | 以下是公共方法，但不推荐外部使用
	// +------------------------------------------------------------------------------
	/**
	 * 连接 MySQL 服务器
	 * @param string $host		主机
	 * @param string $user		用户名
	 * @param string $pass		密码
	 * @param string $name		数据库名称
	 * @param string $charset	字符集
	 * @param string $engine	数据库引擎
	 * @return resource
	 */
	public function connect($host, $user, $pass, $name, $charset = 'utf8', $engine = '') {
		$link = mysql_connect($host, $user, $pass);
		if(!$link) {
			throw new Exception(mysql_error());
		}
		$result = mysql_select_db($name, $link);
		if(!$result) {
			throw new Exception(mysql_error());
		}
		if(!empty($engine) && $engine == 'InnoDB') {
			$this->query("SET innodb_flush_log_at_trx_commit=no", $link);
		}

		// 不考虑 mysql 5.0.1 下以版本
		$this->query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary, sql_mode=''", $link);
		//$this->query("SET names utf8, sql_mode=''", $link);
		return $link;
	}

	/**
	 * 发送一条 MySQL 查询
	 * @param string $sql		SQL 语句
	 * @param string $link		打开的连接
	 * @param boot $isthrow		错误时是否抛
	 * @return resource
	 */
	public function query($sql, $link = NULL, $isthrow = TRUE) {
		empty($link) && $link = $this->wlink;

		if(defined('DEBUG') && DEBUG && isset($_ENV['_sqls']) && count($_ENV['_sqls']) < 1000) {
			$start = microtime(1);
			$result = mysql_query($sql, $link);
			$runtime = number_format(microtime(1) - $start, 4);

			// explain 分析 select 语句
			$explain_str = '';
			if(substr($sql, 0, 6) == 'SELECT') {
				$query = mysql_query("explain $sql", $link);
				if($query !== FALSE) {
					$explain_arr = mysql_fetch_assoc($query);
					//print_r($explain_arr);
					$explain_str = ' <font color="blue">[explain type: '.$explain_arr['type'].' | rows: '.$explain_arr['rows'].']</font>';
				}
			}
			$_ENV['_sqls'][] = ' <font color="red">[time:'.$runtime.'s]</font> '.htmlspecialchars(stripslashes($sql)).$explain_str;
		}else{
			$result = mysql_query($sql, $link);
		}

		if(!$result && $isthrow) {
			$s = 'MySQL Query Error: <b>'.$sql.'</b>. '.mysql_error();

			if(defined('DEBUG') && !DEBUG) $s = str_replace($this->tablepre, '***', $s); // 防止泄露敏感信息

			throw new Exception($s);
		}
		$_ENV['_sqlnum']++;
		return $result;
	}

	/**
	 * 获取第一条数据
	 * @param string $sql		SQL 语句
	 * @param string $link		打开的连接
	 * @return array
	 */
	public function fetch_first($sql, $link = NULL) {
		empty($link) && $link = $this->rlink;
		$query = $this->query($sql, $link);
		return mysql_fetch_assoc($query);
	}

	/**
	 * 获取多条数据 (特殊情况会用到)
	 * @param string $sql		SQL 语句
	 * @param string $link		打开的连接
	 * @return array
	 */
	public function fetch_all($sql, $link = NULL) {
		empty($link) && $link = $this->rlink;
		$query = $this->query($sql, $link);
		$ret = array();
		while($row = mysql_fetch_assoc($query)) {
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * 获取结果数据
	 * @param resource $query	查询结果集
	 * @param int $row			第几列
	 * @return int
	 */
	public function result($query, $row) {
		return mysql_num_rows($query) ? intval(mysql_result($query, $row)) : FALSE;
	}

	/**
	 * 获取 mysql 版本
	 * @return string
	 */
	public function version() {
		return mysql_get_server_info($this->rlink);
	}

	/**
	 * 关闭读写数据库连接
	 */
	public function __destruct() {
		if(!empty($this->wlink)) {
			mysql_close($this->wlink);
		}
		if(!empty($this->rlink) && !empty($this->wlink) && $this->rlink != $this->wlink) {
			mysql_close($this->rlink);
		}
	}

	/**
	 * 将数组转换为 where 语句
	 * @param array $arr 数组
	 * @return string
	 * in: array('id'=> array('>'=>'10', '<'=>'200'))
	 * out: WHERE id>'10' AND id<'200'
	 * 支持: '>=', '<=', '>', '<', 'LIKE', 'IN' (尽量少用，能不用则不用。'LIKE' 会导致全表扫描，大数据时不要使用)
	 * 注意1: 为考虑多种数据库兼容和性能问题，其他表达式不要使用，如：!= 会导致全表扫描
	 * 注意2: 高性能准则要让SQL走索引，保证查询至少达到range级别
	 */
	private function arr2where($arr) {
		$s = '';
		if(!empty($arr)) {
			foreach($arr as $key=>$val) {
				if(is_array($val)) {
					foreach($val as $k=>$v) {
						if(is_array($v)) {
							if($k === 'IN' && $v) {
								foreach($v as $i) {
									$i = addslashes($i);
									$s .= "$key='$i' OR "; // 走索引时，OR 比 IN 快
								}
								$s = substr($s, 0, -4).' AND ';
							}
						}else{
							$v = addslashes($v);
							if($k === 'LIKE') {
								$s .= "$key LIKE '%$v%' AND ";
							}else{
								$s .= "$key$k'$v' AND ";
							}
						}
					}
				}else{
					$val = addslashes($val);
					$s .= "$key='$val' AND ";
				}
			}
			$s && $s = ' WHERE '.substr($s, 0, -5);
		}
		return $s;
	}

	/**
	 * 将数组转换为SQL语句
	 * @param array $arr 数组
	 * @return string
	 * in: array('cid'=>1, 'aid'=>2)
	 * out: cid='1',aid='2'
	 */
	private function arr2sql($arr) {
		$s = '';
		foreach($arr as $k=>$v) {
			$v = addslashes($v);
			$s .= "$k='$v',";
		}
		return rtrim($s, ',');
	}

	/**
	 * 将键名转换为数组
	 * @param string $key	键名
	 * @return array
	 * in: article-cid-1-aid-2
	 * out: array('article', array('cid'=>1, 'aid'=>2), 'cid=1 AND aid=2')
	 */
	private function key2arr($key) {
		$arr = explode('-', $key);

		if(empty($arr[0])) {
			throw new Exception('table name is empty.');
		}

		$table = $arr[0];
		$keyarr = array();
		$keystr = '';
		$len = count($arr);
		for($i = 1; $i < $len; $i = $i + 2) {
			if(isset($arr[$i + 1])) {
				$v = $arr[$i + 1];
				$keyarr[$arr[$i]] = is_numeric($v) ? intval($v) : $v;	// 因为 mongodb 区分数字和字符串

				$keystr .= ($keystr ? ' AND ' : '').$arr[$i]."='".addslashes($v)."'";
			} else {
				$keyarr[$arr[$i]] = NULL;
			}
		}

		if(empty($keystr)) {
			throw new Exception('keystr name is empty.');
		}
		return array($table, $keyarr, $keystr);
	}
}
?>
