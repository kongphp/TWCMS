<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
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
	public function uploads($config, $uid, $cid = 0, $id = 0) {
		$up = new upload($config, 'upfile');
		$info = $up->getFileInfo();

		if($info['state'] == 'SUCCESS') {
			$data = array(
				'cid' => $cid,
				'uid' => $uid,
				'id' => $id,
				'filename' => $info['name'],
				'filetype' => $info['ext'],
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

	// 远程图片下载并记录到数据库 ($conf 用到5个参数 maxSize upDir cid uid id)
	public function remote_down($uri, &$conf) {
		// php.ini 中的 allow_url_fopen 关闭时不抓取远程图片
		if(function_exists('ini_get') && !ini_get('allow_url_fopen')) return FALSE;

		// 获取请求头
		try{ $heads = get_headers($uri, 1); }catch(Exception $e) { return FALSE; }

		// 死链检测
		if(!(strstr($heads[0], "200") && stristr($heads[0], "OK"))) return FALSE;

		// Content-Type验证和格式验证
		if(stristr($heads['Content-Type'], 'image')) {
			$fileExt = trim(strtolower(strrchr($heads['Content-Type'], '/')), '/');
			if(in_array($fileExt, array('jpg', 'jpeg', 'gif', 'png'))) {
				$fileExt = ($fileExt == 'jpeg') ? 'jpg' : $fileExt;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}

		try{
			// 抓取远程图片
			$context = stream_context_create(array('http'=>array('follow_location'=>false, 'timeout'=>60))); // 不重定向抓取
			$img = file_get_contents($uri, false, $context);

			// 图片大小验证
			$filesize = strlen($img);
			$maxSize = $conf['maxSize']*1024;
			if($filesize > $maxSize) return FALSE;

			// 创建图片目录
			$dir = date('Ym/d/');
			$updir = $conf['upDir'].$dir;
			if(!is_dir($updir) && !mkdir($updir, 0755, true)) {
				return FALSE;
			}

			// 图片写入自己的服务器
			$filepath = $dir.date('His').uniqid().random(6, 3).'.'.$fileExt;
			if(!file_put_contents($conf['upDir'].$filepath, $img)) return FALSE;

			// 记录到数据库
			$data = array(
				'cid' => $conf['cid'],
				'uid' => $conf['uid'],
				'id' => $conf['id'],
				'filename' => basename($uri),
				'filetype' => $fileExt,
				'filesize' => $filesize,
				'filepath' => $filepath,
				'dateline' => $_ENV['_time'],
				'downloads' => 0,
				'isimage' => 1,
			);
			if(!$this->create($data)) return FALSE;

			return $filepath;
		}catch(Exception $e) {
			return FALSE;
		}
	}

	// 删除单个附件
	public function xdelete($aid) {
		$data = $this->read($aid);
		$updir = TWCMS_PATH.'upload/'.substr($this->table, 4, -7).'/';

		$file = $updir.$data['filepath'];
		$thumb = image::thumb_name($file);

		try{
			is_file($file) && unlink($file);
			is_file($thumb) && unlink($thumb);
			return $this->delete($aid);
		}catch(Exception $e) {
			return FALSE;
		}
	}
}
