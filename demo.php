<?php
/**
 * Created by PhpStorm.
 * User: foolkaka
 * Date: 16-7-5
 * Time: 下午3:32
 */


define('DEBUG', 'on');
require_once __DIR__.'/vendor/autoload.php';

$cloud = Swoole\Client\SOA::getInstance();
$cloud->setEncodeType(false, true);
$cloud->putEnv('app', 'test');
$cloud->putEnv('appKey', 'test1234');
$cloud->setServers(array('10.17.112.138:8888'));
$ret2 = $cloud->task("Api\\Test::test1", ["hello", 'world']);
$ret3 = $cloud->task("Api\\Test::hello");
var_dump($ret2->getResult(), $ret3->getResult());
