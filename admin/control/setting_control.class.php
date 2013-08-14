<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class setting_control extends admin_control {
	// 基本设置
	public function index() {
		if(empty($_POST)) {
			$cfg = $this->kv->xget('cfg');
			$input = array();
			$input['webname'] = form::get_text('webname', $cfg['webname']);
			$input['webdomain'] = form::get_text('webdomain', $cfg['webdomain']);
			$input['webdir'] = form::get_text('webdir', $cfg['webdir']);
			$input['webmail'] = form::get_text('webmail', $cfg['webmail']);
			$input['tongji'] = form::get_textarea('tongji', $cfg['tongji']);
			$input['beian'] = form::get_text('beian', $cfg['beian']);

			// hook admin_setting_control_index_after.php

			$this->assign('input', $input);
			$this->display();
		}else{
			_trim($_POST);
			$this->kv->xset('webname', R('webname', 'P'), 'cfg');
			$this->kv->xset('webdomain', R('webdomain', 'P'), 'cfg');
			$this->kv->xset('webdir', R('webdir', 'P'), 'cfg');
			$this->kv->xset('webmail', R('webmail', 'P'), 'cfg');
			$this->kv->xset('tongji', R('tongji', 'P'), 'cfg');
			$this->kv->xset('beian', R('beian', 'P'), 'cfg');

			// hook admin_setting_control_index_post_after.php

			$this->kv->save_changed();
			exit('{"err":0, "msg":"修改成功"}');
		}
	}

	// SEO设置
	public function seo() {
		if(empty($_POST)) {
			$cfg = $this->kv->xget('cfg');
			$input = array();
			$input['seo_title'] = form::get_text('seo_title', $cfg['seo_title']);
			$input['seo_keywords'] = form::get_text('seo_keywords', $cfg['seo_keywords']);
			$input['seo_description'] = form::get_textarea('seo_description', $cfg['seo_description']);

			// hook admin_setting_control_seo_after.php

			$this->assign('input', $input);
			$this->display();
		}else{
			_trim($_POST);
			$this->kv->xset('seo_title', R('seo_title', 'P'), 'cfg');
			$this->kv->xset('seo_keywords', R('seo_keywords', 'P'), 'cfg');
			$this->kv->xset('seo_description', R('seo_description', 'P'), 'cfg');

			// hook admin_setting_control_seo_post_after.php

			$this->kv->save_changed();
			exit('{"err":0, "msg":"修改成功"}');
		}
	}

	// 链接设置
	public function link() {
		$this->display();
	}

	// 附件设置
	public function attach() {
		if(empty($_POST)) {
			$cfg = $this->kv->xget('cfg');
			$input = array();
			$input['up_img_ext'] = form::get_text('up_img_ext', $cfg['up_img_ext'], 'inp wa');
			$input['up_img_max_size'] = form::get_number('up_img_max_size', $cfg['up_img_max_size'], 'inp ws');
			$input['up_file_ext'] = form::get_text('up_file_ext', $cfg['up_file_ext'], 'inp wa');
			$input['up_file_max_size'] = form::get_number('up_file_max_size', $cfg['up_file_max_size'], 'inp ws');
			$input['get_file_ext'] = form::get_text('get_file_ext', $cfg['get_file_ext'], 'inp wa');
			$input['get_file_max_size'] = form::get_number('get_file_max_size', $cfg['get_file_max_size'], 'inp ws');

			// hook admin_setting_control_attach_after.php

			$this->assign('input', $input);
			$this->display();
		}else{
			_trim($_POST);
			$this->kv->xset('up_img_ext', R('up_img_ext', 'P'), 'cfg');
			$this->kv->xset('up_img_max_size', R('up_img_max_size', 'P'), 'cfg');
			$this->kv->xset('up_file_ext', R('up_file_ext', 'P'), 'cfg');
			$this->kv->xset('up_file_max_size', R('up_file_max_size', 'P'), 'cfg');
			$this->kv->xset('get_file_ext', R('get_file_ext', 'P'), 'cfg');
			$this->kv->xset('get_file_max_size', R('get_file_max_size', 'P'), 'cfg');

			// hook admin_setting_control_attach_post_after.php

			$this->kv->save_changed();
			exit('{"err":0, "msg":"修改成功"}');
		}
	}

	//hook admin_setting_control_after.php
}
