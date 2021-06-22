workerman-chat
=======
基于workerman的GatewayWorker框架开发的一款高性能支持分布式部署的聊天室系统。

GatewayWorker框架文档：http://www.workerman.net/gatewaydoc/

原项目地址：https://github.com/walkor/workerman-chat

 特性
======
 * 使用websocket协议
 * 多浏览器支持（浏览器支持html5或者flash任意一种即可）
 * 多房间支持
 * 私聊支持
 * 掉线自动重连
 * 微博图片自动解析
 * 聊天内容支持微博表情
 * 支持多服务器部署
 * 业务逻辑全部在一个文件中，快速入门可以参考这个文件[Applications/Chat/Event.php](https://github.com/walkor/workerman-chat/blob/master/Applications/Chat/Event.php)   
  
下载安装
=====
1、git clone https://github.com/wangxu-999999999/workerman-chat.git

2、composer install

启动停止(Linux系统)
=====
以debug方式启动  
```php start.php start  ```

以daemon方式启动  
```php start.php start -d ```

启动(windows系统)
======
双击start_for_win.bat  

注意：  
windows系统下无法使用 stop reload status 等命令  
如果无法打开页面请尝试关闭服务器防火墙  

测试
=======
浏览器访问 http://服务器ip或域:55151,例如http://127.0.0.1:55151

 [更多请访问www.workerman.net](http://www.workerman.net/workerman-chat)

改动
=======
增加 config.php 配置文件

分离部署
=======
写好配置文件，在对应的机器上启动即可

docker分离部署
=======
例如：

172.172.0.10、172.172.0.11用于web、gateway

web和gateway的ip保持一致，各容器的端口不能相同，参考config.php.example

172.172.0.12用于register

172.172.0.13、172.172.0.14用于worker

1、register

$ docker run -p 1236:1236 -v ~/chat/:/root/chat --network=php_net --ip 172.172.0.12 -itd --name register php

2、worker

$ docker run -v ~/chat/:/root/chat --network=php_net --ip 172.172.0.13 -itd --name worker_1 php

$ docker run -v ~/chat/:/root/chat --network=php_net --ip 172.172.0.14 -itd --name worker_2 php

3、web、gateway

$ docker run -p 7272:7272 -p 55151:55151 -v ~/chat/:/root/chat --network=php_net --ip 172.172.0.10 -itd --name gateway_1 php

$ docker run -p 7273:7273 -p 55152:55152 -v ~/chat/:/root/chat --network=php_net --ip 172.172.0.11 -itd --name gateway_2 php

4、各容器分别执行php start.php start

5、分别访问宿主机的55151、55152端口，55151的客户端会连接7272端口，55152的客户端会连接7273端口

6、运行过程中，根据需要自由增减worker和web、gateway容器的数量，注意：web、gateway容器关闭时，部分客户端会下线
