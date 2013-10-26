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

		// 检查是否有图标和设置功能
		foreach($plugins as &$arr) {
			if(isset($arr) && is_array($arr)) {
				foreach($arr as $dir => &$v) {
					is_file(PLUGIN_PATH.$dir.'/show.jpg') && $v['is_show'] = 1;
					is_file(PLUGIN_PATH.$dir.'/setting.php') && $v['is_setting'] = 1;
				}
			}
		}

		$this->assign('plugins', $plugins);
		$this->display();
	}

	// 插件启用
	public function enable() {
		$dir = R('dir', 'P');
		$this->check_plugin($dir);
		$plugins = $this->get_plugin_config();
		isset($plugins[$dir]) || E(1, '启用出错，插件未安装！');

		// 如果是编辑器插件，卸载其他编辑器插件
		if(substr($dir, 0, 7) == 'editor_') {
			foreach($plugins as $k => $v) {
				substr($k, 0, 7) == 'editor_' && $plugins[$k]['enable'] = 0;
			}
		}

		$plugins[$dir]['enable'] = 1;
		if($this->set_plugin_config($plugins)) {
			$this->clear_cache();
			E(0, '启用完成！');
		}else{
			E(1, '写入文件失败！');
		}
	}

	// 插件停用
	public function disabled() {
		$dir = R('dir', 'P');
		$this->check_plugin($dir);
		$plugins = $this->get_plugin_config();
		isset($plugins[$dir]) || E(1, '停用出错，插件未安装！');

		$plugins[$dir]['enable'] = 0;
		if($this->set_plugin_config($plugins)) {
			$this->clear_cache();
			E(0, '停用完成！');
		}else{
			E(1, '写入文件失败！');
		}
	}

	// 插件删除
	public function delete() {
		$dir = R('dir', 'P');
		$this->check_plugin($dir);

		$plugins = $this->get_plugin_config();

		// 只允许删除停用或未安装的插件
		if(empty($plugins[$dir]['enable'])) {
			// 检测有 uninstall.php 文件，则执行卸载
			$uninstall = PLUGIN_PATH.$dir.'/uninstall.php';
			if(is_file($uninstall)) {
				include $uninstall;
			}

			if(_rmdir(PLUGIN_PATH.$dir)) {
				if(isset($plugins[$dir])) {
					unset($plugins[$dir]);
					if(!$this->set_plugin_config($plugins)) {
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

	// 插件安装
	public function install() {
		$dir = R('dir', 'P');
		$this->check_plugin($dir);

		$plugins = $this->get_plugin_config();
		isset($plugins[$dir]) && E(1, '插件已经安装过！');

		$cms_version = $this->get_version($dir);
		$cms_version && version_compare($cms_version, C('version'), '>') && E(1, '无法安装，最低版本要求：TWCMS '.$cms_version);

		// 检测有 install.php 文件，则执行安装
		$install = PLUGIN_PATH.$dir.'/install.php';
		if(is_file($install)) include $install;

		$plugins[$dir] = array('enable' => 0);
		if(!$this->set_plugin_config($plugins)) E(1, '写入文件失败！');

		E(0, '安装完成！');
	}

	// 在线安装插件
	public function install_plugin() {
		$dir = R('dir');

		if(empty($dir)) $this->install_tips('插件目录名不能为空！');
		if(preg_match('/\W/', $dir)) $this->install_tips('插件目录名不正确！');
		$install_dir = PLUGIN_PATH.$dir;
		if(is_dir($install_dir)) $this->install_tips('插件目录已存在，已安装过？');

		$this->download($dir);
		$zipfile = $install_dir.'.zip';
		try{
			kp_zip::unzip($zipfile, $install_dir);
		}catch(Exception $e) {
			$this->install_tips('解压插件文件出错！');
		}
		unlink($zipfile);

		// ======  开始安装 ======
		$cms_version = $this->get_version($dir);
		$cms_version && version_compare($cms_version, C('version'), '>') && $this->install_tips('无法安装，最低版本要求：TWCMS '.$cms_version);

		// 检测有 install.php 文件，则执行安装
		$install = PLUGIN_PATH.$dir.'/install.php';
		if(is_file($install)) include $install;

		$plugins = $this->get_plugin_config();
		$plugins[$dir] = array('enable' => 0);
		if(!$this->set_plugin_config($plugins)) $this->install_tips('写入配置文件失败！');

		$this->install_tips('下载并安装完成！', 0);
	}

	// 在线升级插件
	public function upgrade() {
		$dir = R('dir');

		if(empty($dir)) $this->install_tips('插件目录名不能为空！');
		if(preg_match('/\W/', $dir)) $this->install_tips('插件目录名不正确！');

		// 1. 下载插件
		$this->download($dir, TRUE);

		// 2. 删除旧版插件
		$install_dir = PLUGIN_PATH.$dir;
		_rmdir($install_dir);

		// 3. 解压新版插件
		$zipfile = $install_dir.'.zip';
		try{
			kp_zip::unzip($zipfile, $install_dir);
		}catch(Exception $e) {
			$this->install_tips('解压插件文件出错！');
		}

		// 4. 删除安装包
		unlink($zipfile);

		// 5. 判断是否已安装
		$plugins = $this->get_plugin_config();
		if(isset($plugins[$dir])) {
			// 检测有 upgrade.php 文件，则执行升级
			$upgrade = PLUGIN_PATH.$dir.'/upgrade.php';
			if(is_file($upgrade)) include $upgrade;
		}

		// 6. 清除缓存
		$this->clear_cache();

		$this->install_tips('下载并升级完成！', 0);
	}

	// 在线下载插件
	private function download($dir, $is_upgrade = FALSE) {
		if(function_exists('set_time_limit')) {
			set_time_limit(600); // 10分钟
			$timeout = 300;
		}else{
			$timeout = 20;
		}

		$url = 'http://www.twcms.cn/app/download.php?plugin='.$dir.($is_upgrade ? '&upgrade=1' : '');
		try{
			$s = fetch_url($url, $timeout);
			if(empty($s) || substr($s, 0, 2) != 'PK') throw new Exception();
		}catch(Exception $e) {
			$this->install_tips('下载插件失败!');
		}
		try{
			file_put_contents(PLUGIN_PATH.$dir.'.zip', $s);
		}catch(Exception $e) {
			$this->install_tips('插件写入出错，写入权限不对？');
		}
	}

	// 在线安装提示
	private function install_tips($s, $err = 1) {
		echo '$(".ajaxtips b").html("'.$s.'");';
		echo 'var err = '.$err.';';
		exit;
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

	// 检查版本
	private function get_version($dir) {
		$cfg = is_file(PLUGIN_PATH.$dir.'/conf.php') ? (array)include(PLUGIN_PATH.$dir.'/conf.php') : array();
		return isset($cfg['cms_version']) ? $cfg['cms_version'] : 0;
	}

	// 获取插件配置信息
	private function get_plugin_config() {
		return is_file(CONFIG_PATH.'plugin.inc.php') ? (array)include(CONFIG_PATH.'plugin.inc.php') : array();
	}

	// 设置插件配置信息
	private function set_plugin_config($plugins) {
		return file_put_contents(CONFIG_PATH.'plugin.inc.php', "<?php\nreturn ".var_export($plugins, TRUE).";\n?>");
	}
}
