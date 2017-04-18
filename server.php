<?php
$app = require __DIR__.'/app.php';

//设置PID文件的存储路径
Swoole\Network\Server::setPidFile(storage_path('logs') . '/soaphp_server.pid');
/**
 * 显示Usage界面
 * php app_server.php start|stop|reload
 */
Swoole\Network\Server::start(function ()
{
    $AppSvr = new Swoole\Protocol\SOAServer;

    $server = Swoole\Network\Server::autoCreate('127.0.0.1', 8888);
    $server->setProtocol($AppSvr);
    //$server->daemonize(); //作为守护进程
    $server->run(
        array(
            'worker_num' => 8,
            'max_request' => 5000,
            'dispatch_mode' => 3,
            'open_length_check' => 1,
            'package_max_length' => $AppSvr->packet_maxlen,
            'package_length_type' => 'N',
            'package_body_offset' => \Swoole\Protocol\SOAServer::HEADER_SIZE,
            'package_length_offset' => 0,
        )
    );
});

