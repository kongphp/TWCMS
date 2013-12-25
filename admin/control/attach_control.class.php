<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class attach_control extends admin_control {
	// 上传图集和缩略图
	public function upload_image() {
		$type = R('type');
		$mid = max(2, (int)R('mid'));
		$table = $this->models->get_table($mid);
		$cfg = $this->runtime->xget();

		$updir = 'upload/'.$table.'/';
		$config = array(
			'maxSize'=>$cfg['up_img_max_size'],
			'allowExt'=>$cfg['up_img_ext'],
			'upDir'=>TWCMS_PATH.$updir,
		);
		$this->cms_content_attach->table = 'cms_'.$table.'_attach';
		$info = $this->cms_content_attach->upload($this->_user['uid'], $config);

		if($info['state'] == 'SUCCESS') {
			$path = $updir.$info['path'];
			$src_file = TWCMS_PATH.$path;
			$dst_file = image::thumb_name($src_file);
			$thumb = str_replace(TWCMS_PATH, '', $dst_file);

			$thumb_w = $cfg['thumb_'.$table.'_w'];
			$thumb_h = $cfg['thumb_'.$table.'_h'];
			$thumb_h = $cfg['thumb_'.$table.'_h'];

			if($type == 'img') { // 图集
				image::thumb($src_file, $dst_file, $thumb_w, $thumb_h, $cfg['thumb_type'], $cfg['thumb_quality']);
				if(R('ajax')) {
					echo '{"path":"'.$path.'","thumb":"'.$thumb.'","state":"'.$info['state'].'"}';
				}else{
					echo '<script>parent.setDisplayImg("'.$path.'","'.$thumb.'");</script>';
				}
			}elseif($type == 'pic') { // 缩略图
				image::thumb($src_file, $dst_file, $thumb_w, $thumb_h, $cfg['thumb_type'], $cfg['thumb_quality']);
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
