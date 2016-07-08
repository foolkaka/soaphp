# soaphp
SOA这个可以很多种叫法，常见的RPC，远程方法调用，中间件平台等等。

如果说在前端http json api很重要，那么SOA在后端服务方面则非常重要。

本系统基于lumen框架和swoole扩展，提供了一个高性能SOA服务器框架，并且提供了一套可并行调用服务器端的Client实现。

# Swoole扩展
PHP语言的高性能网络通信框架，提供了PHP语言的异步多线程服务器，异步TCP/UDP网络客户端，异步MySQL，数据库连接池，AsyncTask，消息队列，毫秒定时器，异步文件读写，异步DNS查询。

Swoole可以广泛应用于互联网、移动通信、企业软件、网络游戏、物联网、车联网、智能家庭等领域。 使用PHP+Swoole作为网络通信框架，可以使企业IT研发团队的效率大大提升，更加专注于开发创新产品。

Swoole底层内置了异步非阻塞、多线程的网络IO服务器。PHP程序员仅需处理事件回调即可，无需关心底层。与Nginx/Tornado/Node.js等全异步的框架不同，Swoole既支持全异步，也支持同步。

# Lumen
Lumen 是一个由 Laravel 组件搭建而成的微框架, 由 Laravel 官方维护. Lumen 为速度而生, 是当前最快的 PHP 框架之一, 甚至比类似的微框架 Silex 速度还要快.

Lumen 比其他微框架的优点是, 构建在 Laravel 之上, 使其具备 Laravel 强大的功能, 如 路由, 依赖注入, Eloquent ORM, 数据库迁移管理, 队列和计划任务等.

Laravel 本来就是一个功能齐全, 速度飞快的框架, 但是 Lumen 因为去除了很多 Laravel 的配置和可自定义的选项, 速度越加飞快, 毫秒必争.

飞快的速度, 再加上 Laravel 非常方便的功能, 使用 Lumen 开发应用会是非常愉悦的体验.

# 安装
pecl install swoole

composer require foolkaka/soaphp

composer update

# demo
start server

php server.php start

run demo

php demo.php

# Help | Bug
https://github.com/foolkaka/soaphp/issues

微信/QQ: 786455

Blog: https://doyouplay.net
