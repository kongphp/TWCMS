<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class article_control extends admin_control {
	// 内容管理
	public function index() {
		// hook admin_article_control_index_before.php

		$cid = intval(R('cid'));

		// 获取分类下拉框
		$cidhtml = $this->category->get_cidhtml_by_mid(2, $cid, '所有文章');
		$this->assign('cidhtml', $cidhtml);

		// 初始模型表名
		$this->cms_content->table = 'cms_article';

		// 初始分页
		$pagenum = 20;
		if($cid) {
			$categorys = $this->category->read($cid);
			$total = isset($categorys['count']) ? $categorys['count'] : 0;
		}else{
			$total = $this->cms_content->count();
		}
		$maxpage = max(1, ceil($total/$pagenum));
		$page = min($maxpage, max(1, intval(R('page'))));
		$pages = pages($page, $maxpage, '?u=article-index'.($cid ? '-cid-'.$cid : '').'-page-{page}');
		$this->assign('total', $total);
		$this->assign('pages', $pages);

		// 读取内容列表
		$where = $cid ? array('cid' => $cid) : array();
		$cms_article_arr = $this->cms_content->list_arr($where, 'id', -1, ($page-1)*$pagenum, $pagenum, $total);
		$this->assign('cms_article_arr', $cms_article_arr);

		// hook admin_article_control_index_after.php

		$this->display();
	}

	// 文章发布
	public function add() {
		// hook admin_article_control_add_before.php

		if(empty($_POST)) {
			$this->_cokey = 'content';
			$this->_title = '文章发布';
			$this->_place = '内容 &#187; 内容管理 &#187 文章发布';
			$cid = intval(R('cid'));

			$cidhtml = $this->category->get_cidhtml_by_mid(2, $cid);
			$this->assign('cidhtml', $cidhtml);

			$this->display();
		}else{
			$cid = intval(R('cid', 'P'));
			$title = trim(strip_tags(R('title', 'P')));
			$flags = (array)R('flag', 'P');
			$contentstr = trim(R('content', 'P'));
			$isremote = intval(R('isremote', 'P'));
			$pic = trim(R('pic', 'P'));
			$uid = $this->_user['uid'];
			$endstr = '';

			empty($cid) && E(1, '分类ID不能为空！');
			empty($title) && E(1, '标题不能为空！');

			$categorys = $this->category->read($cid);
			if(empty($categorys)) E(1, '分类ID不存在！');

			$mid = $this->category->get_mid_by_cid($cid);
			$table = $this->models->get_table($mid);

			// 处理标签，最多支持5个标签
			$tags = trim(R('tags', 'P'), ", \t\n\r\0\x0B");
			$tags_arr = explode(',', $tags);
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

			// 远程图片本地化
			$this->cms_content_attach->table = 'cms_'.$table.'_attach';
			if($isremote) {
				function_exists('set_time_limit') && set_time_limit(0);
				$cfg = $this->runtime->xget();
				$updir = 'upload/'.$table.'/';
				$_ENV['_prc_err'] = 0;
				$_ENV['_prc_arg'] = array(
					'hosts'=>array('127.0.0.1', 'localhost', $_SERVER['HTTP_HOST'], $cfg['webdomain']),
					'uid'=>$uid,
					'maxSize'=>10000,
					'upDir'=>TWCMS_PATH.$updir,
					'preUri'=>$cfg['weburl'].$updir,
				);
				$contentstr = preg_replace_callback('#\<img [^\>]*src=["\']((?:http|ftp)\://[^"\']+)["\'][^\>]*\>#iU', array($this, 'img_replace'), $contentstr);
				unset($_ENV['_prc_arg']);
				$endstr .= $_ENV['_prc_err'] ? '，但远程抓取图片失败 '.$_ENV['_prc_err'].' 张！' : '';
			}

			// 计算图片数，和非图片文件数
			$imagenum = $this->cms_content_attach->find_count(array('id'=>0, 'uid'=>$uid, 'isimage'=>1));
			$filenum = $this->cms_content_attach->find_count(array('id'=>0, 'uid'=>$uid, 'isimage'=>0));

			// 如果缩略图为空，并且内容含有图片，则将第一张图片设置为缩略图
			if(empty($pic) && $imagenum) {
				$pic_arr = $this->cms_content_attach->find_fetch(array('id'=>0, 'uid'=>$uid, 'isimage'=>1), array(), 0, 1);
				$pic_arr = current($pic_arr);
				$cfg = $this->runtime->xget();
				$path = 'upload/'.$table.'/'.$pic_arr['filepath'];
				$pic = image::thumb_name($path);
				$src_file = TWCMS_PATH.$path;
				$dst_file = TWCMS_PATH.$pic;
				if(!is_file($dst_file)) {
					image::thumb($src_file, $dst_file, $cfg['thumb_'.$table.'_w'], $cfg['thumb_'.$table.'_h'], $cfg['thumb_type'], $cfg['thumb_quality']);
				}
			}

			// 如果摘要为空，自动生成摘要
			$intro = trim(R('intro', 'P'));
			$intro = empty($intro) ? $contentstr : $intro;
			$intro = preg_replace('/\s{2,}/', ' ', strip_tags($intro));
			$intro = trim(utf8::cutstr_cn($intro, 255, ''));

			// 写入内容表
			$cms_content = array(
				'cid' => $cid,
				'title' => $title,
				'color' => trim(R('color', 'P')),
				'alias' => trim(R('alias', 'P')),
				'tags' => '',
				'intro' => $intro,
				'pic' => $pic,
				'uid' => $uid,
				'author' => trim(R('author', 'P')),	// 可以不等于发布用户
				'source' => trim(R('source', 'P')),
				'dateline' => strtotime(trim(R('dateline', 'P'))),
				'lasttime' => $_ENV['_time'],
				'ip' => ip2long($_ENV['_ip']),
				'iscomment' => intval(R('iscomment', 'P')),
				'comments' => 0,
				'imagenum' => $imagenum,
				'filenum' => $filenum,
				'flags' => implode(',', $flags),
				'seo_title' => trim(strip_tags(R('seo_title', 'P'))),
				'seo_keywords' => trim(strip_tags(R('seo_keywords', 'P'))),
				'seo_description' => trim(strip_tags(R('seo_description', 'P'))),
			);
			$this->cms_content->table = 'cms_'.$table;
			$id = $this->cms_content->create($cms_content);
			if(!$id) {
				E(1, '写入内容表出错');
			}

			// 写入内容数据表
			$this->cms_content_data->table = 'cms_'.$table.'_data';
			$cms_content_data = array(
				'content' => $contentstr,
			);
			if(!$this->cms_content_data->set($id, $cms_content_data)) {
				E(1, '写入内容数据表出错');
			}

			// 写入内容属性标记表
			$this->cms_content_flag->table = 'cms_'.$table.'_flag';
			foreach($flags as $flag) {
				if(!$this->cms_content_flag->set(array($flag, $id), array('cid'=>$cid))) {
					E(1, '写入内容属性标记表出错');
				}
			}

			// 写入内容查看数表
			$this->cms_content_views->table = 'cms_'.$table.'_views';
			$cms_content_views = array(
				'cid' => $cms_content['cid'],
				'views' => intval(R('views', 'P')),
			);
			if(!$this->cms_content_views->set($id, $cms_content_views)) {
				E(1, '写入内容查看数表出错');
			}

			// 写入内容标签表
			$this->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';
			$tags_arr2 = array();
			foreach($tag_set as $v) {
				$this->cms_content_tag->update($v);
				$tags_arr2[$v['tagid']] = $v['name'];
				$this->cms_content_tag_data->set(array($v['tagid'], $id), array('id'=>$id));
			}

			// 更新标签json到内容表
			$cms_content2 = array('id'=>$id, 'tags'=>json_encode($tags_arr2));
			if(!$this->cms_content->update($cms_content2)) {
				E(1, '写入标签到内容表出错');
			}

			// 更新附件归宿 cid 和 id
			if($imagenum || $filenum) {
				if(!$this->cms_content_attach->find_update(array('id'=>0, 'uid'=>$uid), array('cid'=>$cid, 'id'=>$id))) {
					E(1, '更新内容附件表出错');
				}
			}

			// 更新用户发布的内容条数
			$this->_user['contents']++;
			$this->user->update($this->_user);

			// 更新相关分类
			$data = $this->category->get($cid);
			$data['count']++;
			$this->category->update($data);
			$this->category->update_cache($cid);

			// hook admin_article_control_add_after.php

			E(0, '发表成功'.$endstr, $id);
		}
	}

	// 远程图片处理 (如果抓取失败则不替换; 没有考虑排除重复图片问题)
	private function img_replace($mat) {
		// 根据域名排除本站图片
		$urls = parse_url($mat[0]);
		if(in_array($urls['host'], $_ENV['_prc_arg']['hosts'])) return $mat[0];

		$file = $this->cms_content_attach->remote_down($mat[1], $_ENV['_prc_arg']);
		if($file) {
			return str_replace($mat[1], $file, $mat[0]);
		}else{
			$_ENV['_prc_err']++;
			return $mat[0];
		}
	}

	// hook admin_article_control_after.php
}
