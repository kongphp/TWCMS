<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_attach extends model {
	function __construct() {
		$this->table = '';			// 表名 (可以是 cms_article_attach cms_product_attach cms_photo_attach 等)
		$this->pri = array('aid');	// 主键
		$this->maxid = 'aid';		// 自增字段
	}

	// 上传并记录到数据库
	public function upload($uid, $maxsize, $allowext) {
		$config = array('maxSize'=>$maxsize, 'allowExt'=>$allowext);
		$up = new upload('upfile', $config);
		$info = $up->getFileInfo();

		if($info['state'] == 'SUCCESS') {
			$data = array(
				'cid' => 0,
				'uid' => $uid,
				'id' => 0,
				'filename' => $info['name'],
				'filetype' => $info['type'],
				'filesize' => $info['size'],
				'filepath' => $info['path'],
				'dateline' => $_ENV['_time'],
				'downloads' => 0,
				'isimage' => $info['isimage'],
			);

			$info['maxid'] = $this->create($data);
			if(!$info['maxid']) {
				$info['state'] = '写入附件表失败';
			}
		}

		return $info;
	}
}
