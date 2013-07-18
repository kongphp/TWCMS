twcms
=====

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
		|--kongphp			框架目录
		|--model				模型目录
		|--plugin				插件目录
		|--runtime				运行目录
			|--backup			数据库备份目录
			|--filecache		文件缓存目录
			|--logs			系统日志目录
			|--syscache		系统变量缓存目录
			|--twcms_control	控制器编译缓存目录
			|--twcms_model	模型编译缓存目录
			|--twcms_view		视图编译缓存目录
			|--twcms_view_diy	DIY视图编译缓存目录
		|--view				视图目录
	|--upload					上传文件目录
