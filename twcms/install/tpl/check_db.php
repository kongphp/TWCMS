<form id="form" method="post" action="index.php?do=complete">
<div class="content">
	<h2>数据库信息请从空间商获取</h2>
	<table class="tb">
		<tr>
			<th colspan="2">数据库设置</th>
		</tr>
		<tr>
			<td width="80">主机</td>
			<td><input class="inp" id="dbhost" name="dbhost" type="text" value="localhost" tips="如果数据库和程序不在同一服务器请填IP"></td>
		</tr>
		<tr>
			<td>用户名</td>
			<td><input class="inp" id="dbuser" name="dbuser" type="text" value="root" tips="请填写数据库用户名"></td>
		</tr>
		<tr>
			<td>密码</td>
			<td><input class="inp" id="dbpw" name="dbpw" type="password" value="" tips="请填写数据库密码"></td>
		</tr>
		<tr>
			<td>数据库名</td>
			<td><input class="inp" id="dbname" name="dbname" type="text" value="twcms" tips="请填写数据库名"></td>
		</tr>
		<tr>
			<td>表前辍</td>
			<td><input class="inp" id="dbpre" name="dbpre" type="text" value="tw_" tips="如安装多套twcms，请修改"></td>
		</tr>
		<tr>
			<td>覆盖安装</td>
			<td><input name="cover" type="checkbox" value="1"></td>
		</tr>
	</table>
	<table class="tb">
		<tr>
			<th colspan="2">创始人设置</th>
		</tr>
		<tr>
			<td width="80">用户名</strong></td>
			<td><input class="inp" id="adm_user" name="adm_user" type="text" value="admin" tips="请填写用户名，后台登陆时使用"></td>
		</tr>
		<tr>
			<td>密码</strong></td>
			<td><input class="inp" id="adm_pass" name="adm_pass" type="password" value="" tips="请填写密码，不能小于8位"></td>
		</tr>
	</table>
</div>
<div class="button"><input id="submit" type="submit" value="下一步" class="but grey" /><a href="index.php?do=check_env">上一步</a></div>
</form>
