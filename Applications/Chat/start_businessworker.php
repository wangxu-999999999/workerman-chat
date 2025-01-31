<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'config.php';

/**
 * @var array $workerHosts
 * @var array $registerHost
 */

$ip = gethostbyname(gethostname());

if (array_key_exists($ip, $workerHosts)) {

    $count = isset($workerHosts[$ip]['count']) ? $workerHosts[$ip]['count'] : 1;
    $name = (isset($workerHosts[$ip]['name']) && $workerHosts[$ip]['name']) ? $workerHosts[$ip]['name'] : 'worker';

    // bussinessWorker 进程
    $worker = new BusinessWorker();
    // 日志
    $logPath = __DIR__ . "/Log/worker_{$ip}.log";
    if (isset($workerHosts[$ip]['log_path']) && $workerHosts[$ip]['log_path']) {
        $logPath = $workerHosts[$ip]['log_path'];
    }
    $worker::$stdoutFile = $logPath;
    // worker名称
    $worker->name = $name;
    // bussinessWorker进程数量
    $worker->count = $count;
    // 服务注册地址
    $worker->registerAddress = $registerHost['host'];

    // 如果不是在根目录启动，则运行runAll方法
    if(!defined('GLOBAL_START'))
    {
        Worker::runAll();
    }
}
