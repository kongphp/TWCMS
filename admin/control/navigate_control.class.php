<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class navigate_control extends admin_control {
	// 导航管理
	public function index() {
		// 模型名称
		$mod_name = $this->models->get_name();
		$this->assign('mod_name', $mod_name);

		// 全部分类
		$category_arr = $this->category->get_category();
		$this->assign('category_arr', $category_arr);

		// 导航数组
		$nav_arr = $this->kv->xget('navigate');
		$this->assign('nav_arr', $nav_arr);

		// hook admin_navigate_control_index_after.php

		$this->display();
	}

	// 导航管理
	public function get_navigate_content() {
		// 导航数组
		$nav_arr = $this->kv->xget('navigate');
		$this->assign('nav_arr', $nav_arr);

		$this->display('inc-navigate_content.htm');
	}

	// 保存修改
	public function nav_save() {
		$navi = R('navi', 'P');

		if(!empty($navi) && is_array($navi)) {
			$nav_arr = array();
			$i = 0;
			foreach($navi as $v) {
				$cid = intval($v[0]);
				$name = htmlspecialchars(trim($v[1]));
				$url = $cid ? $cid : htmlspecialchars(trim($v[2]));
				$target = $v[3] ? '_blank' : '_self';
				$rank = intval($v[4]);
				if($rank > 1) {
					$nav_arr[$i]['son'][] = array('cid'=>$cid, 'name'=>$name, 'url'=>$url, 'target'=>$target);
				}else{
					$i++;
					$nav_arr[$i] = array('cid'=>$cid, 'name'=>$name, 'url'=>$url, 'target'=>$target);
				}
			}
			$this->kv->set('navigate', $nav_arr);
		}else{
			E(1, '非法提交！');
		}

		E(0, '保存修改完成！');
	}

	// 添加分类
	public function add_cate() {
		$cate = R('cate', 'P');

		if(!empty($cate) && is_array($cate)) {
			$nav_arr = $this->kv->xget('navigate');
			foreach($cate as $arr) {
				if(isset($arr[0]) && isset($arr[1])) {
					$nav_arr[] = array('cid'=>intval($arr[1]), 'name'=>htmlspecialchars(trim($arr[0])), 'url'=>'', 'target'=>'_self');
				}
			}
			$this->kv->set('navigate', $nav_arr);

			E(0, '添加成功！');
		}else{
			E(1, '添加分类不能为空！');
		}
	}

	// 添加链接
	public function add_link() {
		$name = htmlspecialchars(trim(R('name', 'P')));
		$url = htmlspecialchars(trim(R('url', 'P')));
		$target = (int) R('target', 'P');

		!$name && E(1, '名称不能为空！', 'name');
		!$url && E(1, '链接不能为空！', 'url');

		$nav_arr = $this->kv->xget('navigate');
		$nav_arr[] = array('cid'=>0, 'name'=>$name, 'url'=>$url, 'target'=>($target ? '_blank' : '_self'));
		$this->kv->set('navigate', $nav_arr);

		E(0, '添加成功！');
	}

	// 删除
	public function del() {
		$key = R('key', 'P');

		$nav_arr = $this->kv->xget('navigate');
		if(is_numeric($key)) {
			unset($nav_arr[$key]);
		}else{
			$k = explode('-', $key);
			$k1 = intval($k[0]);
			$k2 = intval($k[1]);
			if(isset($nav_arr[$k1]['son'][$k2])) unset($nav_arr[$k1]['son'][$k2]);
		}
		$this->kv->set('navigate', $nav_arr);

		E(0, '删除完成！');
	}

	// hook admin_navigate_control_after.php
}
