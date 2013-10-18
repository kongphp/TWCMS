<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class plugin_control extends admin_control {
	// 插件管理
	public function index() {
		$plugins = core::get_plugins();
		$this->assign('plugins', $plugins);

		// hook admin_plugin_control_index_after.php

		$this->display();
	}

	// 插件启用
	public function enable() {
		$dir = R('dir', 'P');
		$this->check_plugin($dir);
		$plugins = $this->get_plugin_config();
		if(isset($plugins[$dir])) {
			$plugins[$dir]['enable'] = 1;
			if($this->set_plugin_config($plugins)) {
				E(0, '启用完成！');
			}else{
				E(1, '写入文件失败！');
			}
		}else{
			E(1, '启用出错，插件未安装！');
		}
	}

	// 插件停用
	public function disabled() {
		$dir = R('dir', 'P');
		$this->check_plugin($dir);
		$plugins = $this->get_plugin_config();
		if(isset($plugins[$dir])) {
			$plugins[$dir]['enable'] = 0;
			if($this->set_plugin_config($plugins)) {
				E(0, '停用完成！');
			}else{
				E(1, '写入文件失败！');
			}
		}else{
			E(1, '停用出错，插件未安装！');
		}
	}

	// 插件删除
	public function delete() {
		$dir = R('dir', 'P');
		$this->check_plugin($dir);

		$plugins = $this->get_plugin_config();

		// 只允许删除停用或未安装的插件
		if(empty($plugins[$dir]['enable'])) {
			if(_rmdir(PLUGIN_PATH.$dir)) {
				if(isset($plugins[$dir])) {
					unset($plugins[$dir]);
					if($this->set_plugin_config($plugins)) {
						E(1, '写入文件失败！');
					}
				}
				E(0, '删除完成！');
			}else{
				E(1, '删除出错！');
			}
		}else{
			E(1, '启用的插件不允许删除！');
		}
	}

	// 插件设置
	public function setting() {
		$dir = R('dir');
		$this->check_plugin($dir);
		$this->assign('dir', $dir);
		$setting = PLUGIN_PATH.$dir.'/setting.php';
		if(is_file($setting)) {
			include $setting;
		}else{
			echo 'setting.php 文件不存在！';
		}
	}

	// 检查是否为合法的插件名
	private function check_plugin($dir) {
		if(empty($dir)) {
			E(1, '插件目录名不能为空！');
		}elseif(preg_match('/\W/', $dir)) {
			E(1, '插件目录名不正确！');
		}elseif(!is_dir(PLUGIN_PATH.$dir)) {
			E(1, '插件目录名不存在！');
		}
	}

	// 获取插件配置信息
	private function get_plugin_config() {
		return is_file(CONFIG_PATH.'plugin.inc.php') ? (array)include(CONFIG_PATH.'plugin.inc.php') : array();
	}

	// 设置插件配置信息
	private function set_plugin_config($plugins) {
		return file_put_contents(CONFIG_PATH.'plugin.inc.php', "<?php\nreturn ".var_export($plugins, TRUE).";\n?>");
	}

	// hook admin_plugin_control_after.php
}
