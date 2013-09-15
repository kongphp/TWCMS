<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class content_control extends admin_control {
	// 内容管理
	public function index() {
		// hook admin_content_control_index_before.php

		$cid = intval(R('cid'));

		// 初始模型表名
		$mid = $this->category->get_mid_by_cid($cid);
		$tablename = $this->models->get_tablename();
		$this->cms_content->table = 'cms_'.$tablename[$mid];

		// 获取分类下拉框
		$category_cid = $this->category->get_category_cid($cid);
		$this->assign('category_cid', $category_cid);

		// 初始分页
		$pagenum = 20;
		$total = $this->cms_content->count();
		$maxpage = max(1, ceil($total/$pagenum));
		$page = min($maxpage, max(1, intval(R('page'))));
		$pages = pages($page, $maxpage, '?u=content-index-page-%d');
		$this->assign('pages', $pages);

		// 读取内容列表
		$where = $cid ? array('cid' => $cid) : array();
		$cms_content_arr = $this->cms_content->find_fetch($where, array('id'=>-1), ($page-1)*$pagenum, $pagenum);
		$this->assign('cms_content_arr', $cms_content_arr);

		// hook admin_content_control_index_after.php

		$this->display();
	}

	// 内容发布
	public function add() {
		// hook admin_content_control_add_before.php

		if(empty($_POST)) {
			$this->_cokey = 'content';
			$this->_title = '内容发布';
			$this->_place = '内容 &#187; 内容管理 &#187 内容发布';

			$category_cid = $this->category->get_category_cid();
			$this->assign('category_cid', $category_cid);

			$this->display();
		}else{
			$cid = intval(R('cid', 'P'));

			$cms_content = array(
				'cid' => $cid,
				'id' => intval(R('id', 'P')),
				'title' => trim(strip_tags(R('title', 'P'))).time(),
				'color' => trim(R('color', 'P')),
				'alias' => trim(R('alias', 'P')),
				'tags' => trim(R('tags', 'P')),
				'intro' => trim(R('intro', 'P')),
				'pic' => trim(R('pic', 'P')),
				'uid' => $this->_user['uid'],
				'author' => trim(R('author', 'P')),	// 可以不等于发布用户
				'source' => trim(R('source', 'P')),
				'dateline' => strtotime(trim(R('dateline', 'P'))),
				'lasttime' => $_ENV['_time'],
				'ip' => ip2long($_ENV['_ip']),
				'type' => 0,
				'iscomment' => intval(R('iscomment', 'P')),
				'comments' => 0,
				'seo_title' => trim(strip_tags(R('seo_title', 'P'))),
				'seo_keywords' => trim(strip_tags(R('seo_keywords', 'P'))),
				'seo_description' => trim(strip_tags(R('seo_description', 'P'))),
			);

			// 初始模型表名
			$mid = $this->category->get_mid_by_cid($cid);
			$tablename = $this->models->get_tablename();
			$this->cms_content->table = 'cms_'.$tablename[$mid];

			$maxid = $this->cms_content->create($cms_content);
			if(!$maxid) {
				E(1, '写入内容表出错');
			}

			$this->cms_content_data->table = 'cms_'.$tablename[$mid].'_data';
			$cms_content_data = array(
				'content' => trim(R('content', 'P')).time(),
			);
			if($mid == 3 || $mid == 4) $cms_content_data['images'] = R('images', 'P');
			if(!$this->cms_content_data->set($maxid, $cms_content_data)) {
				E(1, '写入内容数据表出错');
			}

			$this->cms_content_views->table = 'cms_'.$tablename[$mid].'_views';
			$cms_content_views = array(
				'cid' => $cms_content['cid'],
				'views' => intval(R('views', 'P')),
			);
			if(!$this->cms_content_views->set($maxid, $cms_content_views)) {
				E(1, '写入内容查看数表出错');
			}

			// 更新相关分类
			$data = $this->category->get($cid);
			$data['count']++;
			$this->category->update($data);
			$this->category->update_cache($cid);

			E(0, '发表成功');
		}

		// hook admin_content_control_add_after.php
	}

	// hook admin_content_control_after.php
}
