<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

class form{
	// 文本
	public static function get_text($name, &$val, $class='inp w1') {
		return '<input name="'.$name.'" type="text" value="'.htmlspecialchars($val).'" class="'.$class.'" />';
	}

	// 多行文本
	public static function get_textarea($name, &$val, $class='inp w3') {
		return '<textarea name="'.$name.'" class="'.$class.'">'.htmlspecialchars($val).'</textarea>';
	}

	// 密码
	public static function get_password($name, &$val, $class='inp w2') {
		return '<input name="'.$name.'" type="password" value="'.$val.'" class="'.$class.'" />';
	}

	// 数字
	public static function get_number($name, &$val, $class='inp wnum') {
		return '<input name="'.$name.'" type="number" step="1" min="0" value="'.$val.'" class="'.$class.'">';
	}

	// 单选
	public static function get_yesno($name, &$val) {
		$s = '<label><input class="mr3" name="'.$name.'" type="radio" value="1"'.($val==1 ? ' checked="checked"' : '').'>&#26159;</label>';
		$s .= '<label><input class="mr3" name="'.$name.'" type="radio" value="0"'.($val==0 ? ' checked="checked"' : '').'>&#21542;</label>';
		return $s;
	}

	/**
	 * 循环控件
	 * @param string $type 类型
	 * @param string $name 表单名
	 * @param string $arr 分类数组
	 * @param string $val 默认选中值
	 * @param string $split 分隔字符串
	 */
	public static function loop($type, $name, $arr, &$val, $split = '<br>') {
		$s = '';
		switch ($type) {
			case 'radio':
				foreach ($arr as $v => $n){
					$s .= '<label><input class="mr3" name="'.$name.'" type="radio" value="'.$v.'"'.($v==$val ? ' checked="checked"' : '').'>'.$n.'</label>'.$split;
				}
				break;
			case 'checkbox':
				foreach ($arr as $v => $n){
					$s .= '<label><input class="mr3" name="'.$name.'[]" type="checkbox" value="'.$v.'"'.(in_array($v, explode(',', $val)) ? ' checked="checked"' : '').'>'.$n.'</label>'.$split;
				}
				break;
			case 'select':
				$s .= '<select name="'.$name.'" class="se1">';
				foreach ($arr as $v => $n){
					$s .= '<option value="'.$v.'"'.($v==$val ? ' selected="selected"' : '').'>'.$n.'</option>';
				}
				$s .= '</select>';
				break;
			case 'multiple':
				$s .= '<select name="'.$name.'[]" multiple="multiple" class="se2">';
				foreach ($arr as $v => $n){
					$s .= '<option value="'.$v.'"'.(in_array($v, explode(',', $val)) ? ' selected="selected"' : '').'>'.$n.'</option>';
				}
				$s .= '</select>';
		}
		return $s;
	}
}
