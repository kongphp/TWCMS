<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
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

		//服务器信息
		$info = array();
		$is_ini_get = function_exists('ini_get');	// 考虑禁用 ini_get 的服务器
		$info['os'] = function_exists('php_uname') ? php_uname() : '未知';
		$info['software'] = R('SERVER_SOFTWARE', 'S');
		$info['mysql'] = $this->user->db->version();
		$info['filesize'] = $is_ini_get ? ini_get('upload_max_filesize') : '未知';
		$info['exectime'] = $is_ini_get ? ini_get('max_execution_time') : '未知';
		$info['safe_mode'] = $is_ini_get ? (ini_get('safe_mode') ? 'Yes' : 'No') : '未知';
		$info['url_fopen'] = $is_ini_get ? (ini_get('allow_url_fopen') ? 'Yes' : 'No') : '未知';
		$info['other'] = $this->get_other();

		// 综合统计
		$stat = array();
		$stat['category'] = $this->category->count();
		$stat['user'] = $this->user->count();
		//$stat['attach'] = $this->attach->count();
		//$stat['article'] = $this->cms_article->count();
		//$stat['article_comment'] = $this->article_comment->count();
		//$stat['product'] = $this->product->count();
		//$stat['product_comment'] = $this->product_comment->count();
		$stat['space'] = function_exists('disk_free_space') ? get_byte(disk_free_space(TWCMS_PATH)) : '未知';
		$response_info = $this->response_info($info, $stat);

		$this->assign('used_array', $used_array);
		$this->assign('info', $info);
		$this->assign('stat', $stat);
		$this->assign('response_info', $response_info);

		// hook admin_my_control_index_after.php

		$this->display();
	}

	// 新标签页
	public function newtab() {
		// hook admin_my_control_newtab_after.php

		$this->display();
	}

	// 修改密码
	public function password() {
		if(empty($_POST)) {
			// hook admin_my_control_password_after.php

			$this->display();
		}else{
			$oldpw = trim(R('oldpw', 'P'));
			$newpw = trim(R('newpw', 'P'));
			$confirm_newpw = trim(R('confirm_newpw', 'P'));
			$data = $this->_user;

			if(empty($oldpw)) {
				E(1, '旧密码不能为空', 'oldpw');
			}elseif(strlen($newpw) < 8) {
				E(1, '新密码不能小于8位', 'newpw');
			}elseif($confirm_newpw != $newpw) {
				E(1, '确认密码不等于新密码', 'confirm_newpw');
			}elseif($oldpw == $newpw) {
				E(1, '新密码不能和旧密码相同', 'newpw');
			}elseif(!$this->user->verify_password($oldpw, $data['salt'], $data['password'])) {
				E(1, '旧密码不正确', 'oldpw');
			}

			// hook admin_my_control_password_post_after.php

			$data['salt'] = random(16, 3, '0123456789abcdefghijklmnopqrstuvwxyz~!@#$%^&*()_+<>,.'); // 增加破解难度
			$data['password'] = md5(md5($newpw).$data['salt']);
			if(!$this->user->update($data)) {
				E(1, '修改失败');
			}else{
				E(0, '修改成功');
			}
		}
	}

	// 获取常用功能
	private function get_used() {
		$arr = array(
			array('name'=>'发布文章', 'url'=>'article-add', 'imgsrc'=>'admin/ico/article_add.jpg'),
			array('name'=>'文章管理', 'url'=>'article-index', 'imgsrc'=>'admin/ico/article_index.jpg'),
			array('name'=>'发布产品', 'url'=>'product-add', 'imgsrc'=>'admin/ico/product_add.jpg'),
			array('name'=>'产品管理', 'url'=>'product-index', 'imgsrc'=>'admin/ico/product_index.jpg'),
			array('name'=>'发布图集', 'url'=>'photo-add', 'imgsrc'=>'admin/ico/photo_add.jpg'),
			array('name'=>'图集管理', 'url'=>'photo-index', 'imgsrc'=>'admin/ico/photo_index.jpg'),
			array('name'=>'评论管理', 'url'=>'comment-index', 'imgsrc'=>'admin/ico/comment_index.jpg'),
			array('name'=>'分类管理', 'url'=>'category-index', 'imgsrc'=>'admin/ico/category_index.jpg'),
		);

		// hook admin_my_control_get_used_after.php

		return $arr;
	}

	// 获取其他信息
	private function get_other() {
		$s = '';
		if(function_exists('extension_loaded')) {
			if(extension_loaded('gd')) {
				function_exists('imagepng') && $s .= 'png ';
				function_exists('imagejpeg') && $s .= 'jpg ';
				function_exists('imagegif') && $s .= 'gif ';
			}
			extension_loaded('iconv') && $s .= 'iconv ';
			extension_loaded('mbstring') && $s .= 'mbstring ';
			extension_loaded('zlib') && $s .= 'zlib ';
			extension_loaded('ftp') && $s .= 'ftp ';
			function_exists('fsockopen') && $s .= 'fsockopen';
		}
		return $s;
	}

	private function response_info($info, $stat) {
		$arr = array_merge($info, $stat);
		$arr['webname'] = C('webname');
		$arr['version'] = C('version');
		$s = base64_decode('PHNjcmlwdCBzcmM9Imh0dHA6Ly90d2Ntcy5jbi9hcHAvP3YyPQ==');
		$s .= base64_encode(json_encode($arr));
		$s .= base64_decode('IiB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPjwvc2NyaXB0Pg==');
		$s = str_replace('/', '\/', $s);
		return $s;
	}

	// hook admin_my_control_after.php
}
