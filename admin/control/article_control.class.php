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

			$data['flags'] = array();
			$data['pic_src'] = '../static/img/nopic.gif';
			//$_user['username']
			//{$data[source]} = date('Y-m-d H:i:s', $_ENV['_time']);
			$this->assign('data', $data);

			$this->display('article_set.htm');
		}else{
			$cid = intval(R('cid', 'P'));
			$title = trim(strip_tags(R('title', 'P')));
			$flags = (array)R('flag', 'P');
			$views = intval(R('views', 'P'));
			$contentstr = trim(R('content', 'P'));
			$intro = trim(R('intro', 'P'));
			$dateline = trim(R('dateline', 'P'));
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

			// 标签预处理，最多支持5个标签
			$tags = trim(R('tags', 'P'), ", \t\n\r\0\x0B");
			$tags_arr = explode(',', $tags);
			$this->cms_content_tag->table = 'cms_'.$table.'_tag';
			$tagdatas = $tags = array();
			for($i=0; isset($tags_arr[$i]) && $i<5; $i++) {
				$name = trim($tags_arr[$i]);
				if($name) {
					$tagdata = $this->cms_content_tag->find_fetch(array('name'=>$name), array(), 0, 1);
					if(!$tagdata) {
						$tagid = $this->cms_content_tag->create(array('name'=>$name, 'count'=>0, 'content'=>''));
						if(!$tagid) {
							E(1, '写入标签表出错');
						}
						$tagdata = $this->cms_content_tag->get($tagid);
					}else{
						$tagdata = current($tagdata);
					}

					$tagdata['count']++;

					$tagdatas[] = $tagdata;
					$tags[$tagdata['tagid']] = $tagdata['name'];
				}
			}

			// 远程图片本地化
			$this->cms_content_attach->table = 'cms_'.$table.'_attach';
			if($isremote) {
				$endstr .= $this->get_remote_img($table, $uid, $contentstr);
			}

			// 计算图片数，和非图片文件数
			$imagenum = $this->cms_content_attach->find_count(array('id'=>0, 'uid'=>$uid, 'isimage'=>1));
			$filenum = $this->cms_content_attach->find_count(array('id'=>0, 'uid'=>$uid, 'isimage'=>0));

			// 如果缩略图为空，并且内容含有图片，则将第一张图片设置为缩略图
			if(empty($pic) && $imagenum) {
				$pic = auto_pic($table, $uid);
			}

			// 如果摘要为空，自动生成摘要
			$intro = $this->auto_intro($intro, $contentstr);

			// 写入内容表
			$data = array(
				'cid' => $cid,
				'title' => $title,
				'color' => trim(R('color', 'P')),
				'alias' => trim(R('alias', 'P')),
				'tags' => json_encode($tags),
				'intro' => $intro,
				'pic' => $pic,
				'uid' => $uid,
				'author' => trim(R('author', 'P')),	// 可以不等于发布用户
				'source' => trim(R('source', 'P')),
				'dateline' => $dateline ? strtotime($dateline) : $_ENV['_time'],
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
			$id = $this->cms_content->create($data);
			if(!$id) {
				E(1, '写入内容表出错');
			}

			// 写入内容数据表
			$this->cms_content_data->table = 'cms_'.$table.'_data';
			if(!$this->cms_content_data->set($id, array('content' => $contentstr))) {
				E(1, '写入内容数据表出错');
			}

			// 写入内容查看数表
			$this->cms_content_views->table = 'cms_'.$table.'_views';
			if(!$this->cms_content_views->set($id, array('cid' => $cid, 'views' => $views))) {
				E(1, '写入内容查看数表出错');
			}

			// 更新附件归宿 cid 和 id
			if($imagenum || $filenum) {
				if(!$this->cms_content_attach->find_update(array('id'=>0, 'uid'=>$uid), array('cid'=>$cid, 'id'=>$id))) {
					E(1, '更新内容附件表出错');
				}
			}

			// 写入内容属性标记表
			$this->cms_content_flag->table = 'cms_'.$table.'_flag';
			foreach($flags as $flag) {
				if(!$this->cms_content_flag->set(array($flag, $id), array('cid'=>$cid))) {
					E(1, '写入内容属性标记表出错');
				}
			}

			// 写入内容标签表
			$this->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';
			foreach($tagdatas as $tagdata) {
				$this->cms_content_tag->update($tagdata);
				$this->cms_content_tag_data->set(array($tagdata['tagid'], $id), array('id'=>$id));
			}

			// 更新用户发布的内容条数
			$this->_user['contents']++;
			$this->user->update($this->_user);

			// 更新分类的内容条数
			$categorys['count']++;
			$this->category->update($categorys);
			$this->category->update_cache($cid);

			// hook admin_article_control_add_after.php

			E(0, '发表成功'.$endstr, $id);
		}
	}

	// 文章编辑
	public function edit() {
		// hook admin_article_control_edit_before.php

		if(empty($_POST)) {
			$this->_cokey = 'content';
			$this->_title = '文章编辑';
			$this->_place = '内容 &#187; 内容管理 &#187 文章编辑';
			$id = intval(R('id'));
			$cid = intval(R('cid'));

			$cidhtml = $this->category->get_cidhtml_by_mid(2, $cid);
			$this->assign('cidhtml', $cidhtml);

			$mid = $this->category->get_mid_by_cid($cid);
			$table = $this->models->get_table($mid);

			$this->cms_content->table = 'cms_'.$table;
			$this->cms_content_data->table = 'cms_'.$table.'_data';
			$data = $this->cms_content->get($id);
			$data2 = $this->cms_content_data->get($id);
			$data = array_merge($data, $data2);
			$data['content'] = htmlspecialchars($data['content']);
			$data['tags'] = implode(',', (array)_json_decode($data['tags']));
			$data['intro'] = str_replace('<br />', "\n", strip_tags($data['intro']));
			$data['pic_src'] = empty($data['pic']) ? '../static/img/nopic.gif' : '../upload/'.$table.'/'.$data['pic'];
			$data['flags'] = explode(',', $data['flags']);
			$data['dateline'] = date('Y-m-d H:i:s', $data['dateline']);
			$this->assign('data', $data);

			$this->display('article_set.htm');
		}else{
			$id = intval(R('id', 'P'));
			$cid = intval(R('cid', 'P'));
			$title = trim(strip_tags(R('title', 'P')));
			$flags = (array)R('flag', 'P');
			$views = intval(R('views', 'P'));
			$contentstr = trim(R('content', 'P'));
			$intro = trim(R('intro', 'P'));
			$isremote = intval(R('isremote', 'P'));
			$pic = trim(R('pic', 'P'));
			$uid = $this->_user['uid'];
			$endstr = '';

			empty($id) && E(1, 'ID不能为空！');
			empty($cid) && E(1, '分类ID不能为空！');
			empty($title) && E(1, '标题不能为空！');

			$categorys = $this->category->read($cid);
			if(empty($categorys)) E(1, '分类ID不存在！');

			$mid = $this->category->get_mid_by_cid($cid);
			$table = $this->models->get_table($mid);

			$this->cms_content->table = 'cms_'.$table;
			$data = $this->cms_content->get($id);
			if(empty($data)) E(1, '内容不存在！');

			// 比较属性变化
			$flags_old = array();
			if($data['flags']) {
				$flags_old = explode(',', $data['flags']);
				foreach($flags as $flag) {
					$key = array_search($flag, $flags_old);
					if($key !== false) unset($flags_old[$key]);
				}
			}

			// 比较标签变化
			$tags = trim(R('tags', 'P'), ", \t\n\r\0\x0B");
			$tags_new = explode(',', $tags);
			$tags_old = (array)_json_decode($data['tags']);
			$tags_arr = array();
			foreach($tags_new as $tagname) {
				$key = array_search($tagname, $tags_old);
				if($key === false) {
					$tags_arr[] = $tagname;
				}else{
					unset($tags_old[$key]);
				}
			}

			// 标签预处理，最多支持5个标签
			$this->cms_content_tag->table = 'cms_'.$table.'_tag';
			$tagdatas = $tags = array();
			for($i=0; isset($tags_arr[$i]) && $i<5; $i++) {
				$name = trim($tags_arr[$i]);
				if($name) {
					$tagdata = $this->cms_content_tag->find_fetch(array('name'=>$name), array(), 0, 1);
					if(!$tagdata) {
						$tagid = $this->cms_content_tag->create(array('name'=>$name, 'count'=>0, 'content'=>''));
						if(!$tagid) {
							E(1, '写入标签表出错');
						}
						$tagdata = $this->cms_content_tag->get($tagid);
					}else{
						$tagdata = current($tagdata);
					}

					$tagdata['count']++;

					$tagdatas[] = $tagdata;
					$tags[$tagdata['tagid']] = $tagdata['name'];
				}
			}

			// 远程图片本地化
			$this->cms_content_attach->table = 'cms_'.$table.'_attach';
			if($isremote) {
				$endstr .= $this->get_remote_img($table, $uid, $contentstr);
			}

			// 计算图片数，和非图片文件数
			$imagenum = $this->cms_content_attach->find_count(array('id'=>$id, 'uid'=>$uid, 'isimage'=>1));
			$filenum = $this->cms_content_attach->find_count(array('id'=>$id, 'uid'=>$uid, 'isimage'=>0));

			// 如果缩略图为空，并且内容含有图片，则将第一张图片设置为缩略图
			if(empty($pic) && $imagenum) {
				$pic = auto_pic($table, $uid);
			}

			// 如果摘要为空，自动生成摘要
			$intro = $this->auto_intro($intro, $contentstr);

			// 写入内容表
			$data['id'] = $id;
			$data['cid'] = $cid;
			$data['title'] = $title;
			$data['color'] = trim(R('color', 'P'));
			$data['alias'] = trim(R('alias', 'P'));
			$data['tags'] = json_encode($tags);
			$data['intro'] = $intro;
			$data['pic'] = $pic;
			$data['uid'] = $uid;
			$data['author'] = trim(R('author', 'P'));	// 可以不等于发布用户
			$data['source'] = trim(R('source', 'P'));
			$data['dateline'] = strtotime(trim(R('dateline', 'P')));
			$data['lasttime'] = $_ENV['_time'];
			// $data['ip'] = ip2long($_ENV['_ip']);
			$data['iscomment'] = intval(R('iscomment', 'P'));
			// $data['comments'] = 0;
			$data['imagenum'] = $imagenum;
			$data['filenum'] = $filenum;
			$data['flags'] = implode(',', $flags);
			$data['seo_title'] = trim(strip_tags(R('seo_title', 'P')));
			$data['seo_keywords'] = trim(strip_tags(R('seo_keywords', 'P')));
			$data['seo_description'] = trim(strip_tags(R('seo_description', 'P')));

			if(!$this->cms_content->update($data)) {
				E(1, '更新内容表出错');
			}

			// 写入内容数据表
			$this->cms_content_data->table = 'cms_'.$table.'_data';
			if(!$this->cms_content_data->set($id, array('content' => $contentstr))) {
				E(1, '写入内容数据表出错');
			}

			// 写入内容查看数表
			$this->cms_content_views->table = 'cms_'.$table.'_views';
			if(!$this->cms_content_views->set($id, array('cid' => $cid, 'views' => $views))) {
				E(1, '写入内容查看数表出错');
			}

			// 写入内容属性标记表
			$this->cms_content_flag->table = 'cms_'.$table.'_flag';
			foreach($flags as $flag) {
				if(!$this->cms_content_flag->set(array($flag, $id), array('cid'=>$cid))) {
					E(1, '写入内容属性标记表出错');
				}
			}

			// 删除去掉的属性
			foreach($flags_old as $flag) {
				$flag = intval($flag);
				if($flag) $this->cms_content_flag->delete($flag, $id);
			}

			// 写入内容标签表
			$this->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';
			foreach($tagdatas as $tagdata) {
				$this->cms_content_tag->update($tagdata);
				$this->cms_content_tag_data->set(array($tagdata['tagid'], $id), array('id'=>$id));
			}

			// 删除不用的标签
			foreach($tags_old as $tagid => $tagname) {
				$tagdata = $this->cms_content_tag->get($tagid);
				$tagdata['count']--;
				$this->cms_content_tag->update($tagdata);
				$this->cms_content_tag_data->delete($tagid, $id);
			}

			// hook admin_article_control_edit_after.php

			E(0, '发表成功'.$endstr, $id);
		}
	}

	// 删除文章
	public function del() {
		// hook admin_article_control_del_before.php

		$id = (int) R('id', 'P');
		$cid = (int) R('cid', 'P');

		empty($id) && E(1, '内容ID不能为空！');
		empty($cid) && E(1, '分类ID不能为空！');

		// hook admin_article_control_del_after.php

		$err = $this->cms_content->xdelete('article', $id, $cid);
		if($err) {
			E(1, $err);
		}else{
			E(0, '删除成功！');
		}
	}

	// 批量删除文章
	public function batch_del() {
		// hook admin_article_control_batch_del_before.php

		$id_arr = R('id_arr', 'P');
		if(!empty($id_arr) && is_array($id_arr)) {
			$err_num = 0;
			foreach($id_arr as $v) {
				$err = $this->cms_content->xdelete('article', $v[0], $v[1]);
				if($err) $err_num++;
			}

			if($err_num) {
				E(1, $err_num.' 篇文章删除失败！');
			}else{
				E(0, '删除成功！');
			}
		}else{
			E(1, '参数不能为空！');
		}
	}

	// 自动生成摘要
	private function auto_intro($intro, &$content) {
		if(empty($intro)) {
			$intro = preg_replace('/\s{2,}/', ' ', strip_tags($content));
			return trim(utf8::cutstr_cn($intro, 255, ''));
		}else{
			return str_replace(array("\r\n", "\r", "\n"), '<br />', strip_tags($intro));
		}
	}

	// 自动生成缩略图
	private function auto_pic($table, $uid) {
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
		return $pic;
	}

	// 获取远程图片
	private function get_remote_img($table, $uid, &$content) {
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
			'cfg'=>$cfg,
		);
		$content = preg_replace_callback('#\<img [^\>]*src=["\']((?:http|ftp)\://[^"\']+)["\'][^\>]*\>#iU', array($this, 'img_replace'), $content);
		unset($_ENV['_prc_arg']);
		return $_ENV['_prc_err'] ? '，但远程抓取图片失败 '.$_ENV['_prc_err'].' 张！' : '';
	}

	// 远程图片处理 (如果抓取失败则不替换)
	// $conf 用到4个参数 hosts preUri cfg upDir
	private function img_replace($mat) {
		static $uris = array();
		$uri = $mat[1];
		$conf = &$_ENV['_prc_arg'];

		// 排除重复保存相同URL图片
		if(isset($uris[$uri])) return str_replace($uri, $uris[$uri], $mat[0]);

		// 根据域名排除本站图片
		$urls = parse_url($uri);
		if(in_array($urls['host'], $conf['hosts'])) return $mat[0];

		$file = $this->cms_content_attach->remote_down($uri, $conf);
		if($file) {
			$uris[$uri] = $conf['preUri'].$file;
			$cfg = $conf['cfg'];

			// 是否添加水印
			if(!empty($cfg['watermark_pos'])) {
				image::watermark($conf['upDir'].$file, TWCMS_PATH.'static/img/watermark.png', null, $cfg['watermark_pos'], $cfg['watermark_pct']);
			}

			return str_replace($uri, $uris[$uri], $mat[0]);
		}else{
			$_ENV['_prc_err']++;
			return $mat[0];
		}
	}

	// hook admin_article_control_after.php
}
