<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class category_control extends admin_control {
	// 分类管理
	public function index() {
		$mod_arr = $this->models->get_mod_arr();
		$this->assign('mod_arr', $mod_arr);

		$category_arr = $this->category->get_category();
		$this->assign('category_arr', $category_arr);

		$models = json_encode($this->models->get_models());
		$this->assign('models', $models);

		// hook admin_category_control_index_after.php

		$this->display();
	}

	// 写入分类 (包括添加和编辑)
	public function set() {
		if(!empty($_POST)) {
			$post['cid'] = intval(R('cid', 'P'));
			$post['mid'] = intval(R('mid', 'P'));
			$post['type'] = intval(R('type', 'P'));
			$post['upid'] = intval(R('upid', 'P'));
			$post['name'] = trim(strip_tags(R('name', 'P')));
			$post['alias'] = trim(R('alias', 'P'));
			$post['intro'] = trim(R('intro', 'P'));
			$post['orderby'] = intval(R('orderby', 'P'));
			$post['seo_title'] = trim(strip_tags(R('seo_title', 'P')));
			$post['seo_keywords'] = trim(strip_tags(R('seo_keywords', 'P')));
			$post['seo_description'] = trim(strip_tags(R('seo_description', 'P')));
			$post['cate_tpl'] = trim(strip_tags(R('cate_tpl', 'P')));
			$post['show_tpl'] = trim(strip_tags(R('show_tpl', 'P')));

			$category = &$this->category;

			// 检查基本参数是否填写
			if($err = $category->check_base($post)) {
				E(1, $err['msg'], $err['name']);
			}

			// cid 没有值时，为增加分类，否则为编辑分类
			if(empty($post['cid'])) {
				// 检查别名是否被使用
				if($err = $category->check_alias($post['alias'])) {
					E(1, $err['msg'], $err['name']);
				}

				$maxid = $category->create($post);
				if(!$maxid) {
					E(1, '写入分类数据表出错');
				}

				// 单页时
				if($post['mid'] == 1) {
					$data = array('content' => R('page_content', 'P'));
					if(!$this->cms_page->set($maxid, $data)) {
						E(1, '写入单页数据表出错');
					}
				}
			}else{
				$data = $category->read($post['cid']);

				// 检查分类是否符合编辑条件
				if($err = $category->check_is_edit($post, $data)) {
					E(1, $err['msg'], $err['name']);
				}

				// 表单数据和数据库数据相同则不修改
				foreach($post as $k=>$v) {
					if(in_array($k, array('cid', 'mid'))) continue;
					if($v == $data[$k]) unset($post[$k]);
				}

				// 检查别名是否被使用
				if(isset($post['alias']) && $err = $category->check_alias($post['alias'])) {
					E(1, $err['msg'], $err['name']);
				}

				if($post && !$category->update($post)) {
					E(1, '写入分类数据表出错');
				}

				// 删除以前的单页数据
				if($data['mid'] == 1 && $post['mid'] > 1) {
					$this->cms_page->delete($post['cid']);
				}

				// 单页时
				if($post['mid'] == 1) {
					$data = array('content' => R('page_content', 'P'));
					if(!$this->cms_page->set($post['cid'], $data)) {
						E(1, '写入单页数据表出错');
					}
				}
			}

			if(empty($msg)) {
				E(0, '保存成功');
			}
		}
	}

	// 删除分类
	public function del() {
		$cid = intval(R('cid', 'P'));

		$data = $this->category->read($cid);

		// 检查是否符合删除条件
		if($err_msg = $this->category->check_is_del($data)) {
			E(1, $err_msg);
		}

		if(!$this->category->delete($cid)) {
			E(1, '操作分类表时出错');
		}

		if($data['mid'] == 1 && !$this->cms_page->delete($cid)) {
			E(1, '操作单页表时出错');
		}

		E(0, '删除完成');
	}

	// 修改分类排序
	public function edit_orderby() {
		if(!empty($_POST)) {
			$post['cid'] = intval(R('cid', 'P'));
			$post['orderby'] = intval(R('orderby', 'P'));

			if(!$this->category->update($post)) {
				E(1, '修改分类排序出错');
			}else{
				E(0, '修改分类排序成功');
			}
		}
	}

	// 读取上级分类
	public function get_category_upid() {
		$data['upid'] = $this->category->get_category_upid(intval(R('mid')), intval(R('upid')), intval(R('noid')));
		echo json_encode($data);
		exit;
	}

	// 读取分类 (JSON)
	public function get_category_json() {
		$cid = intval(R('cid', 'P'));
		$data = $this->category->get($cid);

		// 读取单页内容
		if($data['mid'] == 1) {
			$data2 = $this->cms_page->get($cid);
			if($data2) $data['page_content'] = $data2['content'];
		}
		echo json_encode($data);
		exit;
	}

	// 读取分类 (JSON)
	public function get_category_content() {
		$mod_arr = $this->models->get_mod_arr();
		$category_arr = $this->category->get_category();

		$this->assign('mod_arr', $mod_arr);
		$this->assign('category_arr', $category_arr);

		$this->display('inc-category_content.htm');
		exit;
	}

	// hook admin_category_control_after.php
}
