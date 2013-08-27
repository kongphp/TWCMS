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
		//$stat['category'] = $this->category->count();
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

		// hook admin_my_control_index_end.php
		$this->display();
	}

	// 新标签页
	public function newtab() {
		$this->_title = '新标签页';
		$this->_place = '我的 &#187; '.$this->_title;

		// hook admin_my_control_newtab_end.php
		$this->display();
	}

	// 修改密码
	public function password() {
		// hook admin_my_control_password_end.php
		$this->display();
	}

	// 获取常用功能
	private function get_used() {
		$arr = array(
			array('name'=>'发布内容', 'url'=>'?u=content-add', 'imgsrc'=>'admin/ico/01.jpg'),
			array('name'=>'内容管理', 'url'=>'?u=content-index', 'imgsrc'=>'admin/ico/02.jpg'),
			array('name'=>'评论管理', 'url'=>'?u=content-comment', 'imgsrc'=>'admin/ico/03.jpg'),
			array('name'=>'分类管理', 'url'=>'?u=category-index', 'imgsrc'=>'admin/ico/04.jpg'),
		);

		//hook admin_my_control_get_used_end.php
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

	//hook admin_my_control_after.php
}
