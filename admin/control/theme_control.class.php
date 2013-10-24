<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
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

		$theme_dir = APP_PATH.'view/'.$dir;
		$err = 1;
		if(empty($dir)) {
			$s = '主题目录名不能为空！';
		}elseif(preg_match('/\W/', $dir)) {
			$s = '主题目录名不正确！';
		}elseif(is_dir($theme_dir)) {
			$s = '主题已经安装过！';
		}else{
			if(function_exists('set_time_limit')) {
				set_time_limit(600); // 10分钟
				$timeout = 300;
			}else{
				$timeout = 20;
			}

			$url = 'http://www.twcms.cn/app/download.php?theme='.$dir;
			$s = fetch_url($url, $timeout);
			if(empty($s) || substr($s, 0, 2) != 'PK') {
				$s = '下载主题失败!';
			}else{
				$zipfile = $theme_dir.'.zip';
				file_put_contents($zipfile, $s);
				kp_zip::unzip($zipfile, $theme_dir);
				unlink($zipfile);
				$s = '下载并解压完成!';
				$err = 0;
			}
		}

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
