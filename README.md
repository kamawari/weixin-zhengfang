#正方教务系统--微信助手

## 说明：
通过微信公众号查询成绩，课表等。适用于正方教务系统

没有包含验证码识别，走的是不需要验证码的入口：```/default_ysdx.aspx```

原理很简单，就是curl。由于写不同功能的时间不同，simple_html_dom和正则混杂着用，见谅。

## 用法：
* 建表：
```
    CREATE TABLE `jxgl_user` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `openid` varchar(100) NOT NULL,
            `username` varchar(100) DEFAULT NULL,
            `password` varchar(100) DEFAULT NULL,
            `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `openid` (`openid`)
            ) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8
```
* 修改config/config.php

* 微信后台填写url，token默认为"weixin",详见[https://github.com/netputer/wechat-php-sdk](ttps://github.com/netputer/wechat-php-sdk) ,这个很久没更新了，可以换个包或者自己写。

## 功能 (详见lib/function.php)

* 查询图书馆 （好像是ILASIII系统）

 **以下需要绑定学号密码**
* 查成绩
* 查课表
* 查考试安排


## 示例

![qrcode](https://raw.githubusercontent.com/liaol/weixin-zhengfang/master/qrcode.jpg)



Have fun!

