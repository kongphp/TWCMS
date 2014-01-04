<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

class upload{
	private $config;	//上传文件配置 共三个参数 maxSize(允许上传最大文件) | allowExt(允许上传的文件后缀) | upDir(上传保存目录)
	private $file;		//上传文件信息
	private $upDir;		//上传文件根目录
	private $fileName;	//上传原文件名
	private $fileSize;	//上传文件大小
	private $fileType;	//上传文件类型
	private $fileExt;	//上传原扩展名
	private $filePath;	//上传文件保存路径 (不包含 $upDir)
	private $fileState;	//上传文件状态
	private $isImage;	//是否图片
	private $stateMap = array(
		'0' => 'SUCCESS',
		'1' => '文件大小超出 upload_max_filesize 限制',
		'2' => '文件大小超出 MAX_FILE_SIZE 限制',
		'3' => '文件未被完整上传',
		'4' => '没有文件被上传',
		'5' => '上传文件为空',
		'6' => '缺少临时文件夹',
		'7' => '写文件失败',
		'8' => '上传被其它扩展中断',
		'POST' => '未接收到 $_POST 数据',
		'FILES' => '未接收到 $_FILES 数据',
		'SIZE' => '文件大小超出网站限制',
		'EXT' => '不允许的扩展名',
		'DIR' => '目录创建失败',
		'IO' => '文件写入失败',
		'UNKNOWN' => '未知错误',
		'MOVE' => '文件保存时出错'
	);

	public function __construct($config, $formName, $base64=false) {
		$this->config = $config;
		$this->upDir = $config['upDir'];
		$this->fileState = $this->stateMap[0];
		$this->upFile($base64, $formName);
	}

	// 获取当前上传成功文件的各项信息
	public function getFileInfo() {
		return array(
			'state' => $this->fileState,
			'name' => $this->fileName,
			'size' => $this->fileSize,
			'type' => $this->fileType,
			'ext' => $this->fileExt,
			'path' => $this->filePath,
			'isimage' => $this->getIsImage()
		);
	}

	// 获取是否是图片文件
	private function getIsImage() {
		return in_array($this->fileExt, array('gif', 'jpg', 'jpeg', 'png', 'bmp')) ? 1 : 0; // 1为图片 0为文件
	}

	// 上传文件
	private function upFile($base64, $formName) {
		if('base64' == $base64) {
			if(empty($_POST[$formName])) {
				$this->fileState = $this->getFileState('POST');
				return;
			}
			$content = $_POST[$formName];
			$this->base64ToImage($content);
			return;
		}

		if(empty($_FILES[$formName])) {
			$this->fileState = $this->getFileState('FILES');
			return;
		}
		$this->file = $_FILES[$formName];
		if($this->file['error']) {
			$this->fileState = $this->getFileState($this->file['error']);
			return;
		}
		if(!is_uploaded_file($this->file['tmp_name'])) {
			$this->fileState = $this->getFileState('UNKNOWN');
			return;
		}

		$this->fileName = $this->file['name'];
		$this->fileSize = $this->file['size'];
		$this->fileType = $this->file['type'];
		$this->fileExt = $this->getFileExt();
		if(!$this->checkSize()) {
			$this->fileState = $this->getFileState('SIZE');
			return;
		}
		if(!$this->checkExt()) {
			$this->fileState = $this->getFileState('EXT');
			return;
		}
		$dir = date('Ym/d/');
		$updir = $this->upDir.$dir;
		if(!is_dir($updir) && !mkdir($updir, 0755, true)) {
			$this->fileState = $this->getFileState('DIR');
			return;
		}

		$this->filePath = $dir.$this->getName();
		if($this->fileState == $this->stateMap[0]) {
			if(!move_uploaded_file($this->file['tmp_name'] , $this->upDir.$this->filePath)) {
				$this->fileState = $this->getFileState('MOVE');
			}
		}
	}

	// 处理base64编码的图片上传
	private function base64ToImage($base64Data) {
		$img = base64_decode($base64Data);

		$dir = date('Ym/d/');
		$updir = $this->upDir.$dir;
		if(!is_dir($updir) && !mkdir($updir, 0755, true)) {
			$this->fileState = $this->getFileState('DIR');
			return;
		}
		$this->fileName = '';
		$this->fileSize = strlen($img);
		$this->fileType = 'image/png';
		$this->fileExt = 'png';
		$this->filePath = $dir.$this->getName();
		if(!file_put_contents($this->upDir.$this->filePath, $img)) {
			$this->fileState = $this->getFileState('IO');
			return;
		}
	}

	// 检测上传文件大小是否合格
	private function checkSize() {
		return $this->fileSize <= ($this->config['maxSize'] * 1024);
	}

	// 检测文件类型检测是否合格
	private function checkExt() {
		return in_array($this->fileExt, $this->getAllowExt());
	}

	// 获取允许上传的扩展名
	private function getAllowExt() {
		$conf = explode(',', $this->config['allowExt']);
		$arr = array();
		foreach($conf as $v) {
			$v = trim($v);
			if($v) $arr[] = $v;
		}
		return $arr;
	}

	// 获取上传文件状态
	private function getFileState($errCode) {
		return empty($this->stateMap[$errCode]) ? $this->stateMap['UNKNOWN'] : $this->stateMap[$errCode];
	}

	// 获取新的安全文件名
	private function getName() {
		// 白名单后缀，其他文件后缀为 .file
		// 防XSS漏洞需要注意：IE会解析 .jpg .gif .txt 中的 <script>
		$Exts = array(
			'gif', 'jpg', 'jpeg', 'png', 'bmp',
			'swf', 'fla', 'as',
			'mp3', 'mp4', 'flv', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb',
			'wps', 'doc', 'ppt', 'docx', 'xsl', 'xls', 'xlsx',
			'zip', 'rar', 'tar', 'tar.gz', 'gz', '7z', 'bz', 'bz2', 'iso',
			'chm', 'torrent', 'ttf', 'font',
		);
		$fileExt = in_array($this->fileExt, $Exts) ? '.'.$this->fileExt : '_'.$this->fileExt.'.file';
		return date('His').uniqid().$this->random(6).$fileExt;
	}

	// 获取安全的文件扩展名
	private function getFileExt() {
		return preg_replace('/\W/', '', strtolower(substr(strrchr($this->file['name'], '.'), 1, 10)));
	}

	// 随机字符串
	private function random($length) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		for($i = 0, $max = strlen($chars) - 1, $hash = ''; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
		return $hash;
	}
}
