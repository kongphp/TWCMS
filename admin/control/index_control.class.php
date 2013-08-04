<?php
/**
 *	[TWCMS] (C)2012-2013 TongWang Inc.
 */

defined('TWCMS_PATH') or exit;

class index_control extends admin_control{
	public function index() {
		$this->display();
	}

	public function login() {
		if(empty($_POST)) {
			$this->display();
		}elseif(form_submit()) {
			$username = R('username', 'P');
			$password = R('password', 'P');

			if(empty($username)) {
				exit('{"name":"username", "message":"啊哦，帐号不能为空哦！"}');
			}elseif(empty($password)){
				exit('{"name":"password", "message":"啊哦，密码不能为空哦！"}');
			}elseif(strlen($password) < 6){
				exit('{"name":"password", "message":"啊哦，密码不能小于6位哦！"}');
			}

			$user = M('user');
			$users = $user->get_user_by_username($username);
			if($users && $user->verify_password($password, $users['salt'], $users['password'])) {
				// 写入 cookie
				$admauth = str_auth("$users[uid]\t$users[username]\t$users[password]\t$users[groupid]\t$_SERVER[_ip]", 'ENCODE');
				_setcookie('admauth', $admauth, 0, '', '', false, true);

				// 更新登陆信息
				$data = array(
					'uid' => $users['uid'],
					'loginip' => ip2long($_SERVER['_ip']),
					'logindate' => $_SERVER['_time'],
					'lastip' => $users['loginip'],
					'lastdate' => $users['logindate'],
					'logins' => intval($users['logins'])+1,
				);
				$user->update($data);

				exit('{"name":"", "message":"登录成功！"}');
			}else{
				exit('{"name":"password", "message":"啊哦，帐号或密码不正确!"}');
			}
		}else{
			exit('{"name":"username", "message":"啊哦，表单失效！请刷新后再试！"}');
		}
	}
}