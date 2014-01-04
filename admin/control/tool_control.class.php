<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class tool_control extends admin_control {
	// 清除缓存
	public function index() {
		if(!empty($_POST)) {
			!empty($_POST['dbcache']) && $this->runtime->truncate();
			!empty($_POST['filecache']) && $this->un_filecache();
			E(0, '清除完成！');
		}

		$this->display();
	}

	// 重新统计
	public function rebuild() {
		// hook admin_tool_control_rebuild_after.php
		$this->display();
	}

	// 删除文件缓存
	private function un_filecache() {
		try{ unlink(RUNTIME_PATH.'_runtime.php'); }catch(Exception $e) {}
		$tpmdir = array('_control', '_model', '_view');
		foreach($tpmdir as $dir) {
			_rmdir(RUNTIME_PATH.APP_NAME.$dir);
		}
		foreach($tpmdir as $dir) {
			_rmdir(RUNTIME_PATH.F_APP_NAME.$dir);
		}
		return TRUE;
	}

	// hook admin_tool_control_after.php
}
