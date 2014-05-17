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
			!empty($_POST['runtime_db']) && $this->runtime->truncate();
			!empty($_POST['runtime_file']) && $this->un_filecache();
			E(0, '清除完成！');
		}

		$this->display();
	}

	// 重新统计
	public function rebuild() {
		if(!empty($_POST)) {
			// 重新统计分类的内容数量
			if(!empty($_POST['re_cate'])) {
				$tables = $this->models->get_table_arr();
				$cids = $this->category->get_category_db();

				foreach($cids as $row) {
					if($row['mid'] == 1) continue;

					$this->cms_content->table = 'cms_'.(isset($tables[$row['mid']]) ? $tables[$row['mid']] : 'article');
					$count = $this->cms_content->find_count(array('cid'=>$row['cid']));

					$this->category->update(array('cid'=>$row['cid'], 'count'=>$count));
				}
			}

			// 清空数据表的 count max 值，让其重新统计
			if(!empty($_POST['re_table'])) {
				$this->db->truncate('framework_count');
				$this->db->truncate('framework_maxid');
			}

			E(0, '重新统计完成！');
		}

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
