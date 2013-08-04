<?php
/**
 *	[TWCMS] (C)2012-2013 TongWang Inc.
 */

defined('TWCMS_PATH') or exit;
class user extends model {
	function __construct() {
		$this->table = 'user';		// 表名
		$this->pri = array('uid');	// 主键
		$this->maxid = 'uid';		// 自增字段
	}

	// 根据用户名获取用户数据
	public function get_user_by_username($username) {
		$users = $this->find_fetch(array('username'=>$username), array(), 0, 1);
		return $users ? array_pop($users) : array();
	}
}