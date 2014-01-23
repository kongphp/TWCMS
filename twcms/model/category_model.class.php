<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class category extends model {
	private $data = array();		// 防止重复查询

	function __construct() {
		$this->table = 'category';	// 表名
		$this->pri = array('cid');	// 主键
		$this->maxid = 'cid';		// 自增字段
	}

	// 暂时用些方法解决获取 cfg 值
	function __get($var) {
		if($var == 'cfg') {
			return $this->cfg = $this->runtime->xget();
		}else{
			return parent::__get($var);
		}
	}

	// 检查基本参数是否填写
	public function check_base(&$post) {
		if(empty($post['mid'])) {
			$name = 'mid';
			$msg = '请选择分类模型';
		}elseif(!isset($post['type'])) {
			$name = 'type';
			$msg = '请选择分类属性';
		}elseif(!isset($post['upid'])) {
			$name = 'upid';
			$msg = '请选择所属频道';
		}elseif(strlen($post['name']) < 1) {
			$name = 'name';
			$msg = '请填写分类名称';
		}elseif(strlen($post['alias']) < 1) {
			$name = 'alias';
			$msg = '请填写分类别名';
		}elseif(strlen($post['alias']) > 50) {
			$name = 'alias';
			$msg = '分类别名不能超过50个字符';
		}elseif(empty($post['cate_tpl'])) {
			$name = 'cate_tpl';
			$msg = '请填写分类页模板';
		}elseif($post['mid'] > 1 && empty($post['show_tpl'])) {
			$name = 'show_tpl';
			$msg = '请填写内容页模板';
		}
		return empty($msg) ? FALSE : array('name' => $name, 'msg' => $msg);
	}

	// 检查别名是否被使用
	public function check_alias($alias) {
		$msg = $this->only_alias->check_alias($alias);
		return empty($msg) ? FALSE : array('name' => 'alias', 'msg' => $msg);
	}

	// 检查是否符合修改条件
	public function check_is_edit($post, $data) {
		if($post['cid'] == $post['upid']) {
			$name = 'upid';
			$msg = '所属频道不能修改为自己';	// 暂时不考虑 upid 不能为自己的下级分类或非频道分类 (前端已经限制)
		}elseif($data['count'] > 0 && $post['mid'] != $data['mid']) {
			$name = 'mid';
			$msg = '分类中有内容，不允许修改分类模型，请先清空分类内容';
		}elseif($data['count'] > 0 && $post['type'] != $data['type']) {
			$name = 'type';
			$msg = '分类中有内容，不允许修改分类属性，请先清空分类内容';
		}elseif($data['type'] == 1 && $post['mid'] != $data['mid'] && $this->check_is_son($data['cid'])) {
			$name = 'mid';
			$msg = '分类有下级分类，不允许修改分类模型';
		}elseif($data['type'] == 1 && $post['type'] != $data['type'] && $this->check_is_son($data['cid'])) {
			$name = 'type';
			$msg = '分类有下级分类，不允许修改分类类型';
		}
		return empty($msg) ? FALSE : array('name' => $name, 'msg' => $msg);
	}

	// 检查是否符合删除条件
	public function check_is_del($data) {
		if($data['type'] == 1 && $this->check_is_son($data['cid'])) {
			return '分类有下级分类，请先删除下级分类';
		}elseif($data['count'] > 0) {
			return '分类中有内容，请先删除内容';
		}
		return FALSE;
	}

	// 检查是否有下级分类
	public function check_is_son($upid) {
		return $this->find_fetch_key(array('upid' => $upid), array(), 0, 1) ? TRUE : FALSE;
	}

	// 从数据库获取分类
	public function get_category_db() {
		if(isset($this->data['category_db'])) {
			return $this->data['category_db'];
		}

		// hook category_model_get_category_db_before.php

		$arr = array();
		$tmp = $this->find_fetch(array(), array('orderby'=>1));
		foreach($tmp as $v) {
			$arr[$v['cid']] = $v;
		}

		// hook category_model_get_category_db_after.php

		return $this->data['category_db'] = $arr;
	}

	// 获取分类 (树状结构)
	public function get_category_tree() {
		if(isset($this->data['category_tree'])) {
			return $this->data['category_tree'];
		}

		$this->data['category_tree'] = array();
		$tmp = $this->get_category_db();

		// 格式化为树状结构 (会舍弃不合格的结构)
		foreach($tmp as $v) {
			$tmp[$v['upid']]['son'][$v['cid']] = &$tmp[$v['cid']];
		}
		$this->data['category_tree'] = isset($tmp['0']['son']) ? $tmp['0']['son'] : array();

		// 格式化为树状结构 (不会舍弃不合格的结构)
		// foreach($tmp as $v) {
		// 	if(isset($tmp[$v['upid']])) $tmp[$v['upid']]['son'][] = &$tmp[$v['cid']];
		// 	else $this->data['category_tree'][] = &$tmp[$v['cid']];
		// }

		return $this->data['category_tree'];
	}

	// 获取分类 (二维数组)
	public function get_category() {
		if(isset($this->data['category_array'])) {
			return $this->data['category_array'];
		}

		$arr = $this->get_category_tree();
		return $this->data['category_array'] = $this->to_array($arr);
	}

	// 递归转换为二维数组
	public function to_array($data, $pre = 1) {
		static $arr = array();

		foreach($data as $k => $v) {
			$v['pre'] = $pre;
			if(isset($v['son'])) {
				$arr[$v['mid']][] = $v;
				self::to_array($v['son'], $pre+1);
			}else{
				$arr[$v['mid']][] = $v;
			}
		}

		return $arr;
	}

	// 获取模型下级所有列表分类的cid
	public function get_cids_by_mid($mid) {
		$k = 'cate_by_mid_'.$mid;
		if(isset($this->data[$k])) return $this->data[$k];

		$arr = $this->runtime->xget($k);
		if(empty($arr)) {
			$arr = $this->get_cids_by_upid(0, $mid);
			$this->runtime->set($k, $arr);
		}
		$this->data[$k] = $arr;
		return $arr;
	}

	// 获取频道分类下级列表分类的cid
	public function get_cids_by_upid($upid, $mid) {
		$arr = array();
		$tmp = $this->get_category_db();
		if($upid != 0 && !isset($tmp[$upid])) return FALSE;

		foreach($tmp as $k => $v) {
			if($v['mid'] == $mid) {
				$tmp[$v['upid']]['son'][$v['cid']] = &$tmp[$v['cid']];
			}else{
				unset($tmp[$k]);
			}
		}

		if(isset($tmp[$upid]['son'])) {
			foreach($tmp[$upid]['son'] as $k => $v) {
				if($v['type'] == 1) {
					$arr[$k] = isset($v['son']) ? self::recursion_cid($v['son']) : array();
				}elseif($v['type'] == 0) {
					$arr[$k] = 1;
				}
			}
		}

		return $arr;
	}

	// 递归获取下级分类全部 cid
	public function recursion_cid(&$data) {
		$arr = array();
		foreach($data as $k => $v) {
			if(isset($v['son'])) {
				$arr2 = self::recursion_cid($v['son']);
				$arr = array_merge($arr, $arr2);
			}else{
				if($v['type'] == 0) {
					$arr[] = intval($v['cid']);
				}
			}
		}
		return $arr;
	}

	// 获取分类下拉列表HTML (内容发布时使用)
	public function get_cidhtml_by_mid($_mid, $cid, $tips = '选择分类') {
		$category_arr = $this->get_category();

		$s = '<select name="cid" id="cid">';
		if(empty($category_arr)) {
			$s .= '<option value="0">没有分类</option>';
		}else{
			$s .= '<option value="0">'.$tips.'</option>';
			foreach($category_arr as $mid => $arr) {
				if($mid != $_mid) continue;

				foreach($arr as $v) {
					$disabled = $v['type'] == 1 ? ' disabled="disabled"' : '';
					$s .= '<option value="'.$v['cid'].'"'.($v['type'] == 0 && $v['cid'] == $cid ? ' selected="selected"' : '').$disabled.'>';
					$s .= str_repeat("　", $v['pre']-1);
					$s .= '|─'.$v['name'].($v['type'] == 1 ? '[频道]' : '').'</option>';
				}
			}
		}
		$s .= '</select>';
		return $s;
	}

	// 获取上级分类的 HTML 代码 (只显示频道分类)
	public function get_category_upid($mid, $upid = 0, $noid = 0) {
		$category_arr = $this->get_category();

		$s = '<option value="0">无</option>';
		if(isset($category_arr[$mid])) {
			foreach($category_arr[$mid] as $v) {
				// 不显示列表的分类
				if($mid> 1 && $v['type'] == 0) continue;

				// 当 $noid 有值时，排除等于它和它的下级分类
				if($noid) {
					if(isset($pre)) {
						if($v['pre'] > $pre) continue;
						else unset($pre);
					}
					if($v['cid'] == $noid) {
						$pre = $v['pre'];
						continue;
					}
				}

				$s .= '<option value="'.$v['cid'].'"'.($v['cid'] == $upid ? ' selected="selected"' : '').'>';
				$s .= str_repeat("　", $v['pre']-1);
				$s .= '|─'.$v['name'].'</option>';
			}
		}

		return $s;
	}

	// 获取指定分类的 mid (如果 cid 为空，则读第一个分类的 mid)
	public function get_mid_by_cid($cid) {
		if($cid) {
			$arr = $this->read($cid);
		}else{
			$arr = $this->get_category();
			if(empty($arr)) return 2;

			$arr = current($arr);
			$arr = current($arr);
		}
		return $arr['mid'];
	}

	// 获取分类当前位置
	public function get_place($cid) {
		$p = array();
		$tmp = $this->get_category_db();

		while(isset($tmp[$cid]) && $v = &$tmp[$cid]) {
			array_unshift($p, array(
				'cid'=> $v['cid'],
				'name'=> $v['name'],
				'url'=> $this->category_url($v['cid'], $v['alias'])
			));
			$cid = $v['upid'];
		}

		return $p;
	}

	// 获取分类缓存合并数组
	public function get_cache($cid) {
		$k = 'cate_'.$cid;
		if(isset($this->data[$k])) return $this->data[$k];

		$arr = $this->runtime->xget($k);
		if(empty($arr)) {
			$arr = $this->update_cache($cid);
		}
		$this->data[$k] = $arr;
		return $arr;
	}

	// 更新分类缓存合并数组
	public function update_cache($cid) {
		$k = 'cate_'.$cid;
		$arr = $this->read($cid);
		if(empty($arr)) return FALSE;

		$arr['place'] = $this->get_place($cid);	// 分类当前位置
		$arr['topcid'] = $arr['place'][0]['cid'];	// 顶级分类CID
		$arr['table'] = $this->cfg['table_arr'][$arr['mid']];	// 分类模型表名

		// 如果为频道，获取频道分类下级CID
		if($arr['type'] == 1) {
			$arr['son_list'] = $this->get_cids_by_upid($cid, $arr['mid']);
			$arr['son_cids'] = array();
			if(!empty($arr['son_list'])) {
				foreach($arr['son_list'] as $c => $v) {
					if(is_array($v)) {
						$v && $arr['son_cids'] = array_merge($arr['son_cids'], $v);
					}else{
						$arr['son_cids'][] = $c;
					}
				}
			}
		}

		// hook category_model_update_cache_after.php

		$this->runtime->set($k, $arr);
		return $arr;
	}

	// 删除所有分类缓存 (最多读取2000条，如果缓存太大，需要手工清除缓存)
	public function delete_cache() {
		$key_arr = $this->runtime->find_fetch_key(array(), array(), 0, 2000);
		foreach ($key_arr as $v) {
			if(substr($v, 10, 5) == 'cate_') {
				$this->runtime->delete(substr($v, 10));
			}
		}
		return TRUE;
	}

	// 分类链接格式化
	public function category_url(&$cid, &$alias, $page = FALSE) {
		// hook category_model_category_url_before.php

		if(empty($_ENV['_config']['twcms_parseurl'])) {
			return $this->cfg['webdir'].'index.php?cate--cid-'.$cid.($page ? '-page-{page}' : '').$_ENV['_config']['url_suffix'];
		}else{
			if($page) {
				return $this->cfg['webdir'].$alias.$this->cfg['link_cate_page_pre'].'{page}'.$this->cfg['link_cate_page_end'];
			}else{
				return $this->cfg['webdir'].$alias.$this->cfg['link_cate_end'];
			}
		}
	}
}
