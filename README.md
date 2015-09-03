# emlog-sina
Emlog第三方新浪微博帐号登录

使用前请现在Emlog数据库中运行此SQL

alter table emlog_user add weibo_openid  varchar(60) COLLATE 'utf8_general_ci' NOT NULL;
