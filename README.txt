通王网站内容管理系统(TWCMS)，基于PHP+MySQL的技术架构。

TWCMS2.0定位于高安全、高性能、高扩展、高SEO、高傻瓜。

TWCMS2.0目录结构
	|--admin					后台文件目录
	|--static					静态文件目录
	|--twcms					核心目录
		|--block				模块目录
		|--config				配置目录
		|--control				控制器目录
		|--install				安装目录
		|--kongphp				框架目录
		|--model				模型目录
		|--plugin				插件目录
		|--runtime				运行目录
			|--backup			数据库备份目录
			|--filecache			文件缓存目录
			|--logs				系统日志目录
			|--syscache			系统变量缓存目录
			|--twcms_control		控制器编译缓存目录
			|--twcms_model			模型编译缓存目录
			|--twcms_view			视图编译缓存目录
			|--twcms_view_diy		DIY视图编译缓存目录
		|--view					视图目录
	|--upload					上传文件目录


TWCMS2.0简易模板引擎(共8个标签)
1，{inc:header.htm}			包含模板
2，{hook:header_before.htm}		模板钩子(方便插件修改模板)
3，{php}{/php}				模板支持PHP代码 (不支持<??><?php?>的写法，可在配置文件中关闭支持PHP代码)
4，{block:}{/block}			模板模块
5，{loop:}{/loop}			数组遍历
6，{if:} {else} {eleseif:} {/if}	逻辑判断
7，{$变量}				显示变量
8，{@$k+1}				显示逻辑变量 (用于运算时的输出，一般用的很少)
