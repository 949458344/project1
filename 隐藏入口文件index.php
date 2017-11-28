
RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
变成
RewriteRule ^(.*)$ index.php?/$1 [QSA,PT,L]


网站根目录必须指向public,不然要加上index.php入口文件，否则报错

1.httpd.conf配置文件中加载了mod_rewrite.so模块  //在APACHE里面去配置

#LoadModule rewrite_module modules/mod_rewrite.so把前面的警号去掉


2.AllowOverride None 讲None改为 All
在APACHE里面去配置 (注意其他地方的AllowOverride也统统设置为ALL)
<Directory "D:/server/apache/cgi-bin">
AllowOverride none  改   AllowOverride ALL
Options None
Order allow,deny
Allow from all
</Directory>