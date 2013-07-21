<?php 
$_SERVER['_config'] = array(
	'url_suffix' => '.html',
	'twcms_view_php' => 1,

	'plugin_disable' => 0,			// 禁止掉所有插件

	'zone' => 'Asia/Shanghai',		// 时区

	// 数据库配置，type 为默认的数据库类型，可以支持多种数据库: mysql|pdo_mysql|mongodb	
	'db' => array(
		'type' => 'mysql',
		// 主数据库
		'master' => array(
			'host' => 'localhost',
			'user' => 'root',
			'password' => '123',
			'name' => 'twcms',
			'charset' => 'utf8',
			'tablepre' => 'tw_',
			'engine'=>'MyISAM',
		),
		// 从数据库(可以是从数据库服务器群，如果不设置将使用主数据库)
		/* 
		'slaves' => array(
			array(
				'host' => 'localhost',
				'user' => 'root',
				'password' => '123',
				'name' => 'twcms',
				'charset' => 'utf8',
				'engine'=>'MyISAM',
			),
		),
		*/
	),

	'cache' => array(
		'enable'=>0,
		'l2_cache'=>1,
		'type'=>'memcache',
		'pre' => 'tw_',
		'memcache'=>array (
			'multi'=>1,
			'host'=>'127.0.0.1',
			'port'=>'11211',
		)
	),
);