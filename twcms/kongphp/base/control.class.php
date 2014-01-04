<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

class control{
	public function __get($var) {
		if($var == 'view') {
			return $this->view = new view();
		}elseif($var == 'db') {
			$db = 'db_'.$_ENV['_config']['db']['type'];
			return $this->db = new $db($_ENV['_config']['db']);	// 给开发者调试时使用，不建议在控制器中操作 DB
		}else{
			return $this->$var = core::model($var);
		}
	}

	public function assign($k, &$v) {
		$this->view->assign($k, $v);
	}

	public function assign_value($k, $v) {
		$this->view->assign_value($k, $v);
	}

	public function display($filename = null) {
		$this->view->display($filename);
	}

	public function message($status, $message, $jumpurl = '', $delay = 2) {
		if(R('ajax')) {
			echo json_encode(array('kong_status'=>$status, 'message'=>$message, 'jumpurl'=>$jumpurl, 'delay'=>$delay));
		}else{
			if(empty($jumpurl)) {
				$jumpurl = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
			}
			include KONG_PATH.'tpl/sys_message.php';
		}
		exit;
	}

	public function __call($method, $args) {
		// DEBUG关闭时，为防止泄漏敏感信息，用404错误代替
		if(DEBUG) {
			throw new Exception('控制器没有找到：'.get_class($this).'->'.$method.'('.(empty($args) ? '' : var_export($args, 1)).')');
		}else{
			core::error404();
		}
	}
}
