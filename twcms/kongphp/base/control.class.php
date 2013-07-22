<?php
// +------------------------------------------------------------------------------
// | Copyright (C) 2013 wuzhaohuan <kongphp@gmail.com> All rights reserved.
// +------------------------------------------------------------------------------

class control extends view{
	public function __get($var) {
		if($var == 'view') {
			return $this->view = new view();			
		}elseif($var == 'db') {
			$db = 'db_'.$_SERVER['_config']['db']['type'];
			return $this->db = new $db($_SERVER['_config']['db']);	// 给开发者调试时使用，千万不要在控制器中操作 DB
		}
	}

	public function message($msg, $jumpurl = '') {
		if(R('ajax')) {

		} else {
			echo $msg;
		}
		exit;
	}

	public function __call($method, $args) {
		throw new Exception('控制器没有找到：'.get_class($this).'->'.$method.'('.(empty($args) ? '' : var_export($args, 1)).')');
	}
}