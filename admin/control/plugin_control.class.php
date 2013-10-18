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

	// 启用插件
	public function enable() {
		$plugin = R('plugin', 'P');
		if($plugin) {
			if(preg_match('/\W/', $plugin)) {
				E(1, '插件目录名不正确！');
			}else{
				$plu_file = CONFIG_PATH.'plugin.inc.php';
				$plugins = is_file($plu_file) ? (array)include($plu_file) : array();
				if(isset($plugins[$plugin])) {
					$plugins[$plugin]['enable'] = 1;
					if(file_put_contents($plu_file, "<?php\nreturn ".var_export($plugins, TRUE).";\n?>")) {
						E(0, '启用完成！');
					}else{
						E(1, '写入文件失败！');
					}
				}else{
					E(1, '启用出错，插件未安装！');
				}
			}
		}
	}

	// 停用插件
	public function disabled() {
		$plugin = R('plugin', 'P');
		if($plugin) {
			if(preg_match('/\W/', $plugin)) {
				E(1, '插件目录名不正确！');
			}else{
				$plu_file = CONFIG_PATH.'plugin.inc.php';
				$plugins = is_file($plu_file) ? (array)include($plu_file) : array();
				if(isset($plugins[$plugin])) {
					$plugins[$plugin]['enable'] = 0;
					if(file_put_contents($plu_file, "<?php\nreturn ".var_export($plugins, TRUE).";\n?>")) {
						E(0, '停用完成！');
					}else{
						E(1, '写入文件失败！');
					}
				}else{
					E(1, '停用出错，插件未安装！');
				}
			}
		}
	}

	// 删除插件
	public function delete() {
		$plugin = R('plugin', 'P');
		if($plugin) {
			if(preg_match('/\W/', $plugin)) {
				E(1, '插件目录名不正确！');
			}else{
				$plu_file = CONFIG_PATH.'plugin.inc.php';
				$plugins = is_file($plu_file) ? (array)include($plu_file) : array();

				// 只允许删除停用或未安装的插件
				if(empty($plugins[$plugin]['enable'])) {
					if(_rmdir(PLUGIN_PATH.$plugin)) {
						if(isset($plugins[$plugin])) {
							unset($plugins[$plugin]);
							if(!file_put_contents($plu_file, "<?php\nreturn ".var_export($plugins, TRUE).";\n?>")) {
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
		}
	}

	// hook admin_plugin_control_after.php
}
