<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

class view{
	private $vars = array();			//模板变量集合
	private $head_arr = array();		//模板头部代码数组

	public function __construct() {
		$_ENV['_theme'] = 'default';	//主题目录
		$_ENV['_view_diy'] = FALSE;		//DIY模板解析是否开启
	}

	public function assign($k, &$v) {
		$this->vars[$k] = &$v;
	}

	public function assign_value($k, $v) {
		$this->vars[$k] = $v;
	}

	// 注意: 为安全考虑，$filename 尽量限制为 (英文 数字 _ .)
	public function display($filename = null) {
		$_ENV['_tplname'] = is_null($filename) ? $_GET['control'].'_'.$_GET['action'].'.htm' : $filename;
		extract($this->vars, EXTR_SKIP);
		include $this->get_tplfile($_ENV['_tplname']);
	}

	private function get_tplfile($filename) {
		$view_dir = APP_NAME.($_ENV['_view_diy'] ? '_view_diy' : '_view').'/';
		$php_file = RUNTIME_PATH.$view_dir.$_ENV['_theme'].','.$filename.'.php';

		if(!is_file($php_file) || DEBUG) {
			$tpl_file = core::get_original_file($filename, VIEW_PATH.$_ENV['_theme'].'/');

			if(!$tpl_file) {
				throw new Exception('模板文件 '.$_ENV['_theme'].'/'.$filename.' 不存在');
			}

			if(FW($php_file, $this->tpl_parse($tpl_file)) === false) {
				throw new Exception("写入模板编译文件 $filename 失败");
			}
		}
		return $php_file;
	}

	private function tpl_parse($tpl_file) {
		//严格要求的变量和数组 $abc[a]['b']["c"][$d] 合法    $abc[$a[b]] 不合法
		$reg_arr = '[a-zA-Z_]\w*(?:\[\w+\]|\[\'\w+\'\]|\[\"\w+\"\]|\[\$[a-zA-Z_]\w*\])*';

		$s = file_get_contents($tpl_file);

		//第1步 包含inc模板
		$s = preg_replace_callback('#\{inc\:([\w\.]+)\}#', array($this, 'parse_inc'), $s);

		//第2步 解析模板hook
		$s = preg_replace_callback('#\{hook\:([\w\.]+)\}#', array('core', 'parse_hook'), $s);

		//第3步 解析php代码
		$s = preg_replace('#(?:\<\?.*?\?\>|\<\?.*)#s', '', $s);	//清理掉PHP语法(目的统一规范)
		$s = preg_replace('#\{php\}(.*?)\{\/php\}#s', '<?php \\1 ?>', $s);
		//$s = preg_replace('#\{php\}.*?\{\/php\}#s', '', $s);	//特殊需求，不想让模板支持PHP代码

		//第4步 包含block
		$s = preg_replace_callback('#\{block\:([a-zA-Z_]\w*)\040?([^\n\}]*?)\}(.*?){\/block}#s', array($this, 'parse_block'), $s);

		//第5步 解析loop
		while(preg_match('#\{loop\:\$'.$reg_arr.'(?:\040\$[a-zA-Z_]\w*){1,2}\}.*?\{\/loop\}#s', $s))
			$s = preg_replace_callback('#\{loop\:(\$'.$reg_arr.'(?:\040\$[a-zA-Z_]\w*){1,2})\}(.*?)\{\/loop\}#s', array($this, 'parse_loop'), $s);

		//第6步 解析if (未考虑安全过滤)
		while(preg_match('#\{if\:[^\n\}]+\}.*?\{\/if\}#s', $s))
			$s = preg_replace_callback('#\{if\:([^\n\}]+)\}(.*?)\{\/if\}#s', array($this, 'parse_if'), $s);

		//第7步 解析变量
		$s = preg_replace('#\{\@([^\}]+)\}#', '<?php echo(\\1); ?>', $s);	//用于运算时的输出 如 {@$k+2}
		$s = preg_replace_callback('#\{(\$'.$reg_arr.')\}#', array($this, 'parse_vars'), $s);

		// $s = str_replace(array("\r\n", "\n", "\t"), '', $s); // 压缩HTML代码

		//第8步 组合模板代码
		$head_str = empty($this->head_arr) ? '' : implode("\r\n", $this->head_arr);
		$s = "<?php defined('APP_NAME') || exit('Access Denied'); $head_str\r\n?>$s";
		$s = str_replace('?><?php ', '', $s);

		return $s;
	}

	private function parse_inc($matches) {
		// 注意：在可视化设计时需要排除前缀 inc- 的模板，所以不能去掉前缀
		$filename = 'inc-'.$matches[1];
		$tpl_file = core::get_original_file($filename, VIEW_PATH.$_ENV['_theme'].'/');

		if(!$tpl_file) {
			throw new Exception('模板文件 '.$_ENV['_theme'].'/'.$filename.' 不存在');
		}

		return file_get_contents($tpl_file);
	}

	private function parse_block($matches) {
		$func = $matches[1];
		$config = $matches[2];
		$s = $matches[3];

		$lib_file = core::get_original_file('kp_block_'.$func.'.lib.php', BLOCK_PATH);
		if(!is_file($lib_file)) return '';

		//为减少IO，把需要用到的函数代码放到模板解析代码头部
		$lib_str = file_get_contents($lib_file);
		$lib_str = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', array('core', 'parse_hook'), $lib_str);
		if(!DEBUG) $lib_str = _strip_whitespace($lib_str);
		$lib_str = core::clear_code($lib_str);
		$this->head_arr['kp_block_'.$func] = $lib_str;

		$s = $this->rep_double($s);
		$config = $this->rep_double($config);

		//解析设置数组并生成执行函数
		$config_arr = array();
		preg_match_all('#([a-zA-Z_]\w*)="(.*?)" #', $config.' ', $m);
		foreach($m[2] as $k=>$v) {
			if(isset($v)) $config_arr[strtolower($m[1][$k])] = addslashes($v);
		}
		unset($m);
		$func_str = 'kp_block_'.$func.'('.var_export($config_arr, 1).');';

		//-----------定义转换后的首尾代码-----------
		$before = $after = '';
		//公共块移到模板解析代码头部
		if(substr($func, 0, 7) == 'global_') {
			$this->head_arr[$func] = '$gdata = '.$func_str;
		}else{
			$before .= '<?php $data = '.$func_str.' ?>';
			$after .= '<?php unset($data); ?>';
		}
		//DIY模板时才能用到
		if($_ENV['_view_diy']) {
			$this->kp_block_id++;
			$before .= '<span kp_block_diy="before" kp_block_id="'.$this->kp_block_id.'"></span>';
			$after .= '<span kp_block_diy="after" kp_block_id="'.$this->kp_block_id.'"></span>';
		}
		return $before.$s.$after;
	}

	//严格要求格式 {loop:$arr[a] $v $k}
	private function parse_loop($matches) {
		$args = explode(' ', $this->rep_double($matches[1]));
		$s = $this->rep_double($matches[2]);

		$arr = $this->rep_vars($args[0]);
		$v = empty($args[1]) ? '$v' : $args[1];
		$k = empty($args[2]) ? '' : $args[2].'=>';
		return "<?php if(isset($arr) && is_array($arr)) { foreach($arr as $k&$v) { ?>$s<?php }} ?>";
	}

	private function parse_if($matches) {
		$expr = $this->rep_double($matches[1]);
		$expr = $this->rep_vars($expr);
		$s = preg_replace_callback('#\{elseif\:([^\n\}]+)\}#', array($this, 'rep_elseif'), $this->rep_double($matches[2]));
		$s = str_replace('{else}', '<?php }else{ ?>', $s);
		return "<?php if ($expr) { ?>$s<?php } ?>";
	}

	private function rep_elseif($matches) {
		$expr = $this->rep_double($matches[1]);
		$expr = $this->rep_vars($expr);
		return "<?php }elseif($expr) { ?>";
	}

	private function parse_vars($matches) {
		$vars = $this->rep_double($matches[1]);
		$vars = $this->rep_vars($vars);
		return "<?php echo(isset($vars) ? $vars : ''); ?>";
	}

	//替换 " 号， 注意只能是 " 号
	private function rep_double($s) {
		return str_replace('\"', '"', $s);
	}

	//转$abc[a]['b']["c"][$d] 为 $abc['a']['b']['c'][$d]
	private function rep_vars($s) {
		$s = preg_replace('#\[(\w+)\]#', "['\\1']", $s);
		$s = preg_replace('#\[\"(\w+)\"\]#', "['\\1']", $s);
		$s = preg_replace('#\[\'(\d+)\'\]#', '[\\1]', $s);
		return $s;
	}
}
