INSERT INTO `pre_user` (`uid`, `username`, `password`, `salt`, `groupid`, `email`, `homepage`, `intro`, `regip`, `regdate`, `loginip`, `logindate`, `lastip`, `lastdate`, `contents`, `uploads`, `comments`, `logins`) VALUES
(1, 'admin', '31adb5784cd04b2ce8b45c1661b511ad', 'admin', 1, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

INSERT INTO `pre_user_group` (`groupid`, `groupname`, `system`, `purviews`) VALUES
(1, '管理员组', 1, ''),
(2, '主编组', 1, ''),
(3, '编辑组', 1, ''),
(6, '待验证用户组', 1, ''),
(7, '禁止用户组', 1, ''),
(11, '注册用户', 1, '');
