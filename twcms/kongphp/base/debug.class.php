<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

class debug{
	/**
	 * 初始化 debug 操作
	 */
	public static function init() {
		if(DEBUG) {
			error_reporting(E_ALL | E_STRICT);
			register_shutdown_function(array('debug', 'shutdown_handler'));	// 程序关闭时执行
		}else{
			error_reporting(0);	// 关闭错误输出
		}
		function_exists('ini_set') && ini_set('display_errors', 'On');
		set_error_handler(array('debug', 'error_handler'));	// 设置错误处理方法
		set_exception_handler(array('debug', 'exception_handler'));	// 设置异常处理方法
	}

	/**
	 * 错误处理
	 * @param string $errno 错误类型
	 * @param string $errstr 错误消息
	 * @param string $errfile 错误文件
	 * @param int $errline 错误行号
	 */
	public static function error_handler($errno, $errstr, $errfile, $errline) {
		if(!empty($_ENV['_exception'])) return;

		// 兼容 php 5.3 以下版本
		defined('E_DEPRECATED') || define('E_DEPRECATED', 8192);
		defined('E_USER_DEPRECATED') || define('E_USER_DEPRECATED', 16384);

		$error_type = array(
			E_ERROR					=> '运行错误',
			E_WARNING				=> '运行警告',
			E_PARSE					=> '语法错误',
			E_NOTICE				=> '运行通知',
			E_CORE_ERROR			=> '初始错误',
			E_CORE_WARNING			=> '初始警告',
			E_COMPILE_ERROR			=> '编译错误',
			E_COMPILE_WARNING		=> '编译警告',
			E_USER_ERROR			=> '用户定义的错误',
			E_USER_WARNING			=> '用户定义的警告',
			E_USER_NOTICE			=> '用户定义的通知',
			E_STRICT				=> '代码标准建议',
			E_RECOVERABLE_ERROR		=> '致命错误',
			E_DEPRECATED			=> '代码警告',
			E_USER_DEPRECATED		=> '用户定义的代码警告',
		);

		$errno_str = isset($error_type[$errno]) ? $error_type[$errno] : '未知错误';
		$s = "[$errno_str] : $errstr";
		if(DEBUG) {
			throw new Exception($s);
		}else{
			// 线上模式放宽一些，只记录日志，不中断程序执行
			if(in_array($errno, array(E_NOTICE, E_USER_NOTICE, E_DEPRECATED))) {
				log::write($s);
			}else{
				throw new Exception($s);
			}
		}
	}

	/**
	 * 异常处理
	 * @param int $e 异常对象
	 */
	public static function exception_handler($e) {
		DEBUG && $_ENV['_exception'] = 1;	// 只输出一次

		// 第1步正确定位
		$trace = $e->getTrace();
		if(!empty($trace) && $trace[0]['function'] == 'error_handler' && $trace[0]['class'] == 'debug') {
			$message = $e->getMessage();
			$file = $trace[0]['args'][2];
			$line = $trace[0]['args'][3];
		}else{
			$message = '[程序异常] : '.$e->getMessage();
			$file = $e->getFile();
			$line = $e->getLine();
		}
		$message = self::to_message($message);

		// 第2步写日志 (暂不使用 error_log() )
		log::write("$message File: $file [$line]");

		// 第3步根据情况输出错误信息
		try{
			core::ob_clean();
			if(R('ajax', 'R')) {
				if(DEBUG) {
					$kp_error = "$message File: $file [$line]<br><br>".str_replace("\n", '<br>', $e->getTraceAsString());
				}else{
					$len = strlen($_SERVER['DOCUMENT_ROOT']);
					$file = substr($file, $len);
					$kp_error = "$message File: $file [$line]";
				}
				echo json_encode(array('kp_error' => $kp_error));
			}else{
				if(DEBUG) {
					self::exception($message, $file, $line, $e->getTraceAsString());
				}else{
					$len = strlen($_SERVER['DOCUMENT_ROOT']);
					$file = substr($file, $len);
					self::sys_error($message, $file, $line);
				}
			}
		}catch(Exception $e) {
			echo get_class($e)." thrown within the exception handler. Message: ".$e->getMessage()." on line ".$e->getLine();
		}
	}

	/**
	 * 输出异常信息
	 * @param string $message 异常消息
	 * @param string $file 异常文件
	 * @param int $line 异常行号
	 * @param string $tracestr 异常追踪信息
	 */
	public static function exception($message, $file, $line, $tracestr) {
		include KONG_PATH.'tpl/exception.php';
	}

	/**
	 * 数组转换成HTML代码 (支持双行变色)
	 * @param array $arr 一维数组
	 * @param int $type 显示类型
	 * @param boot $html 是否转换为 HTML 实体
	 * @return string
	 */
	public static function arr2str($arr, $type = 2, $html = TRUE) {
		$s = '';
		$i = 0;
		foreach($arr as $k => $v) {
			switch ($type) {
				case 0:
					$k = ''; break;
				case 1:
					$k = "#$k "; break;
				default:
					$k = "#$k => ";
			}

			$i++;
			$c = $i%2 == 0 ? ' class="even"' : '';
			$html && is_string($v) && $v = htmlspecialchars($v);
			if(is_array($v) || is_object($v)) {
				$v = gettype($v);
			}
			$s .= "<li$c>$k$v</li>";
		}
		return $s;
	}

	/**
	 * 程序关闭时执行
	 */
	public static function shutdown_handler() {
		if(empty($_ENV['_exception'])) {
			if($e = error_get_last()) {
				core::ob_clean();
				$message = $e['message'];
				$file = $e['file'];
				$line = $e['line'];
				if(R('ajax', 'R')) {
					if(!DEBUG) {
						$len = strlen($_SERVER['DOCUMENT_ROOT']);
						$file = substr($file, $len);
					}
					$kp_error = "[致命错误] : $message File: $file [$line]";
					echo json_encode(array('kp_error' => $kp_error));
				}else{
					self::sys_error('[致命错误] : '.$message, $file, $line);
				}
			}
		}
	}

	/**
	 * 输出系统错误
	 * @param string $message 错误消息
	 * @param string $file 错误文件
	 * @param int $line 错误行号
	 */
	public static function sys_error($message, $file, $line) {
		include KONG_PATH.'tpl/sys_error.php';
	}

	/**
	 * 获取错误定位代码
	 * @param string $file 错误文件
	 * @param int $line 错误行号
	 * @return array
	 */
	public static function get_code($file, $line) {
		$arr = file($file);
		$arr2 = array_slice($arr, max(0, $line - 5), 10, true);

		$s = '<table cellspacing="0" width="100%">';
		foreach ($arr2 as $i => &$v) {
			$i++;
			$v = htmlspecialchars($v);
			$v = str_replace(' ', '&nbsp;', $v);
			$v = str_replace('	', '&nbsp;&nbsp;&nbsp;&nbsp;', $v);
			$s .= '<tr'.($i == $line ? ' style="background:#faa;"' : '').'><td width="40">#'.$i."</td><td>$v</td>";
		}
		$s .= '</table>';
		return $s;
	}

	/**
	 * 过滤消息内容
	 * @param string $s 消息内容
	 * @return string
	 */
	public static function to_message($s) {
		$s = strip_tags($s);
		if(strpos($s, 'mysql_connect') !== false) {
			$s .= ' [连接数据库出错！配置信息见 config.inc.php]';
		}
		return $s;
	}

	/**
	 * 输出追踪信息
	 */
	public static function sys_trace() {
		include KONG_PATH.'tpl/sys_trace.php';
	}
}
?>
