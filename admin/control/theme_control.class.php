<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class theme_control extends admin_control {
	// 主题设置
	public function index() {
		// hook admin_theme_control_index_before.php

		$cfg = $this->runtime->xget('cfg');
		$k = &$cfg['theme'];
		$themes = self::get_theme_all();

		// 启用的主题放在第一
		if(isset($themes[$k])) {
			$tmp = array();
			$tmp[$k] = $themes[$k];
			unset($themes[$k]);
			$themes = $tmp + $themes;
		}

		$this->assign('themes', $themes);
		$this->assign('theme', $cfg['theme']);

		// hook admin_theme_control_index_after.php

		$this->display();
	}

	// 启用主题
	public function enable() {
		$theme = R('theme', 'P');
		$this->check_theme($theme);

		$this->kv->xset('theme', $theme, 'cfg');
		$this->kv->save_changed();
		$this->runtime->delete('cfg');
		$this->clear_cache();
		E(0, '启用成功！');
	}

	// 删除主题
	public function delete() {
		$theme = R('theme', 'P');
		$this->check_theme($theme);

		if(_rmdir(APP_PATH.'view/'.$theme)) {
			E(0, '删除完成！');
		}else{
			E(1, '删除出错！');
		}
	}

	// 在线安装插件
	public function install_theme() {
		$dir = R('dir');

		if(empty($dir)) $this->install_tips('主题目录名不能为空！');
		if(preg_match('/\W/', $dir)) $this->install_tips('主题目录名不正确！');
		$install_dir = APP_PATH.'view/'.$dir;
		if(is_dir($install_dir)) $this->install_tips('此主题已经安装过了！');

		if(function_exists('set_time_limit')) {
			set_time_limit(600); // 10分钟
			$timeout = 300;
		}else{
			$timeout = 20;
		}

		$url = 'http://www.twcms.cn/app/download.php?theme='.$dir;
		try{
			$s = fetch_url($url, $timeout);
		}catch(Exception $e) {
			$this->install_tips('下载主题出错！');
		}
		if(empty($s) || substr($s, 0, 2) != 'PK') {
			$this->install_tips('下载主题失败!');
		}
		$zipfile = $install_dir.'.zip';
		try{
			file_put_contents($zipfile, $s);
		}catch(Exception $e) {
			$this->install_tips('主题写入出错，写入权限不对？');
		}
		try{
			kp_zip::unzip($zipfile, $install_dir);
		}catch(Exception $e) {
			$this->install_tips('解压主题文件出错！');
		}
		unlink($zipfile);
		$this->install_tips('下载并解压完成！', 0);
	}

	// 在线安装提示
	private function install_tips($s, $err = 1) {
		echo '$(".ajaxtips b").html("'.$s.'");';
		echo 'var err = '.$err.';';
		exit;
	}

	// 检查是否为合法的主题名
	private function check_theme($dir) {
		if(empty($dir)) {
			E(1, '主题目录名不能为空！');
		}elseif(preg_match('/\W/', $dir)) {
			E(1, '主题目录名不正确！');
		}elseif(!is_dir(APP_PATH.'view/'.$dir)) {
			E(1, '主题目录名不存在！');
		}
	}

	// 读取所有主题
	private function get_theme_all() {
		$dir = APP_PATH.'view/';
		$files = _scandir($dir);
		$themes = array();
		foreach($files as $file) {
			if(preg_match('/\W/', $file)) continue;
			$path = $dir.'/'.$file;
			$info = $path.'/info.ini';
			if(filetype($path) == 'dir' && is_file($info) && $lines = file($info)) {
				$themes[$file] = self::get_theme_info($lines);
			}
		}
		return $themes;
	}

	// 读取主题信息
	private function get_theme_info($lines) {
		$res = array();
		foreach($lines as $str) {
			$arr = explode('=', trim($str));
			$k = trim($arr[0]);
			$v = isset($arr[1]) ? trim($arr[1]) : '';
			if($k == 'brief') {
				$res[$k] = strip_tags($v, '<br>');
			}elseif(in_array($k, array('name', 'version', 'update', 'author', 'authorurl'))) {
				$res[$k] = strip_tags($v);
			}
		}
		return $res;
	}

	// hook admin_theme_control_after.php
}
