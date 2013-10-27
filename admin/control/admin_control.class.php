<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class admin_control extends control {
	public $_user = array();	// 用户
	public $_group = array();	// 用户组

	public $_navs = array();	// 导航
	public $_cokey = '';		// 父级
	public $_title = '';		// 标题
	public $_place = '';		// 位置

	function __construct() {
		$_ENV['_config']['FORM_HASH'] = form_hash();
		$this->assign('C', $_ENV['_config']);
		$this->assign_value('core', F_APP_NAME);

		$admauth = R($_ENV['_config']['cookie_pre'].'admauth', 'R');

		$err = 0;
		if(empty($admauth)) {
			$err = 1;
		}else{
			$admauth = str_auth($admauth);
			if(empty($admauth)) {
				$err = 1;
			}else{
				$arr = explode("\t", $admauth);
				if(count($arr) < 5) {
					$err = 1;
				}else{
					$uid      = $arr[0];
					$username = $arr[1];
					$password = $arr[2];
					$groupid  = $arr[3];
					$ip       = $arr[4];

					$user = &$this->user;
					$user_group = &$this->user_group;

					$this->_user = $user->get($uid);
					$this->_group = $user_group->get($groupid);

					if(empty($this->_group)) {
						$err = 1;
					}elseif($this->_user['password'] != $password || $this->_user['username'] != $username || $this->_user['groupid'] != $groupid) {
						$err = 1;
					}elseif($_ENV['_ip'] != $ip) {
						_setcookie('admauth', '', 1);
						$this->message(0, '您的IP已经改变，为了安全考虑，请重新登录！', '?u=index-login');
					}else{
						// 初始化导航数组
						$this->init_navigation();

						// 检查用户组权限 (如果非管理员将重新定义导航数组)
						$this->check_user_group();

						// 初始化标题、位置
						$this->init_title_place();

						$this->assign('_user', $this->_user);
						$this->assign('_group', $this->_group);
						$this->assign('_navs', $this->_navs);
						$this->assign('_cokey', $this->_cokey);
						$this->assign('_title', $this->_title);
						$this->assign('_place', $this->_place);
					}
				}
			}
		}

		if(R('control') == 'index' && R('action') == 'login') {
			if(!$err) {
				exit('<html><body><script>top.location="./"</script></body></html>');
			}
		}elseif($err) {
			if(R('ajax')) {
				$this->message(0, '非法访问，请登陆后再试！', '?u=index-login');
			}
			exit('<html><body><script>top.location="?u=index-login"</script></body></html>');
		}

		// hook admin_admin_control_construct_after.php
	}

	// 检查是不是管理员
	protected function check_isadmin() {
		if($this->_group['groupid'] != 1) {
			$this->message(0, '对不起，您不是管理员，无权访问。', -1);
		}
	}

	// 检查用户组权限
	protected function check_user_group() {
		if($this->_group['groupid'] == 1) return;
		if($this->_group['groupid'] > 5) {
			log::write("无权用户组尝试登录后台", 'login_log.php');
			$this->message(0, '对不起，您所在的用户组无权访问后台', -1);
		}else{
			$purviews = _json_decode($this->_group['purviews']);
			/*
			提示：$purviews 返回的结果如下，如果有特别需求，开发者可根据下面的结构进行扩展。
			array(
				'navs' => array(),	//显示的导航数组
				'whitelist' => array('content'=>array('index'=>1,'comment'=>1))	//白名单，允许执行的权限
			)
			*/

			// 重新定义导航数组
			isset($purviews['navs']) && $this->_navs = $purviews['navs'];

			// 判断权限，如果不在白名单中终止执行
			$control = &$_GET['control'];
			$action = &$_GET['action'];
			if($control != 'index' && $control != 'my' && !isset($purviews['whitelist'][$control][$action])) {
				$this->message(0, '对不起，您所在的用户组无权访问', -1);
			}
		}
	}

	// 初始化标题、位置
	public function init_title_place() {
		$url = $_GET['control'].'-'.$_GET['action'];
		if(!isset($this->_navs[1][$url])) return;

		$this->_cokey = $this->_navs[1][$url]['p'];
		$this->_title = $this->_navs[1][$url]['name'];
		$this->_place = $this->_navs[0][$this->_cokey].' &#187; '.$this->_title;
	}

	// 清除缓存
	public function clear_cache() {
		$this->runtime->truncate();

		try{ unlink(RUNTIME_PATH.'_runtime.php'); }catch(Exception $e) {}
		$tpmdir = array('_control', '_model', '_view');
		foreach($tpmdir as $dir) _rmdir(RUNTIME_PATH.APP_NAME.$dir);
		foreach($tpmdir as $dir) _rmdir(RUNTIME_PATH.F_APP_NAME.$dir);
		return TRUE;
	}

	// 初始化导航数组
	protected function init_navigation() {
		$this->_navs = array(
			array(
				'my'=>'我的',
				'setting'=>'设置',
				'category'=>'分类',
				'content'=>'内容',
				'theme'=>'主题',
				'plugin'=>'插件',
				'user'=>'用户',
				'tool'=>'工具',
			),
			array(
				'my-index'=>array('name'=>'后台首页', 'p'=>'my'),
				'my-password'=>array('name'=>'修改密码', 'p'=>'my'),
				'my-newtab'=>array('name'=>'新标签页', 'p'=>'my'),

				'setting-index'=>array('name'=>'基本设置', 'p'=>'setting'),
				'setting-seo'=>array('name'=>'SEO设置', 'p'=>'setting'),
				'setting-link'=>array('name'=>'链接设置', 'p'=>'setting'),
				'setting-attach'=>array('name'=>'附件设置', 'p'=>'setting'),

				'category-index'=>array('name'=>'分类管理', 'p'=>'category'),
				'models-index'=>array('name'=>'模型管理', 'p'=>'category'),

				'content-index'=>array('name'=>'内容管理', 'p'=>'content'),
				'comment-index'=>array('name'=>'评论管理', 'p'=>'content'),
				'tag-index'=>array('name'=>'标签管理', 'p'=>'content'),

				'theme-index'=>array('name'=>'主题管理', 'p'=>'theme'),

				'plugin-index'=>array('name'=>'插件管理', 'p'=>'plugin'),

				'user-index'=>array('name'=>'用户管理', 'p'=>'user'),
				'user_group-index'=>array('name'=>'用户组管理', 'p'=>'user'),

				'tool-index'=>array('name'=>'清除缓存', 'p'=>'tool'),
				'tool-rebuild'=>array('name'=>'重新统计', 'p'=>'tool'),
			),
		);

		// hook admin_admin_control_init_nav_after.php
	}

	// hook admin_admin_control_after.php
}
