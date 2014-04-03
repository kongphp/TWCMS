<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class parseurl_control extends control{
	public function index() {
		// hook parseurl_control_index_before.php

		if(empty($_GET)) return;

		// 为了实现URL灵活性，又不想使用正则表达式，只好写这么一堆垃圾代码，写的想哭。
		$cfg = $this->runtime->xget();
		if(!empty($_ENV['_config']['twcms_parseurl']) && !empty($_GET['rewrite'])) {
			$uri = $_GET['rewrite'];
			unset($_GET['rewrite']);
			$cate_arr = array_flip($cfg['cate_arr']);

			// 分类URL未设置后缀的情况
			if(isset($cate_arr[$uri])) {
				$_GET['control'] = 'cate';
				$_GET['action'] = 'index';
				$_GET['cid'] = $cate_arr[$uri];
				return;
			}

			// 分类URL已设置后缀的情况
			$len = strlen($cfg['link_cate_end']);
			if(substr($uri, -$len) == $cfg['link_cate_end']) {
				$newurl = substr($uri, 0, -$len);
				if(isset($cate_arr[$newurl])) {
					$_GET['control'] = 'cate';
					$_GET['action'] = 'index';
					$_GET['cid'] = $cate_arr[$newurl];
					return;
				}
			}

			// 分类URL分页的情况
			if(strpos($uri, $cfg['link_cate_page_pre']) !== FALSE) {
				$len = strlen($cfg['link_cate_page_end']);
				if(substr($uri, -$len) == $cfg['link_cate_page_end']) {
					$newurl = substr($uri, 0, -$len);
					$u_arr = explode($cfg['link_cate_page_pre'], $newurl);
					if(isset($cate_arr[$u_arr[0]])) {
						$_GET['control'] = 'cate';
						$_GET['action'] = 'index';
						$_GET['cid'] = $cate_arr[$u_arr[0]];
						isset($u_arr[1]) && $_GET['page'] = $u_arr[1];
						return;
					}
				}
			}

			// 标签URL
			$len = strlen($cfg['link_tag_pre']);
			if(substr($uri, 0, $len) == $cfg['link_tag_pre']) {
				$len2 = strlen($cfg['link_tag_end']);
				if(substr($uri, -$len2) == $cfg['link_tag_end']) {
					$newurl = substr($uri, $len, -$len2);
					$u_arr = explode('_', $newurl);
					if(count($u_arr) > 1) {
						$_GET['control'] = 'tag';
						$_GET['action'] = 'index';
						$_GET['mid'] = $u_arr[0];
						$_GET['name'] = $u_arr[1];
						isset($u_arr[2]) && $_GET['page'] = $u_arr[2];
						return;
					}
				}
			}

			// 评论URL
			$len = strlen($cfg['link_comment_pre']);
			if(substr($uri, 0, $len) == $cfg['link_comment_pre']) {
				$len2 = strlen($cfg['link_comment_end']);
				if(substr($uri, -$len2) == $cfg['link_comment_end']) {
					$newurl = substr($uri, $len, -$len2);
					$u_arr = explode('_', $newurl);
					if(count($u_arr) > 1) {
						$_GET['control'] = 'comment';
						$_GET['action'] = 'index';
						$_GET['cid'] = $u_arr[0];
						$_GET['id'] = $u_arr[1];
						isset($u_arr[2]) && $_GET['page'] = $u_arr[2];
						return;
					}
				}
			}

			// 首页分页URL (用的少)
			$len = strlen($cfg['link_index_end']);
			if(substr($uri, 0, 6) == 'index_' && substr($uri, -$len) == $cfg['link_index_end']) {
				$uri = substr($uri, 6, -$len);
				$u_arr = explode('_', $uri);
				if(count($u_arr) > 1) {
					$_GET['control'] = 'index';
					$_GET['action'] = 'index';
					$_GET['mid'] = $u_arr[0];
					$_GET['page'] = $u_arr[1];
					return;
				}
			}

			// 标签排行页 (用的少)
			if($uri == 'tag_top' && $uri == 'tag_top/') {
				$_GET['control'] = 'tag';
				$_GET['action'] = 'top';
				return;
			}

			// hook parseurl_control_index_link_show_before.php

			// 内容页 (最复杂的部分)
			if($cfg['link_show_type'] == 1) { // 1. {cid}/{id} 性能最优
				$len = strlen($cfg['link_show_end']);
				if(empty($len) || substr($uri, -$len) == $cfg['link_show_end']) {
					$u_arr = explode('/', $len ? substr($uri, 0, -$len) : $uri);
					if(count($u_arr) > 1) {
						$_GET['control'] = 'show';
						$_GET['action'] = 'index';
						$_GET['cid'] = $u_arr[0];
						$_GET['id'] = $u_arr[1];
						return;
					}
				}
			}elseif($cfg['link_show_type'] == 2) { // 2. {cate_alias}/{id} 性能次优
				$len = strlen($cfg['link_show_end']);
				if(empty($len) || substr($uri, -$len) == $cfg['link_show_end']) {
					$u_arr = explode('/', $len ? substr($uri, 0, -$len) : $uri);
					if(count($u_arr) > 1 && isset($cate_arr[$u_arr[0]])) {
						$_GET['control'] = 'show';
						$_GET['action'] = 'index';
						$_GET['cid'] = $cate_arr[$u_arr[0]];
						$_GET['id'] = $u_arr[1];
						return;
					}
				}
			}elseif($cfg['link_show_type'] == 3) { // 3. {alias} 性能一般
				$len = strlen($cfg['link_show_end']);
				if(empty($len) || substr($uri, -$len) == $cfg['link_show_end']) {
					$newurl = $len ? substr($uri, 0, -$len) : $uri;

					// 这处有些特别，如果没有设置别名，将用 cid_id 组合
					preg_match('#(\d+)\_(\d+)#', $newurl, $mat);
					if(isset($mat[2])) {
						$_GET['control'] = 'show';
						$_GET['action'] = 'index';
						$_GET['cid'] = $mat[1];
						$_GET['id'] = $mat[2];
						return;
					}elseif(preg_match('#\w+#', $newurl)) {
						$row = $this->only_alias->get($newurl);
						if(!empty($row)) {
							$_GET['control'] = 'show';
							$_GET['action'] = 'index';
							$_GET['cid'] = $row['cid'];
							$_GET['id'] = $row['id'];
							return;
						}
					}
				}
			}else{ // 4. 正则表达式解析，性能较差
				$quote = preg_quote($cfg['link_show'], '#');
				$quote = strtr($quote, array(
					'\{cid\}' => '(?<cid>\d+)',
					'\{id\}' => '(?<id>\d+)',
					'\{alias\}' => '(?<alias>\w+)',
					'\{cate_alias\}' => '(?<cate_alias>\w+)',
					'\{y\}' => '\d{4}',
					'\{m\}' => '\d{2}',
					'\{d\}' => '\d{2}'
				));

				try{
					preg_match('#'.$quote.'#', $uri, $mat);
				}catch(Exception $e) {
					$this->message(0, '内容URL伪静态规则不正确！');
				}

				if(isset($mat['cid']) && isset($mat['id'])) { // {cid} {id} 合组
					$_GET['control'] = 'show';
					$_GET['action'] = 'index';
					$_GET['cid'] = $mat['cid'];
					$_GET['id'] = $mat['id'];
					return;
				}elseif(isset($mat['cate_alias']) && isset($mat['id'])) { // {cate_alias} {id} 合组
					$_GET['control'] = 'show';
					$_GET['action'] = 'index';
					$_GET['cid'] = $cate_arr[$mat['cate_alias']];
					$_GET['id'] = $mat['id'];
					return;
				}elseif(isset($mat['alias'])) { // {alias}
					// 这处有些特别，如果没有设置别名，将用 cid_id 组合
					preg_match('#(\d+)\_(\d+)#', $mat['alias'], $mat2);
					if(isset($mat2[2])) {
						$_GET['control'] = 'show';
						$_GET['action'] = 'index';
						$_GET['cid'] = $mat2[1];
						$_GET['id'] = $mat2[2];
						return;
					}

					$row = $this->only_alias->get($mat['alias']);
					if(!empty($row)) {
						$_GET['control'] = 'show';
						$_GET['action'] = 'index';
						$_GET['cid'] = $row['cid'];
						$_GET['id'] = $row['id'];
						return;
					}
				}
			}
		}

		// 伪静态时，如果 $uri 有值，但没有解析到相关 $_GET 时，就提示404
		if(empty($_GET) && !empty($uri)) {
			core::error404();
		}

		// 上面都不符合到这里解析
		if(!isset($_GET['control'])) {
			if(isset($_GET['u'])) {
				$u = $_GET['u'];
				unset($_GET['u']);
			}elseif(!empty($_SERVER['PATH_INFO'])) {
				$u = $_SERVER['PATH_INFO'];
			}else{
				$_GET = array();
				$u = $_SERVER["QUERY_STRING"];
			}

			//清除URL后缀
			$url_suffix = C('url_suffix');
			if($url_suffix) {
				$suf_len = strlen($url_suffix);
				if(substr($u, -($suf_len)) == $url_suffix) $u = substr($u, 0, -($suf_len));
			}

			$uarr = explode('-', $u);
			if(count($uarr) < 2) return;

			if(isset($uarr[0])) {
				$_GET['control'] = $uarr[0];
				array_shift($uarr);
			}

			if(isset($uarr[0])) {
				$_GET['action'] = $uarr[0];
				array_shift($uarr);
			}

			$num = count($uarr);
			for($i=0; $i<$num; $i+=2){
				isset($uarr[$i+1]) && $_GET[$uarr[$i]] = $uarr[$i+1];
			}
		}
	}
}
