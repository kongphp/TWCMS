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
		$table = $this->models->get_table($mid);
		$this->cms_content->table = 'cms_'.$table;

		// 获取分类下拉框
		$category_cid = $this->category->get_category_cid($cid);
		$this->assign('category_cid', $category_cid);

		// 初始分页
		$pagenum = 20;
		$total = $this->cms_content->count();
		$maxpage = max(1, ceil($total/$pagenum));
		$page = min($maxpage, max(1, intval(R('page'))));
		$pages = pages($page, $maxpage, '?u=content-index-page-{page}');
		$this->assign('total', $total);
		$this->assign('pages', $pages);

		// 读取内容列表
		$where = $cid ? array('cid' => $cid) : array();
		$cms_content_arr = $this->cms_content->list_arr($where, 'id', -1, ($page-1)*$pagenum, $pagenum, $total);
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

			empty($cid) && E(1, '分类ID不能为空！');

			$mid = $this->category->get_mid_by_cid($cid);
			$table = $this->models->get_table($mid);

			$tags = trim(R('tags', 'P'), ", \t\n\r\0\x0B");
			$tags_arr = explode(',', $tags);

			// log::trace('发表内容');
			// 读tag表
			$this->cms_content_tag->table = 'cms_'.$table.'_tag';
			$ti = 0;
			$tag_set = array();
			foreach($tags_arr as $tv) {
				$name = trim($tv);
				if($name) {
					$tag_row = $this->cms_content_tag->find_fetch(array('name'=>$name), array(), 0, 1);
					if(!$tag_row) {
						$tagid = $this->cms_content_tag->create(array('name'=>$name, 'count'=>0, 'content'=>''));
						if(!$tagid) {
							E(1, '写入标签表出错');
						}
						$tag_row = $this->cms_content_tag->get($tagid);
					}else{
						$tag_row = current($tag_row);
					}

					$tag_row['count']++;
					$tag_set[] = $tag_row;

					$ti++;
					if($ti>4) break;
				}
			}
			// log::trace_save();

			// 写入内容表
			$cms_content = array(
				'cid' => $cid,
				'title' => trim(strip_tags(R('title', 'P'))).time(),
				'color' => trim(R('color', 'P')),
				'alias' => trim(R('alias', 'P')),
				'tags' => '',
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
			$this->cms_content->table = 'cms_'.$table;
			$maxid = $this->cms_content->create($cms_content);
			if(!$maxid) {
				E(1, '写入内容表出错');
			}

			// 写入内容数据表
			$this->cms_content_data->table = 'cms_'.$table.'_data';
			$cms_content_data = array(
				'content' => trim(R('content', 'P')).time(),
			);
			if($mid == 3 || $mid == 4) $cms_content_data['images'] = R('images', 'P');
			if(!$this->cms_content_data->set($maxid, $cms_content_data)) {
				E(1, '写入内容数据表出错');
			}

			// 写入内容查看数表
			$this->cms_content_views->table = 'cms_'.$table.'_views';
			$cms_content_views = array(
				'cid' => $cms_content['cid'],
				'views' => intval(R('views', 'P')),
			);
			if(!$this->cms_content_views->set($maxid, $cms_content_views)) {
				E(1, '写入内容查看数表出错');
			}

			// 写入内容标签表
			$this->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';
			$tags_arr2 = array();
			foreach($tag_set as $v) {
				$this->cms_content_tag->update($v);
				$tags_arr2[$v['tagid']] = $v['name'];
				$this->cms_content_tag_data->set(array($v['tagid'], $maxid), array('tagid'=>$v['tagid'], 'id'=>$maxid));
			}

			// 更新标签json到内容表
			$cms_content2 = array('id'=>$maxid, 'tags'=>json_encode($tags_arr2));
			if(!$this->cms_content->update($cms_content2)) {
				E(1, '写入标签到内容表出错');
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
