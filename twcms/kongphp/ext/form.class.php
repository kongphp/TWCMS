<?php
// +------------------------------------------------------------------------------
// | Copyright (C) 2013 wuzhaohuan <kongphp@gmail.com> All rights reserved.
// +------------------------------------------------------------------------------

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
	 * @param string $on 默认选中值
	 * @param string $br 是否换行
	 */
	public static function loop($type, $name, $arr, $on, $br=true) {
		$s = '';
		switch ($type) {
			case 'radio':
				foreach ($arr as $a){
					$s .= '<label><input class="mr3" name="'.$name.'" type="radio" value="'.$a[0].'"'.($a[0]==$on ? ' checked="checked"' : '').'>'.$a[1].'</label>';
					if($br) $s.='<br>';
				}
				break;
			case 'checkbox':
				foreach ($arr as $a){
					$s .= '<label><input class="mr3" name="'.$name.'[]" type="checkbox" value="'.$a[0].'"'.(in_array($a[0],explode(',', $on)) ? ' checked="checked"' : '').'>'.$a[1].'</label>';
					if($br) $s.='<br>';
				}
				break;
			case 'select':
				$s .= '<select name="'.$name.'" class="se1">';
				foreach ($arr as $a){
					$s .= '<option value="'.$a[0].'"'.($a[0]==$on ? ' selected="selected"' : '').'>'.$a[1].'</option>';
				}
				$s .= '</select>';
				break;
			case 'multiple':
				$s .= '<select name="'.$name.'[]" multiple="multiple" class="se2">';
				foreach ($arr as $a){
					$s .= '<option value="'.$a[0].'"'.(in_array($a[0],explode(',', $on)) ? ' selected="selected"' : '').'>'.$a[1].'</option>';
				}
				$s .= '</select>';
		}
		return $s;
	}
}
