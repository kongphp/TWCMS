<?php $err = 0; ?>
<div class="content">
	<h2>服务器环境检测是否可以正常运行TWCMS</h2>
	<table class="tb">
		<tr>
			<th width="150">检查项目</th>
			<th width="150">推荐配置</th>
			<th>当前配置</th>
		</tr>
		<tr>
			<td>服务器</td>
			<td>Apache/2.2.x-Linux</td>
			<td><?php echo trim(preg_replace(array('#PHP\/[\d\.]+#', '#\([\w]+\)#'), '', $_SERVER['SERVER_SOFTWARE'])).'-'.PHP_OS;?></td>
		</tr>
		<tr>
			<td>PHP版本</td>
			<td>5.2.x</td>
			<td><?php echo PHP_VERSION; ?></td>
		</tr>
		<tr>
			<td>上传限制</td>
			<td>2M</td>
			<td><?php echo function_exists('ini_get') && ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow'; ?></td>
		</tr>
		<tr>
			<td>磁盘空间</td>
			<td>10M+</td>
			<td><?php echo function_exists('disk_free_space') ? get_byte(disk_free_space(TWCMS_ROOT)) : 'unknow'; ?></td>
		</tr>
		<tr>
			<td>mysql扩展</td>
			<td>必须开启</td>
			<td><?php
				if(extension_loaded('mysql')) {
					echo '<i>开启[√]</i>';
				}else{
					$err = 1;
					echo '<u>关闭[×]</u>';
				} ?> (关闭将无法使用本系统)</td>
		</tr>
		<tr>
			<td>gd扩展</td>
			<td>建议开启</td>
			<td><?php
				$gd  = '';
				if(extension_loaded('gd')) {
					function_exists('imagepng') && $gd .= ' png';
					function_exists('imagejpeg') && $gd .= ' jpg';
					function_exists('imagegif') && $gd .= ' gif';
				}
				echo $gd ? '<i>开启[√]'.$gd.'</i>' : '<u>关闭[×]</u>';
			?> (关闭将不支持缩略图、水印和验证码)</td>
		</tr>
		<tr>
			<td>allow_url_fopen</td>
			<td>建议开启</td>
			<td><?php echo ini_get('allow_url_fopen') ? '<i>开启[√]</i>' : '<u>关闭[×]</u>'; ?> (关闭将不支持远程本地化，在线安装模板和插件)</td>
		</tr>
	</table>
	<table class="tb">
		<tr>
			<th width="150">目录名</th>
			<th width="150">需要状态</th>
			<th>当前状态</th>
		</tr>
		<?php
		echo '<tr><td>/</td><td>可写 (*nix系统 0777)</td><td>';
		if(_is_writable(TWCMS_ROOT)) {
			echo '<i>可写[√]</i>';
		}else{
			$err = 1;
			echo '<u>不可写[×]</u>';
		}
		echo '</td></tr>';

		$dirs = array(APP_NAME.'/config', APP_NAME.'/log', APP_NAME.'/runtime', APP_NAME.'/plugin', APP_NAME.'/view', 'upload');
		foreach($dirs as $dir) {
			$ret = _dir_write(TWCMS_ROOT.'/'.$dir, TRUE);

			echo '<tr><td>/'.$dir.'/*</td><td>可写 (*nix系统 0777)</td><td>';
			if(!empty($ret['no'])) {
				$err = 1;
				echo '<u>不可写[×]';
				foreach($ret['no'] as $i => $row) {
					echo '<br>['.$row[1].'] '.str_replace(TWCMS_ROOT, '', $row[0]);
					if($i>8) {
						echo '<br>******'; break;
					}
				}
			}else{
				echo '<i>可写[√]</i>';
			}
			echo '</u></td></tr>';
		}
		?>
	</table>
</div>
<div class="button">
	<?php if($err) { ?>
	<a href="javascript:;" class="grey">下一步</a><a href="index.php?do=license">上一步</a>
	<?php }else{ ?>
	<a href="index.php?do=check_db">下一步</a><a href="?do=license">上一步</a>
	<?php } ?>
</div>
