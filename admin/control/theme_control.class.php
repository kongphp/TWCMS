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
		if($theme) {
			if(preg_match('/\W/', $theme)) {
				E(1, '主题目录名不正确！');
			}else{
				$this->kv->xset('theme', $theme, 'cfg');
				$this->kv->save_changed();
				$this->runtime->delete('cfg');
				E(0, '启用成功！');
			}
		}
	}

	// 删除主题
	public function delete() {
		$theme = R('theme', 'P');
		if($theme) {
			if(preg_match('/\W/', $theme)) {
				E(1, '主题目录名不正确！');
			}else{
				if(_rmdir(APP_PATH.'view/'.$theme)) {
					E(0, '删除完成！');
				}else{
					E(1, '删除出错！');
				}
			}
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
