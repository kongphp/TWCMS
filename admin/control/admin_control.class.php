<?php
/**
 *	[TWCMS] (C)2012-2013 TongWang Inc.
 */

defined('TWCMS_PATH') or exit;

class admin_control extends control {
	public $_user = array();	// 用户
	public $_group = array();	// 用户组

	public $_navs = array();	// 导航
	public $_title = '';		// 标题
	public $_place = '';		// 位置

	function __construct() {
		$_SERVER['_config']['FORM_HASH'] = form_hash();
		$this->assign('C', $_SERVER['_config']);

		$admauth = R($_SERVER['_config']['cookie_pre'].'admauth', 'R');

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
					}elseif($_SERVER['_ip'] != $ip) {
						_setcookie('admauth', '', 1);
						$this->message(0, '您的IP已经改变，为了安全考虑，请重新登录！', '?u=index-login.html');
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
				$this->message(0, '非法访问，请登陆后再试！', '?u=index-login.html');
			}
			exit('<html><body><script>top.location="?u=index-login.html"</script></body></html>');
		}

		// hook admin_admin_control_check_after.php
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
			$navs_new = array('my' => $this->_navs['my']);
			if(isset($purviews['navs']) && is_array($purviews['navs'])) {
				$navs_new = array_merge($navs_new, $purviews['navs']);
			}
			$this->_navs = $navs_new;

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
		$control = &$_GET['control'];
		$action = &$_GET['action'];

		$this->_title = isset($this->_navs[$control]['sub'][$action]) ? $this->_navs[$control]['sub'][$action] : '&#26410;&#30693;';
		$this->_place = (isset($this->_navs[$control]['name']) ? $this->_navs[$control]['name'] : '&#26410;&#30693;').' &#187; '.$this->_title;
	}

	// 初始化导航数组
	protected function init_navigation() {
		$this->_navs = array(
			'my'=>array(
				'name'=>'我的',
				'sub'=>array(
					'index'=>'后台首页',
					'newtab'=>'新标签页',
					'password'=>'修改密码',
				)
			),
			'setting'=>array(
				'name'=>'设置',
				'sub'=>array(
					'index'=>'基本设置',
					'seo'=>'SEO设置',
					'ishtml'=>'链接设置',
					'attachment'=>'附件设置',
				)
			),
			'category'=>array(
				'name'=>'分类',
				'sub'=>array(
					'index'=>'分类管理',
					'model'=>'模型管理',
				)
			),
			'content'=>array(
				'name'=>'内容',
				'sub'=>array(
					'index'=>'内容管理',
					'comment'=>'评论管理',
					'tag'=>'标签管理',
				)
			),
			'theme'=>array(
				'name'=>'主题',
				'sub'=>array(
					'index'=>'主题设置',
					'modify'=>'主题编辑',
				)
			),
			'plugin'=>array(
				'name'=>'插件',
				'sub'=>array(
					'index'=>'插件管理',
				)
			),
			'user'=>array(
				'name'=>'用户',
				'sub'=>array(
					'index'=>'用户管理',
					'group'=>'用户组管理',
				)
			),
			'tool'=>array(
				'name'=>'工具',
				'sub'=>array(
					'index'=>'清除缓存',
					'rebuild'=>'重新统计',
				)
			),
		);

		//hook admin_admin_control_get_navigation_end.php
	}
	
	//hook admin_admin_control_after.php
}