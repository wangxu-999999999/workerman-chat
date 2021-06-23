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
use \GatewayWorker\Register;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'config.php';

/**
 * @var array $registerHost
 */

$ip = gethostbyname(gethostname());

list($configIp, $configPort) = explode(':', $registerHost['host']);

if ($ip == $configIp) {

    $name = (isset($registerHost['name']) && $registerHost['name']) ? $registerHost['name'] : 'register';

    // register 服务必须是text协议
    $register = new Register("text://0.0.0.0:{$configPort}");
    // 日志
    $logPath = __DIR__ . "/Log/register_{$ip}.log";
    if (isset($registerHost['log_path']) && $registerHost['log_path']) {
        $logPath = $registerHost['log_path'];
    }
    $register::$stdoutFile = $logPath;
    // 名称
    $register->name = $name;
    // 如果不是在根目录启动，则运行runAll方法
    if(!defined('GLOBAL_START'))
    {
        Worker::runAll();
    }
}
