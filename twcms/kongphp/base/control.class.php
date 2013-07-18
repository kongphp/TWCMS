<?php
// +------------------------------------------------------------------------------
// | Copyright (C) 2013 wuzhaohuan <kongphp@gmail.com> All rights reserved.
// +------------------------------------------------------------------------------

class control extends view{
	public function message($msg, $jumpurl = '') {
		if(core::gpc('ajax')) {

		} else {
			echo $msg;
		}
		exit;
	}

	public function __call($method, $args) {
		throw new Exception('控制器没有找到：'.get_class($this).'->'.$method.'('.(empty($args) ? '' : var_export($args, 1)).')');
	}
}