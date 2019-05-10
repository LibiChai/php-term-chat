## 纯PHP实现的终端聊天室

#### 截图
![截图](https://github.com/LibiChai/php-term-chat/blob/master/demo.png)

#### 说明

- 使用select模型实现。
- 支持群聊和私聊
- 纯试验项目，没有考虑各种异常边界问题

#### 使用方法
1. 启动：``` php chat.php```
2. 终端使用 nc 或者 telnet 连接，默认端口8889 ``` nc 127.0.0.1 8889 ```
3. 输入昵称即可登录
4. 默认群聊，私聊使用 @昵称[空格] 
