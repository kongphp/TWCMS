<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

class image{
	/**
	 * 图片裁切
	 * @param string $src_file	原图路径
	 * @param string $dst_file	目标路径 (裁切后，建议后缀为jpg。null时自动生成目标路径)
	 * @param int $dst_w		目标宽度
	 * @param int $dst_h		目标高度
	 * @param int $type			目标类型 1为补白裁剪 2为居中裁剪 3为上左裁剪 4为按等比缩放，最大不会超过 $dst_w $dst_h
	 * @param int $quality		目标质量
	 */
	public static function thumb($src_file, $dst_file, $dst_w = 120, $dst_h = 120, $type = 1, $quality = 90) {
		if(!is_file($src_file)) return FALSE;

		is_null($dst_file) && $dst_file = self::thumb_name($src_file);

		$dst_ext = self::ext($dst_file);
		if(!in_array($dst_ext, array('jpg', 'gif', 'png'))) return FALSE;

		$imgs = getimagesize($src_file);
		$src_w = $imgs[0];
		$src_h = $imgs[1];
		if(empty($src_w) || empty($src_h)) return FALSE;

		// GD库不支持时，使用原图
		if(!function_exists('imagecreatefromjpeg')) {
			return copy($src_file, $dst_file);
		}

		$im_src = self::load_img($src_file, $imgs['mime']);

		switch($type) {
			case 1: // 补白裁剪
				$scale = min($dst_w/$src_w, $dst_h/$src_h);
				$new_w = round($src_w * $scale);
				$new_h = round($src_h * $scale);
				$dst_x = $new_w < $dst_w ? ($dst_w - $new_w)/2 : 0;
				$dst_y = $new_h < $dst_h ? ($dst_h - $new_h)/2 : 0;

				$im_dst = imagecreatetruecolor($dst_w, $dst_h);
				imagefill($im_dst, 0, 0 ,0xFFFFFF);
				imagecopyresampled($im_dst, $im_src, $dst_x, $dst_y, 0, 0, $new_w, $new_h, $src_w, $src_h);
				break;
			case 2: // 居中裁剪
				$scale = max($dst_w/$src_w, $dst_h/$src_h);
				$new_w = round($dst_w/$scale);
				$new_h = round($dst_h/$scale);
				$x = ($src_w - $new_w)/2;
				$y = ($src_h - $new_h)/2;

				$im_dst = imagecreatetruecolor($dst_w, $dst_h);
				imagecopyresampled($im_dst, $im_src, 0, 0, $x, $y, $dst_w, $dst_h, $new_w, $new_h);
				break;
			case 3: // 上左裁剪
				$scale = max($dst_w/$src_w, $dst_h/$src_h);
				$new_w = round($dst_w/$scale);
				$new_h = round($dst_h/$scale);

				$im_dst = imagecreatetruecolor($dst_w, $dst_h);
				imagecopyresampled($im_dst, $im_src, 0, 0, 0, 0, $dst_w, $dst_h, $new_w, $new_h);
				break;
			default: // 按等比缩放，最大不会超过 $dst_w $dst_h
				$src_scale = $src_w/$src_h;
				$scale_w = $dst_w/$src_w;
				$scale_h = $dst_h/$src_h;
				$scale = min($scale_w, $scale_h);
				$new_w = round($dst_w/$scale);
				$new_h = round($dst_h/$scale);

				if($scale_w >= $scale_h) {
					$new_dst_w = round($dst_h * $src_scale);
					$new_dst_h = $dst_h;
				}else{
					$new_dst_w = $dst_w;
					$new_dst_h = round($dst_w * $src_scale);
				}

				$im_dst = imagecreatetruecolor($new_dst_w, $new_dst_h);
				imagecopyresampled($im_dst, $im_src, 0, 0, 0, 0, $dst_w, $dst_h, $new_w, $new_h);
		}

		switch($dst_ext) {
			case 'jpg': imagejpeg($im_dst, $dst_file, $quality); break;
			case 'gif': imagegif($im_dst, $dst_file); break;
			case 'png': imagepng($im_dst, $dst_file); break;
		}

		imagedestroy($im_dst);
		imagedestroy($im_src);
		return TRUE;
	}

	/**
	 * 图片水印
	 * @param string $src_file	原图路径
	 * @param string $wat_file	水印路径 (建议使用png图片)
	 * @param string $dst_file	目标路径 (null为覆盖原图)
	 * @param int $pos			水印位置
	 * @param int $pct			水印透明度
	 */
	public static function watermark($src_file, $wat_file, $dst_file = null, $pos = 0, $pct = 80){
		$src_ext = self::ext($src_file);
		if(!in_array($src_ext, array('jpg', 'jpeg', 'gif', 'png'))) return FALSE;

		is_null($dst_file) && $dst_file = &$src_file;

		// 动画图片不加水印
		if($src_ext == 'gif' && self::check_animation($src_file)) {
			copy($src_file, $dst_file);
			return filesize($dst_file);
		}

		$wat_ext = self::ext($wat_file);
		if(!in_array($wat_ext, array('jpg', 'gif', 'png'))) return FALSE;

		if(!function_exists('imagecopy')) return FALSE;

		$srcs = getimagesize($src_file);
		$wats = getimagesize($wat_file);
		if(empty($srcs[0]) || empty($wats[0])) return FALSE;

		// 加载原图
		$im_src = self::load_img($src_file, $srcs['mime']);

		// 加载水印图
		$im_wat = self::load_img($wat_file, $wats['mime']);

		$src_w = $srcs[0];
		$src_h = $srcs[1];
		$wat_w = $wats[0];
		$wat_h = $wats[1];
		$wat_w = $wat_w > $src_w ? $src_w : $wat_w;
		$wat_h = $wat_h > $src_h ? $src_h : $wat_h;

		// 水印位置
		switch($pos){
			case 1: //顶端居左
				$x = 0;
				$y = 0;
				break;
			case 2: //顶端居中
				$x = ($src_w - $wat_w) / 2;
				$y = 0;
				break;
			case 3: //顶端居右
				$x = $src_w - $wat_w;
				$y = 0;
				break;
			case 4: //中部居左
				$x = 0;
				$y = ($src_h - $wat_h) / 2;
				break;
			case 5: //中部居中
				$x = ($src_w - $wat_w) / 2;
				$y = ($src_h - $wat_h) / 2;
				break;
			case 6: //中部居右
				$x = $src_w - $wat_w;
				$y = ($src_h - $wat_h) / 2;
				break;
			case 7: //底端居左
				$x = 0;
				$y = $src_h - $wat_h;
				break;
			case 8: //底端居中
				$x = ($src_w - $wat_w) / 2;
				$y = $src_h - $wat_h;
				break;
			case 9: //底端居右
				$x = $src_w - $wat_w;
				$y = $src_h - $wat_h;
				break;
			default: //随机
				$x = rand(0, ($src_w - $wat_w));
				$y = rand(0, ($src_h - $wat_h));
		}

		if($wat_ext == 'png') {
			imagecopy($im_src, $im_wat, $x, $y, 0, 0, $wat_w, $wat_h);
		}else{
			imagealphablending($im_src, true);
			imagecopymerge($im_src, $im_wat, $x, $y, 0, 0, $wat_w, $wat_h, $pct);
		}

		switch($srcs['mime']) {
			case 'image/jpeg': imagejpeg($im_src, $dst_file, 100); break;
			case 'image/gif': imagegif($im_src, $dst_file); break;
			case 'image/png': imagepng($im_src, $dst_file); break;
		}

		imagedestroy($im_src);
		imagedestroy($im_wat);
		return filesize($dst_file);
	}

	// 生成缩略图名字
	public static function thumb_name($filename) {
		return substr($filename, 0, strrpos($filename, '.')).'_thumb.jpg';
	}

	// 获取文件扩展名
	public static function ext($filename) {
		return strtolower(substr(strrchr($filename, '.'), 1, 10));
	}

	// 检查是否是动画图片
	public static function check_animation($filename) {
		$fp = fopen($filename, 'rb');
		$s = fread($fp, filesize($filename));
		fclose($fp);
		return strpos($s, 'NETSCAPE2.0') === FALSE ? 0 : 1;
	}

	// 加载图片资源
	public static function load_img($src_file, $mime) {
		switch($mime) {
			case 'image/jpeg':
				$im_src = imagecreatefromjpeg($src_file);
				!$im_src && $im_src = imagecreatefromgif($src_file);
				break;
			case 'image/gif':
				$im_src = imagecreatefromgif($src_file);
				!$im_src && $im_src = imagecreatefromjpeg($src_file);
				break;
			case 'image/png':
				$im_src = imagecreatefrompng($src_file);
				break;
			case 'image/wbmp':
				$im_src = imagecreatefromwbmp($src_file);
				break;
			default:
				return FALSE;
		}
		return $im_src;
	}
}

/*
用法：
image::thumb('img/1.jpg', 'img/1_1.jpg', 120, 120, 1);
image::thumb('img/1.jpg', 'img/1_2.jpg', 120, 120, 2);
image::thumb('img/1.jpg', 'img/1_3.jpg', 120, 120, 3);

image::watermark('img/2.gif', 'img/water.gif', 'img/2_new.gif', 9, 80);
image::watermark('img/2.gif', 'img/water.gif', null, 9, 80);
*/
