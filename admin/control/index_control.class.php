<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class index_control extends admin_control{
	// 后台首页
	public function index() {
		unset($this->_navs[1]['my-newtab']);
		foreach ($this->_navs[1] as $k => $v) {
			$this->_navs[2][$v['p']][$k] = $v;
		}
		unset($this->_navs[1]);

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

			// 防IP暴力破解
			$ip = &$_ENV['_ip'];
			if($user->anti_ip_brute($ip)) {
				exit('{"name":"password", "message":"啊哦，请15分钟之后再试！"}');
			}

			$users = $user->get_user_by_username($username);
			if($users && $user->verify_password($password, $users['salt'], $users['password'])) {
				// 写入 cookie
				$admauth = str_auth("$users[uid]\t$users[username]\t$users[password]\t$users[groupid]\t$ip", 'ENCODE');
				_setcookie('admauth', $admauth, 0, '', '', false, true);

				// 更新登陆信息
				$data = array(
					'uid' => $users['uid'],
					'loginip' => ip2long($ip),
					'logindate' => $_ENV['_time'],
					'lastip' => $users['loginip'],
					'lastdate' => $users['logindate'],
					'logins' => intval($users['logins'])+1,
				);
				$user->update($data);

				// 删除密码错误记录
				$this->runtime->delete('password_error_'.$ip);

				exit('{"name":"", "message":"登录成功！"}');
			}else{
				// 记录密码错误日志
				$log_password = '******'.substr($password, 6);
				log::write("密码错误：$username - $log_password", 'login_log.php');

				// 记录密码错误次数
				$user->password_error($ip);

				exit('{"name":"password", "message":"啊哦，帐号或密码不正确！"}');
			}
		}else{
			exit('{"name":"username", "message":"啊哦，表单失效！请刷新后再试！"}');
		}
	}

	// 后台登出
	public function logout(){
		_setcookie('admauth', '', 1);
		exit('<html><body><script>top.location="?u=index-login"</script></body></html>');
	}

	//hook admin_index_control_after.php
}
