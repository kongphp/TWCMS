<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('KONG_PATH') || exit;
class cache_memcache implements cache_interface{
	private $conf;
	private $is_getmulti = FALSE;	//是否支持 getMulti 方法
	public $pre;	//缓存前缀 （防止同一台缓存服务器，有多套程序，键名冲突问题）

	public function __construct(&$conf) {
		$this->conf = &$conf;
		$this->pre = $conf['pre'];
	}

	public function __get($var) {
		$c = $this->conf['memcache'];
		if($var == 'memcache') {
			// 判断 Mongo 扩展是否安装
			if(extension_loaded('Memcached')) {
				$this->memcache = new Memcached;
			}elseif(extension_loaded('Memcache')) {
				$this->memcache = new Memcache;
			}else{
				throw new Exception('Memcache Extension not loaded.');
			}

			if(!$this->memcache) {
				throw new Exception('PHP.ini Error: Memcache extension not loaded.');
			}

			if($this->memcache->connect($c['host'], $c['port'])) {
				if(!empty($c['multi'])) {
					$this->is_getmulti = method_exists($this->memcache, 'getMulti');
				}
				return $this->memcache;
			}else{
				throw new Exception('Can not connect to Memcached host.');
			}
		}
	}

	/**
	 * 读取一条数据
	 * @param string $key	键名
	 * @return array
	 */
	public function get($key) {
		return $this->memcache->get($this->pre.$key);
	}

	/**
	 * 读取多条数据
	 * @param array $keys	键名数组
	 * @return array
	 */
	public function multi_get($keys) {
		$data = array();
		// 支持 getMulti 方法
		if($this->is_getmulti) {
			// 补上缓存前缀
			$m_keys = array();
			foreach ($keys as $i=>$k) {
				$m_keys[$i] = $this->pre.$k;
			}
			$m_data = $this->memcache->getMulti($m_keys);
			foreach($keys as $k) {
				if(empty($m_data[$this->pre.$k])) {
					$data[$k] = FALSE;
				}else{
					$data[$k] = $m_data[$this->pre.$k];
				}
			}
		}else{
			foreach($keys as $k) {
				$arr = $this->memcache->get($this->pre.$k);
				if(empty($arr)) {
					$data[$k] = FALSE;
				}else{
					$data[$k] = $arr;
				}
			}
		}
		return $data;
	}

	/**
	 * 写入一条数据
	 * @param string $key	键名
	 * @param array $data	数据
	 * @param int  $life	缓存时间 (默认为永久)
	 * @return bool
	 */
	public function set($key, $data, $life = 0) {
		// 二级缓存开启时，写入最新微秒时间
		if($this->conf['l2_cache'] === 1) {
			$this->memcache->delete($this->pre.'_l2_cache_time');
		}
		return $this->memcache->set($this->pre.$key, $data, 0, $life);
	}

	/**
	 * 更新一条数据
	 * @param string $key	键名
	 * @param array $data	数据
	 * @param int  $life	缓存时间 (默认为永久)
	 * @return bool
	 */
	public function update($key, $data, $life = 0) {
		$key = $this->pre.$key;
		$arr = $this->get($key);
		if($arr !== FALSE) {
			is_array($arr) && is_array($data) && $arr = array_merge($arr, $data);
			return $this->set($key, $arr, $life);
		}
		return FALSE;
	}

	/**
	 * 删除一条数据
	 * @param string $key	键名
	 * @return bool
	 */
	public function delete($key) {
		// 二级缓存开启时，写入最新微秒时间
		if($this->conf['l2_cache'] === 1) {
			$this->memcache->delete($this->pre.'_l2_cache_time');
		}
		return $this->memcache->delete($this->pre.$key);
	}

	/**
	 * 获取/设置最大ID
	 * @param string $table	表名
	 * @param boot/int $val	值	（为 FALSE 时为获取）
	 * @return int
	 */
	public function maxid($table, $val = FALSE) {
		$key = $table.'-Auto_increment';
		if($val === FALSE) {
			return intval($this->get($key));
		}else{
			 $this->set($key, $val);
			 return $val;
		}
	}

	/**
	 * 获取/设置总条数
	 * @param string $table	表名
	 * @param boot/int $val	值	（为 FALSE 时为获取）
	 * @return int
	 */
	public function count($table, $val = FALSE) {
		$key = $table.'-Rows';
		if($val === FALSE) {
			return intval($this->get($key));
		}else{
			$this->set($key, $val);
			return $val;
		}
	}

	/**
	 * 清空缓存
	 * @param string $pre	前缀
	 * @return boot
	 */
	public function truncate($pre = '') {
		return $this->memcache->flush();
	}

	/**
	 * 读取一条二级缓存
	 * @param string $l2_key	二级缓存键名
	 * @return boot
	 */
	public function l2_cache_get($l2_key) {
		$l2_cache_time = $this->get('_l2_cache_time');	// 最后更新数据微秒时间，用来控制缓存
		$l2_key_time = $this->get($l2_key.'_time');	// 用来和 $l2_cache_time 对比是否一样
		if($l2_cache_time && $l2_cache_time === $l2_key_time) {
			return $this->get($l2_key);	// 从缓存中读取数据
		}
		return FALSE;
	}

	/**
	 * 写入一条二级缓存
	 * @param string $l2_key	二级缓存键名
	 * @param string $keys		键名数组
	 * @return boot
	 */
	public function l2_cache_set($l2_key, $keys, $life = 0) {
		$l2_cache_time = $this->get('_l2_cache_time');	// 最后更新数据微秒时间，用来控制缓存
		if(empty($l2_cache_time)) {
			$l2_cache_time = microtime(1);
			$this->memcache->set($this->pre.'_l2_cache_time', $l2_cache_time, 0, 0);
		}
		$this->memcache->set($this->pre.$l2_key.'_time', $l2_cache_time, 0, $life);	// 把最后更新数据微秒时间写入缓存
		return $this->memcache->set($this->pre.$l2_key, $keys, 0, $life);	// 把数据写入缓存
	}
}
?>
