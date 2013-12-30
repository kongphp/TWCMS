<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content extends model {
	function __construct() {
		$this->table = '';			// 表名 (可以是 cms_article、cms_product、cms_photo 等)
		$this->pri = array('id');	// 主键
		$this->maxid = 'id';		// 自增字段
	}

	// 格式化内容数组
	public function format(&$v, $mid, $dateformat = 'Y-m-d H:i:s', $titlenum = 0, $intronum = 0) {
		// hook cms_content_model_format_before.php

		if(empty($v)) return FALSE;

		$v['date'] = date($dateformat, $v['dateline']);
		$v['subject'] = $titlenum ? utf8::cutstr_cn($v['title'], $titlenum) : $v['title'];
		$v['url'] = 'index.php?show--cid-'.$v['cid'].'-id-'.$v['id'].C('url_suffix');
		$v['tags'] = _json_decode($v['tags']);
		if($v['tags']) {
			$v['tag_arr'] = array();
			foreach($v['tags'] as $name) {
				$v['tag_arr'][] = array('name'=>$name, 'url'=>'index.php?tag--mid-'.$mid.'-name-'.urlencode($name).C('url_suffix'));
			}
		}

		$intronum && $v['intro'] = utf8::cutstr_cn($v['intro'], $intronum);
		empty($v['pic']) && $v['pic'] = $_ENV['_config']['front_static'].'img/nopic.gif';

		// hook cms_content_model_format_after.php
	}

	// 获取内容列表
	public function list_arr($where, $orderby, $orderway, $start, $limit, $total) {
		// 优化大数据量翻页
		if($start > 1000 && $total > 2000 && $start > $total/2) {
			$orderway = -$orderway;
			$newstart = $total-$start-$limit;
			if($newstart < 0) {
				$limit += $newstart;
				$newstart = 0;
			}
			$list_arr = $this->find_fetch($where, array($orderby => $orderway), $newstart, $limit);
			return array_reverse($list_arr, TRUE);
		}else{
			return $this->find_fetch($where, array($orderby => $orderway), $start, $limit);
		}
	}

	// 内容关联删除
	public function xdelete($table, $id, $cid) {
		// hook cms_content_model_xdelete_before.php

		$this->table = 'cms_'.$table;
		$this->cms_content_data->table = 'cms_'.$table.'_data';
		$this->cms_content_comment->table = 'cms_'.$table.'_comment';
		$this->cms_content_comment_sort->table = 'cms_'.$table.'_comment_sort';
		$this->cms_content_attach->table = 'cms_'.$table.'_attach';
		$this->cms_content_flag->table = 'cms_'.$table.'_flag';
		$this->cms_content_tag->table = 'cms_'.$table.'_tag';
		$this->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';
		$this->cms_content_views->table = 'cms_'.$table.'_views';

		// 内容读取
		$data = $this->read($id);
		if(empty($data)) return '内容不存在！';

		// 删除评论
		$this->cms_content_comment->find_delete(array('id'=>$id));
		$this->cms_content_comment_sort->delete($id);

		// 删除附件
		$attach_arr = $this->cms_content_attach->find_fetch(array('id'=>$id));
		$updir = TWCMS_PATH.'upload/'.$table.'/';
		foreach($attach_arr as $v) {
			$file = $updir.$v['filepath'];
			$thumb = image::thumb_name($file);
			try{
				is_file($file) && unlink($file);
				is_file($thumb) && unlink($thumb);
			}catch(Exception $e) {}
			$this->cms_content_attach->delete($v['aid']);
		}

		// 更新标签表
		if(!empty($data['tags'])) {
			$tags_arr = _json_decode($data['tags']);
			foreach($tags_arr as $tagid => $name) {
				$this->cms_content_tag_data->delete($tagid, $id);
				$tagdata = $this->cms_content_tag->read($tagid);
				if($tagdata['count'] > 0) $this->cms_content_tag->update(array('tagid'=>$tagid, 'count' => --$tagdata['count']));
			}
		}

		// 更新分类表
		$categorys = $this->category->read($cid);
		if(empty($categorys)) return '读取分类表出错！';
		if($categorys['count'] > 0) {
			if(!$this->category->update(array('cid'=>$cid, 'count'=>--$categorys['count']))) return '写入内容表出错！';
		}

		// 删除内容
		$this->cms_content_data->delete($id);
		$this->cms_content_views->delete($id);
		$this->cms_content_flag->find_delete(array('id'=>$id));
		$ret = $this->delete($id);
		return $ret ? '' : '删除失败！';
	}
}
