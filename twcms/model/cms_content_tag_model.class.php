<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_tag extends model {
	function __construct() {
		$this->table = '';				// 表名 (可以是 cms_article_tag cms_product_tag cms_photo_tag 等)
		$this->pri = array('tagid');	// 主键
		$this->maxid = 'tagid';			// 自增字段
	}

	// 获取标签列表
	public function list_arr($orderway, $start, $limit, $total) {
		// 优化大数据量翻页
		if($start > 1000 && $total > 2000 && $start > $total/2) {
			$orderway = -$orderway;
			$newstart = $total-$start-$limit;
			if($newstart < 0) {
				$limit += $newstart;
				$newstart = 0;
			}
			$list_arr = $this->find_fetch(array(), array('count' => $orderway), $newstart, $limit);
			return array_reverse($list_arr, TRUE);
		}else{
			return $this->find_fetch(array(), array('count' => $orderway), $start, $limit);
		}
	}

	// 标签关联删除 (需要删除三个表: cms_content_tag cms_content_tag_data cms_content)
	public function xdelete($table, $tagid) {
		$this->table = 'cms_'.$table.'_tag';
		$this->cms_content->table = 'cms_'.$table;
		$this->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';

		// 删除 cms_content 表的内容
		try{
			// 如果内容数太大，会删除失败。（这时程序需要改进做分批删除设计）
			$list_arr = $this->cms_content_tag_data->find_fetch(array('tagid'=>$tagid));
			foreach($list_arr as $v) {
				$data = $this->cms_content->read($v['id']);
				if(empty($data)) return '读取内容表出错！';

				$row = _json_decode($data['tags']);
				unset($row[$tagid]);
				$data['tags'] = _json_encode($row);

				if(!$this->cms_content->update($data)) return '写入内容表出错！';
			}
		}catch(Exception $e) {
			return '修改内容表出错！';
		}

		// 删除 cms_content_tag_data 表的内容
		try{
			$this->cms_content_tag_data->find_delete(array('tagid'=>$tagid));
		}catch(Exception $e) {
			return '删除标签数据表出错！';
		}

		return $this->delete($tagid) ? '' : '删除失败！';
	}
}
