<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */
// 请参考 cache_memcache.class.php 开发新模块

interface cache_interface {
	public function get($key);
	public function multi_get($keys);
	public function set($key, $data, $life = 0);
	public function update($key, $data, $life = 0);
	public function delete($key);
	public function maxid($table, $val = FALSE);
	public function count($table, $val = FALSE);
	public function truncate($pre = '');

	public function l2_cache_get($l2_key);
	public function l2_cache_set($l2_key, $keys, $life = 0);
}
