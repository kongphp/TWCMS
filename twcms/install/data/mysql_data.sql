INSERT INTO `pre_user_group` (`groupid`, `groupname`, `system`, `purviews`) VALUES
(1, '管理员组', 1, ''),
(2, '主编组', 1, ''),
(3, '编辑组', 1, ''),
(6, '待验证用户组', 1, ''),
(7, '禁止用户组', 1, ''),
(11, '注册用户', 1, '');

INSERT INTO `pre_models` (`mid`, `name`, `tablename`, `index_tpl`, `cate_tpl`, `show_tpl`, `system`) VALUES
(1, '单页', 'page', '', 'page_show.htm', '', 1),
(2, '文章', 'article', 'article_index.htm', 'article_list.htm', 'article_show.htm', 1),
(3, '产品', 'product', 'product_index.htm', 'product_list.htm', 'product_show.htm', 1),
(4, '图集', 'photo', 'photo_index.htm', 'photo_list.htm', 'photo_show.htm', 1);

INSERT INTO `pre_kv` (`k`, `v`, `expiry`) VALUES
('link_keywords', '["tag","tag_top","comment","index","sitemap","admin","user","space"]', 0);
