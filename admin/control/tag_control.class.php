<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class tag_control extends admin_control {
	// 标签管理
	public function index() {
		// hook admin_tag_control_index_before.php

		$mid = max(2, (int)R('mid'));
		$table = $this->models->get_table($mid);

		// 模型名称
		$mod_name = $this->models->get_name();
		if(isset($mod_name[1])) unset($mod_name[1]);
		$this->assign('mid', $mid);
		$this->assign('mod_name', $mod_name);

		$this->cms_content_tag->table = 'cms_'.$table.'_tag';

		// 初始分页
		$pagenum = 20;
		$total = $this->cms_content_tag->count();
		$maxpage = max(1, ceil($total/$pagenum));
		$page = min($maxpage, max(1, intval(R('page'))));
		$pages = pages($page, $maxpage, '?u=tag-index-mid-'.$mid.'-page-{page}');
		$this->assign('pages', $pages);
		$this->assign('total', $total);

		// 获取标签列表
		$list_arr = $this->cms_content_tag->list_arr(-1, ($page-1)*$pagenum, $pagenum, $total);
		foreach($list_arr as &$v) {
			$this->cms_content_tag->format($v, $mid);
		}

		$this->assign('list_arr', $list_arr);

		// hook admin_tag_control_index_after.php

		$this->display();
	}

	// 读取一条标签
	public function get_json() {
		// hook admin_tag_control_get_json_before.php

		$mid = max(2, (int)R('mid', 'P'));
		$table = $this->models->get_table($mid);

		$tagid = (int) R('tagid', 'P');

		$this->cms_content_tag->table = 'cms_'.$table.'_tag';
		$data = $this->cms_content_tag->read($tagid);

		// hook admin_tag_control_get_json_after.php

		echo json_encode($data);
		exit;
	}

	// 添加标签
	public function add() {
		// hook admin_tag_control_add_before.php

		$mid = max(2, (int)R('mid', 'P'));
		$table = $this->models->get_table($mid);

		$name = trim(safe_str(R('name', 'P')));
		$content = htmlspecialchars(trim(R('content', 'P')));

		empty($name) && E(1, '名称不能为空！');
		strlen($name)>30 && E(1, '名称太长了！');

		$data = array('name'=>$name, 'count'=>0, 'content'=>$content);
		$this->cms_content_tag->table = 'cms_'.$table.'_tag';

		// hook admin_tag_control_add_after.php

		if($this->cms_content_tag->create($data)) {
			E(0, '添加成功！');
		}else{
			E(1, '添加失败！');
		}
	}

	// 编辑标签
	public function edit() {
		// hook admin_tag_control_edit_before.php

		$mid = max(2, (int)R('mid', 'P'));
		$table = $this->models->get_table($mid);

		$tagid = (int) R('tagid', 'P');
		$name = trim(safe_str(R('name', 'P')));
		$content = htmlspecialchars(trim(R('content', 'P')));

		empty($tagid) && E(1, '标签ID不能为空！');
		empty($name) && E(1, '名称不能为空！');
		strlen($name)>30 && E(1, '名称太长了！');

		$this->cms_content->table = 'cms_'.$table;
		$this->cms_content_tag->table = 'cms_'.$table.'_tag';
		$this->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';

		$data = $this->cms_content_tag->read($tagid);

		// 修改 cms_content 表的内容
		if($data['name'] != $name) {
			$list_arr = $this->cms_content_tag_data->find_fetch(array('tagid'=>$tagid));
			foreach($list_arr as $v) {
				$data2 = $this->cms_content->read($v['id']);
				if(empty($data2)) return '读取内容表出错！';

				$row = _json_decode($data2['tags']);
				$row[$tagid] = $name;
				$data2['tags'] = _json_encode($row);

				if(!$this->cms_content->update($data2)) return '写入内容表出错！';
			}
		}

		// hook admin_tag_control_edit_after.php

		$data['name'] = $name;
		$data['content'] = $content;
		if($this->cms_content_tag->update($data)) {
			E(0, '编辑成功！');
		}else{
			E(1, '编辑失败！');
		}
	}

	// 删除标签
	public function del() {
		// hook admin_tag_control_del_before.php

		$mid = max(2, (int)R('mid', 'P'));
		$table = $this->models->get_table($mid);

		$tagid = (int) R('tagid', 'P');

		empty($tagid) && E(1, '标签ID不能为空！');

		// hook admin_tag_control_del_after.php

		$err = $this->cms_content_tag->xdelete($table, $tagid);
		if($err) {
			E(1, $err);
		}else{
			E(0, '删除成功！');
		}
	}

	// 批量删除标签
	public function batch_del() {
		// hook admin_tag_control_batch_del_before.php

		$mid = max(2, (int)R('mid', 'P'));
		$table = $this->models->get_table($mid);

		$id_arr = R('id_arr', 'P');

		if(!empty($id_arr) && is_array($id_arr)) {
			$err_num = 0;
			foreach($id_arr as $tagid) {
				$err = $this->cms_content_tag->xdelete($table, $tagid);
				if($err) $err_num++;
			}

			if($err_num) {
				E(1, $err_num.' 条标签删除失败！');
			}else{
				E(0, '删除成功！');
			}
		}else{
			E(1, '参数不能为空！');
		}
	}

	// hook admin_tag_control_after.php
}
