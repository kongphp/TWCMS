<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class attach_control extends admin_control {
	// 上传图集和缩略图
	public function upload_image() {
		// hook admin_attach_control_upload_image_before.php

		$type = R('type');
		$mid = max(2, (int)R('mid'));
		$cid = (int)R('cid');
		$id = (int)R('id');
		$table = $this->models->get_table($mid);
		$cfg = $this->runtime->xget();

		$updir = 'upload/'.$table.'/';
		$config = array(
			'maxSize'=>$cfg['up_img_max_size'],
			'allowExt'=>$cfg['up_img_ext'],
			'upDir'=>TWCMS_PATH.$updir,
		);
		$this->cms_content_attach->table = 'cms_'.$table.'_attach';
		$info = $this->cms_content_attach->uploads($config, $this->_user['uid'], $cid, $id);

		if($info['state'] == 'SUCCESS') {
			$path = $updir.$info['path'];
			$thumb = image::thumb_name($path);
			$src_file = TWCMS_PATH.$path;
			image::thumb($src_file, TWCMS_PATH.$thumb, $cfg['thumb_'.$table.'_w'], $cfg['thumb_'.$table.'_h'], $cfg['thumb_type'], $cfg['thumb_quality']);

			// 核心功能不打算做复杂了，想生成更多尺寸的图片建议使用此接口做成插件。
			// hook admin_attach_control_upload_image_success_after.php

			// 是否添加水印
			if(!empty($cfg['watermark_pos'])) {
				image::watermark($src_file, TWCMS_PATH.'static/img/watermark.png', null, $cfg['watermark_pos'], $cfg['watermark_pct']);
			}

			if($type == 'img') { // 图集
				if(R('ajax')) {
					echo '{"path":"'.$path.'","thumb":"'.$thumb.'","state":"'.$info['state'].'"}';
				}else{
					echo '<script>parent.setDisplayImg("'.$path.'","'.$thumb.'");</script>';
				}
			}else{ // 缩略图
				echo '<script>parent.setDisplayPic("'.$path.'","'.$thumb.'");</script>';
			}
		}else{
			if(R('ajax')) {
				echo '{"path":"","state":"'.$info['state'].'"}';
			}else{
				echo '<script>alert("'.$info['state'].'");</script>';
			}
		}
		exit;
	}

	// hook admin_attach_control_after.php
}
