<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
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
			$input['dis_comment'] = empty($cfg['dis_comment']) ? 0 : 1;

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
			$this->kv->xset('dis_comment', (int)R('dis_comment', 'P'), 'cfg');

			// hook admin_setting_control_index_post_after.php

			$this->kv->save_changed();
			$this->runtime->delete('cfg');

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
			$this->runtime->delete('cfg');

			exit('{"err":0, "msg":"修改成功"}');
		}
	}

	// 链接设置
	public function link() {
		if(empty($_POST)) {
			$link_switch = $_ENV['_config']['twcms_parseurl'];
			$cfg = $this->kv->xget('cfg');
			$input = array();
			$input['link_switch'] = form::loop('radio', 'link_switch', array('0'=>'动态', '1'=>'伪静态'), $link_switch, ' &nbsp; &nbsp;');
			$input['link_index_end'] = form::get_text('link_index_end', $cfg['link_index_end']);
			$input['link_cate_end'] = form::get_text('link_cate_end', $cfg['link_cate_end']);
			$input['link_cate_page_pre'] = form::get_text('link_cate_page_pre', $cfg['link_cate_page_pre']);
			$input['link_cate_page_end'] = form::get_text('link_cate_page_end', $cfg['link_cate_page_end']);
			$input['link_show'] = form::get_text('link_show', $cfg['link_show']);
			$input['link_tag_pre'] = form::get_text('link_tag_pre', $cfg['link_tag_pre']);
			$input['link_tag_end'] = form::get_text('link_tag_end', $cfg['link_tag_end']);
			$input['link_comment_pre'] = form::get_text('link_comment_pre', $cfg['link_comment_pre']);
			$input['link_comment_end'] = form::get_text('link_comment_end', $cfg['link_comment_end']);

			// hook admin_setting_control_link_after.php

			$this->assign('input', $input);
			$this->display();
		}else{
			_trim($_POST);
			// 伪静态开关
			$link_switch = (int)R('link_switch', 'P');
			$file = APP_PATH.'config/config.inc.php';
			if(!_is_writable($file)) exit('{"err":1, "msg":"配置文件 twcms/config/config.inc.php 不可写！"}');
			$s = file_get_contents($file);
			$s = preg_replace("#'twcms_parseurl'\s*=>\s*\d,#", "'twcms_parseurl' => {$link_switch},", $s);
			if(!file_put_contents($file, $s)) exit('{"err":1, "msg":"写入 config.inc.php 失败"}');

			$this->kv->xset('link_index_end', R('link_index_end', 'P'), 'cfg');
			$this->kv->xset('link_cate_end', R('link_cate_end', 'P'), 'cfg');
			$this->kv->xset('link_cate_page_pre', R('link_cate_page_pre', 'P'), 'cfg');
			$this->kv->xset('link_cate_page_end', R('link_cate_page_end', 'P'), 'cfg');
			$this->kv->xset('link_show', R('link_show', 'P'), 'cfg');
			$this->kv->xset('link_tag_pre', R('link_tag_pre', 'P'), 'cfg');
			$this->kv->xset('link_tag_end', R('link_tag_end', 'P'), 'cfg');
			$this->kv->xset('link_comment_pre', R('link_comment_pre', 'P'), 'cfg');
			$this->kv->xset('link_comment_end', R('link_comment_end', 'P'), 'cfg');

			// hook admin_setting_control_link_post_after.php

			$this->kv->save_changed();
			$this->runtime->delete('cfg');

			exit('{"err":0, "msg":"修改成功"}');
		}
	}

	// 上传设置
	public function attach() {
		if(empty($_POST)) {
			$cfg = $this->kv->xget('cfg');
			$input = array();
			$input['up_img_ext'] = form::get_text('up_img_ext', $cfg['up_img_ext'], 'inp wa');
			$input['up_img_max_size'] = form::get_number('up_img_max_size', $cfg['up_img_max_size'], 'inp ws');
			$input['up_file_ext'] = form::get_text('up_file_ext', $cfg['up_file_ext'], 'inp wa');
			$input['up_file_max_size'] = form::get_number('up_file_max_size', $cfg['up_file_max_size'], 'inp ws');

			// hook admin_setting_control_attach_after.php

			$this->assign('input', $input);
			$this->display();
		}else{
			_trim($_POST);
			$this->kv->xset('up_img_ext', R('up_img_ext', 'P'), 'cfg');
			$this->kv->xset('up_img_max_size', R('up_img_max_size', 'P'), 'cfg');
			$this->kv->xset('up_file_ext', R('up_file_ext', 'P'), 'cfg');
			$this->kv->xset('up_file_max_size', R('up_file_max_size', 'P'), 'cfg');

			// hook admin_setting_control_attach_post_after.php

			$this->kv->save_changed();
			$this->runtime->delete('cfg');

			exit('{"err":0, "msg":"修改成功"}');
		}
	}

	// 图片设置
	public function image() {
		if(empty($_POST)) {
			$cfg = $this->kv->xget('cfg');
			$input = array();
			$input['thumb_article_w'] = form::get_number('thumb_article_w', $cfg['thumb_article_w'], 'inp ws');
			$input['thumb_article_h'] = form::get_number('thumb_article_h', $cfg['thumb_article_h'], 'inp ws');
			$input['thumb_product_w'] = form::get_number('thumb_product_w', $cfg['thumb_product_w'], 'inp ws');
			$input['thumb_product_h'] = form::get_number('thumb_product_h', $cfg['thumb_product_h'], 'inp ws');
			$input['thumb_photo_w'] = form::get_number('thumb_photo_w', $cfg['thumb_photo_w'], 'inp ws');
			$input['thumb_photo_h'] = form::get_number('thumb_photo_h', $cfg['thumb_photo_h'], 'inp ws');

			$input['thumb_type'] = form::loop('radio', 'thumb_type', array('1'=>'补白', '2'=>'居中', '3'=>'上左'), $cfg['thumb_type'], ' &nbsp; &nbsp;');
			$input['thumb_quality'] = form::get_number('thumb_quality', $cfg['thumb_quality'], 'inp ws');

			$cfg['watermark_pos'] = isset($cfg['watermark_pos']) ? (int)$cfg['watermark_pos'] : 0;
			$input['watermark_pct'] = form::get_number('watermark_pct', $cfg['watermark_pct'], 'inp ws');

			// hook admin_setting_control_image_after.php

			$this->assign('input', $input);
			$this->assign('cfg', $cfg);
			$this->display();
		}else{
			$this->kv->xset('thumb_article_w', (int) R('thumb_article_w', 'P'), 'cfg');
			$this->kv->xset('thumb_article_h', (int) R('thumb_article_h', 'P'), 'cfg');
			$this->kv->xset('thumb_product_w', (int) R('thumb_product_w', 'P'), 'cfg');
			$this->kv->xset('thumb_product_h', (int) R('thumb_product_h', 'P'), 'cfg');
			$this->kv->xset('thumb_photo_w', (int) R('thumb_photo_w', 'P'), 'cfg');
			$this->kv->xset('thumb_photo_h', (int) R('thumb_photo_h', 'P'), 'cfg');
			$this->kv->xset('thumb_type', (int) R('thumb_type', 'P'), 'cfg');
			$this->kv->xset('thumb_quality', (int) R('thumb_quality', 'P'), 'cfg');
			$this->kv->xset('watermark_pos', (int) R('watermark_pos', 'P'), 'cfg');
			$this->kv->xset('watermark_pct', (int) R('watermark_pct', 'P'), 'cfg');

			// hook admin_setting_control_image_post_after.php

			$this->kv->save_changed();
			$this->runtime->delete('cfg');

			exit('{"err":0, "msg":"修改成功"}');
		}
	}

	// hook admin_setting_control_after.php
}
