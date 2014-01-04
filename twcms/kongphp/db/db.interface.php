<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */
// 请参考 db_mysql.class.php 开发新模块

interface db_interface {
	public function get($key);
	public function multi_get($keys);
	public function set($key, $data);
	public function update($key, $data);
	public function delete($key);
	public function maxid($key, $val = FALSE);
	public function count($key, $val = FALSE);
	public function truncate($table);
	public function version();

	public function find_fetch($table, $pri, $where = array(), $order = array(), $start = 0, $limit = 0);
	public function find_fetch_key($table, $pri, $where = array(), $order = array(), $start = 0, $limit = 0);
	public function find_update($table, $where, $data, $lowprority = FALSE);
	public function find_delete($table, $where, $lowprority = FALSE);
	public function find_maxid($key);
	public function find_count($table, $where = array());

	public function index_create($table, $index);
	public function index_drop($table, $index);
}
