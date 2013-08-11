<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class my_control extends admin_control {
	// 我的首页
	public function index() {
		// 格式化后显示给用户
		$this->user->format($this->_user);

		// 常用功能
		$used_array = $this->get_used();
		$this->assign('used_array', $used_array);

		$this->display();
	}

	// 获取常用功能
	private function get_used() {
		$arr = array(
			array('name'=>'发布内容', 'url'=>'?u=content-add.html', 'imgsrc'=>'admin/ico/01.jpg'),
			array('name'=>'内容管理', 'url'=>'?u=content-index.html', 'imgsrc'=>'admin/ico/02.jpg'),
			array('name'=>'评论管理', 'url'=>'?u=content-comment.html', 'imgsrc'=>'admin/ico/03.jpg'),
			array('name'=>'分类管理', 'url'=>'?u=category-index.html', 'imgsrc'=>'admin/ico/04.jpg'),
		);

		//hook admin_my_control_get_used_end.php
		return $arr;
	}

	//hook admin_my_control_after.php
}
