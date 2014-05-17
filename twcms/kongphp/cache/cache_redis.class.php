<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 * 参考：
 * http://pecl.php.net/package/redis
 * https://github.com/nicolasff/phpredis
 * http://www.cnblogs.com/ikodota/archive/2012/03/05/php_redis_cn.html
 */

defined('KONG_PATH') || exit;
class cache_redis implements cache_interface{
	public $conf;

	public function __construct(&$conf) {
		$this->conf = $conf;
	}

	public function __get($var) {
		if ($var == 'redis') {
			if (extension_loaded('Redis')) {
				$this->redis = new Redis;
			} else {
				throw new Exception('Redis Extension not loaded.');
			}

			if (!$this->redis) {
				throw new Exception('PHP.ini Error: Redis extension not loaded.');
			}

			if ($this->redis->connect($this->conf['host'], $this->conf['port'])) {
				return $this->redis;
			} else {
				throw new Exception('Can not connect to Redis host.');
			}
		}
	}

	/**
	 * 读取一条数据
	 * @param string $key	键名
	 * @return array
	 */
	public function get($key) {
		return $this->redis->hgetall($key);
	}

	/**
	 * 读取多条数据
	 * @param array $keys	键名数组
	 * @return array
	 */
	public function multi_get($keys) {
		$data = array();
		foreach($keys as $k) {
			$data[$k] = $this->redis->hgetall($k);
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
		$ret = $this->redis->hmset($key, $val);
		if($ret && $life) {
			$this->redis->expire($key, $life);
		}
		return $ret;
	}

	/**
	 * 更新一条数据
	 * @param string $key	键名
	 * @param array $data	数据
	 * @param int  $life	缓存时间 (默认为永久)
	 * @return bool
	 */
	public function update($key, $data, $life = 0) {
		$arr = $this->get($key);
		if($arr === FALSE) return FALSE; // 缓存不存在时，更新失败

		is_array($arr) && is_array($data) && $data = array_merge($arr, $data);
		return $this->set($key, $data, $life);
	}

	/**
	 * 删除一条数据
	 * @param string $key	键名
	 * @return bool
	 */
	public function delete($key) {
		return $this->redis->hdel($key);
	}

	/**
	 * 获取/设置最大ID
	 * @param string $table	表名
	 * @param boot/int $val	值	（为 FALSE 时为获取）
	 * @return int
	 */
	public function maxid($table, $val = FALSE) {
		$key = $table.'-maxid';
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
		$key = $table.'-count';
		if($val === FALSE) {
			return intval($this->get($key));
		}else{
			$this->set($key, $val);
			return $val;
		}
	}

	/**
	 * 清空缓存
	 * @return boot
	 */
	public function truncate() {
		return $this->redis->flushdb();
	}

	/**
	 * 读取一条二级缓存
	 * @param string $l2_key	二级缓存键名
	 * @return boot
	 */
	public function l2_cache_get($l2_key) {
		$l2_cache_time = $this->get('l2_cache_time');	// 最后更新数据微秒时间，用来控制缓存
		$l2_key_time = $this->get($l2_key.'_time');	// 用来和 $l2_cache_time 对比是否一样
		if($l2_cache_time && $l2_cache_time === $l2_key_time) {
			return $this->get($l2_key);	// 从缓存中读取数据
		}
		return FALSE;
	}

	/**
	 * 写入一条二级缓存
	 * @param string $l2_key	二级缓存键名
	 * @param string $data		数据
	 * @return boot
	 */
	public function l2_cache_set($l2_key, $data, $life = 0) {
		$l2_cache_time = $this->get('l2_cache_time');	// 最后更新数据微秒时间，用来控制缓存
		if(empty($l2_cache_time)) {
			$l2_cache_time = microtime(1);
			$this->set('l2_cache_time', $l2_cache_time);
		}
		$this->set($l2_key.'_time', $l2_cache_time, $life);	// 把最后更新数据微秒时间写入缓存
		return $this->set($l2_key, $data, $life);	// 把数据写入缓存
	}

	/**
	 * 设置二级缓存过期
	 * @return boot
	 */
	public function l2_cache_expired() {
		return $this->delete('l2_cache_time');
	}
}
?>
