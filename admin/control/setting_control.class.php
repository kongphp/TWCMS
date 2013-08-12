<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class setting_control extends admin_control {
	// 基本设置
	public function index() {
		// hook admin_setting_control_index_end.php
		$this->display();
	}

	// SEO设置
	public function seo() {
		// hook admin_setting_control_seo_end.php
		$this->display();
	}

	// 链接设置
	public function link() {
		// hook admin_setting_control_link_end.php
		$this->display();
	}

	// 附件设置
	public function attach() {
		// hook admin_setting_control_attach_end.php
		$this->display();
	}

	//hook admin_setting_control_after.php
}
