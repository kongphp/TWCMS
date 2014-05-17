<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class category_control extends admin_control {
	// 分类管理
	public function index() {
		$mod_name = $this->models->get_name();
		$this->assign('mod_name', $mod_name);

		$_ENV['_category_class'] = &$this->category;
		$_cfg = $this->runtime->xget();
		$this->assign('_cfg', $_cfg);

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
			$post = array(
				'cid' => intval(R('cid', 'P')),
				'mid' =>  intval(R('mid', 'P')),
				'type' => intval(R('type', 'P')),
				'upid' => intval(R('upid', 'P')),
				'name' => trim(strip_tags(R('name', 'P'))),
				'alias' => trim(R('alias', 'P')),
				'intro' => trim(strip_tags(R('intro', 'P'))),
				'cate_tpl' => trim(strip_tags(R('cate_tpl', 'P'))),
				'show_tpl' => trim(strip_tags(R('show_tpl', 'P'))),
				'count' => 0,
				'orderby' => intval(R('orderby', 'P')),
				'seo_title' => trim(strip_tags(R('seo_title', 'P'))),
				'seo_keywords' => trim(strip_tags(R('seo_keywords', 'P'))),
				'seo_description' => trim(strip_tags(R('seo_description', 'P'))),
			);

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
					$pagedata = array('cid' => $maxid, 'content' => R('page_content', 'P'));
					if(!$this->cms_page->set($maxid, $pagedata)) {
						E(1, '写入单页数据表出错');
					}
				}
			}else{
				$data = $category->read($post['cid']);

				// 检查分类是否符合编辑条件
				if($err = $category->check_is_edit($post, $data)) {
					E(1, $err['msg'], $err['name']);
				}

				// 别名被修改过才检查是否被使用
				if($post['alias'] != $data['alias']) {
					$err = $category->check_alias($post['alias']);
					if($err) {
						E(1, $err['msg'], $err['name']);
					}

					// 修改导航中的分类的别名
					$navigate = $this->kv->xget('navigate');
					foreach($navigate as $k=>$v) {
						if($v['cid'] == $post['cid']) $navigate[$k]['alias'] = $post['alias'];
						if(isset($v['son'])) {
							foreach($v['son'] as $k2=>$v2) {
								if($v2['cid'] == $post['cid']) $navigate[$k]['son'][$k2]['alias'] = $post['alias'];
							}
						}
					}
					$this->kv->set('navigate', $navigate);
				}

				// 这里赋值，是为了开启缓存后，编辑时更新缓存
				$post['count'] = $data['count'];
				if(!$category->update($post)) {
					E(1, '写入分类数据表出错');
				}

				// 删除以前的单页数据
				if($data['mid'] == 1 && $post['mid'] > 1) {
					$this->cms_page->delete($post['cid']);
				}

				// 单页时
				if($post['mid'] == 1) {
					$pagedata = array('cid' => $post['cid'], 'content' => R('page_content', 'P'));
					if(!$this->cms_page->set($post['cid'], $pagedata)) {
						E(1, '写入单页数据表出错');
					}
				}
			}

			// 删除缓存
			$this->runtime->delete('cfg');
			$this->category->delete_cache();
			$this->runtime->truncate();

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

		// 删除导航中的分类
		$navigate = $this->kv->xget('navigate');
		foreach($navigate as $k=>$v) {
			if($v['cid'] == $cid) unset($navigate[$k]);
			if(isset($v['son'])) {
				foreach($v['son'] as $k2=>$v2) {
					if($v2['cid'] == $cid) unset($navigate[$k]['son'][$k2]);
				}
			}
		}
		$this->kv->set('navigate', $navigate);

		// 删除缓存
		$this->runtime->delete('cfg');
		$this->category->delete_cache();
		$this->runtime->truncate();

		E(0, '删除完成');
	}

	// 修改分类排序
	public function edit_orderby() {
		if(!empty($_POST)) {
			$cid = intval(R('cid', 'P'));
			$orderby = intval(R('orderby', 'P'));

			$post = $this->category->read($cid);
			$post['orderby'] = $orderby;

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

		// 为频道时，检测是否有下级分类
		if($data['type'] == 1 && $this->category->find_fetch_key(array('upid' => $data['cid']), array(), 0, 1)) {
			$data['son_cate'] = 1;
		}

		echo json_encode($data);
		exit;
	}

	// 读取分类 (JSON)
	public function get_category_content() {
		$_ENV['_category_class'] = &$this->category;

		$mod_name = $this->models->get_name();
		$category_arr = $this->category->get_category();

		$this->assign('mod_name', $mod_name);
		$this->assign('category_arr', $category_arr);

		$this->display('inc-category_content.htm');
		exit;
	}

	// hook admin_category_control_after.php
}
