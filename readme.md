
## 项目概述

* 小程序名称：研友计划
* 小程序地址：

该项目为后端API相关实现，使用Laravel进行开发

## 功能如下

- 用户认证 —— 小程序授权，微信信息获取，学生身份验证；
- 研友匹配 —— 为用户匹配符合期待的研友；
- 打卡 —— 打卡相关排行榜及分享；

[产品文档](https://github.com/tangmingshuai/YanYou/blob/master/研友计划180511.pdf)

## 运行环境要求

- Nginx 1.8+
- PHP 7.1+
- Mysql 5.7+
- Redis 3.0+

## 开发环境部署/安装

本项目代码使用 PHP 框架 [Laravel 5.5](https://d.laravel-china.org/docs/5.5/) 开发，本地开发环境使用 [Laravel Homestead](https://d.laravel-china.org/docs/5.5/homestead)。

执行测试数据生成命令：php artisan db:seed --class=TargetInfoTableSeeder (已建立模型关联，每次生成十组用户数据，包括用户信息、基本信息、目标信息)

## 扩展包使用情况

| 扩展包 | 一句话描述 | 本项目应用场景 |
| --- | --- | --- |
| [Intervention/image](https://github.com/Intervention/image) | 图片处理功能库 | 用于图片裁切 |
| [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) | HTTP 请求套件 | 请求教务系统相关服务，用作爬虫处理  |
| [symfony/dom-crawler](https://github.com/symfony/dom-crawler) | 简单好用的html解析 | 对教务系统页面进行解析 |
| [predis/predis](https://github.com/nrk/predis.git) | Redis 官方首推的 PHP 客户端开发包 | 缓存驱动 Redis 基础扩展包 |
| [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) | 页面调试工具栏 (对 phpdebugbar 的封装) | 开发环境中的 DEBUG |
| [barryvdh/laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper)| Laravel IDE Helper | 辅助开发，完善IDE支持 |
| [mewebstudio/Purifier](https://github.com/mewebstudio/Purifier) | 用户提交的 Html 白名单过滤 | 帖子内容的 Html 安全过滤，防止 XSS 攻击 |
| [laravel/horizon](https://github.com/laravel/horizon) | 队列监控 | 队列监控命令与页面控制台 /horizon |
| [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) | JSON Web令牌认证 | 提供与前端较为安全的交互方式 |
| [dingo/api](https://github.com/dingo/api) | 轻松构建RESTful Api | 用于构建Api |
| [overtrue/laravel-wechat](https://github.com/overtrue/laravel-wechat) | 微信SDK for Laravel | 快速开发微信相关接口 |
| [fzaninotto/faker](https://github.com/fzaninotto/Faker) | 快速生成测试数据 | 模拟用户数据，测试匹配算法 | 

## 自定义 Artisan 命令
- [ ] 待添加
## 队列清单
- [ ] 待添加

