<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('KONG_PATH') || exit;
class cache_file implements cache_interface{
	/**
	 * 读取一条数据
	 * @param string $key	键名
	 * @return array
	 */
	public function get($key) {
		// 返回 FALSE 时表示缓存不存在, 这里非常重要，需要 === 判断，不能换成别的值。
		return is_file(RUNTIME_PATH."cache_file/$key.php") ? include(RUNTIME_PATH."cache_file/$key.php") : FALSE;
	}

	/**
	 * 读取多条数据
	 * @param array $keys	键名数组
	 * @return array
	 */
	public function multi_get($keys) {
		$data = array();
		foreach($keys as $k) {
			$arr = $this->get($k);
			$data[$k] = $arr;
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
		return $this->write($key, $data);
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
		!empty($arr) && is_array($arr) && is_array($data) && $data = array_merge($arr, $data);
		return $this->set($key, $data, $life);
	}

	/**
	 * 删除一条数据
	 * @param string $key	键名
	 * @return bool
	 */
	public function delete($key) {
		try {
			return unlink(RUNTIME_PATH."cache_file/$key.php");
		} catch (Exception $e) {
			return FALSE;
		}
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
		$dh = opendir(RUNTIME_PATH.'cache_file/');
		for ($i=0; $i < 2000 && ($file = readdir($dh)); $i++) {
			if($file == '.' || $file == '..') continue;
			try { unlink(RUNTIME_PATH."cache_file/$file.php"); } catch (Exception $e) { }
		}
		closedir($dh);
		return TRUE;
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
			$this->write('l2_cache_time', $l2_cache_time);
		}
		$this->write($l2_key.'_time', $l2_cache_time);	// 把最后更新数据微秒时间写入缓存
		return $this->write($l2_key, $data);	// 把数据写入缓存
	}

	// 让二级缓存过期
	public function l2_cache_expired() {
		try {
			return unlink(RUNTIME_PATH.'cache_file/l2_cache_time.php');
		} catch (Exception $e) {
			return FALSE;
		}
	}

	// 写入缓存文件
	public function write($key, $data) {
		is_dir(RUNTIME_PATH.'cache_file/') OR mkdir(RUNTIME_PATH.'cache_file/', 0777, 1);
		return file_put_contents(RUNTIME_PATH."cache_file/$key.php", '<?php return '.var_export($data, 1).';') ? TRUE : FALSE;
	}
}
?>
