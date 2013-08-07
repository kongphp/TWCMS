<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class index_control extends admin_control{
	// 后台首页
	public function index() {
		$this->display();
		exit;
	}

	// 后台登陆
	public function login() {
		if(empty($_POST)) {
			$this->display();
		}elseif(form_submit()) {
			$user = &$this->user;
			$username = R('username', 'P');
			$password = R('password', 'P');

			if($message = $user->check_username($username)) {
				exit('{"name":"username", "message":"啊哦，'.$message.'"}');
			}elseif($message = $user->check_password($password)){
				exit('{"name":"password", "message":"啊哦，'.$message.'"}');
			}

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

	// 后台登出
	public function logout(){
		_setcookie('admauth', '', 1);
		exit('<html><body><script>top.location="?u=index-login.html"</script></body></html>');
	}

	//hook admin_index_control_after.php
}
