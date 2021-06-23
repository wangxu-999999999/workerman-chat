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
use Workerman\Worker;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Connection\TcpConnection;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'config.php';

/**
 * @var array $webHosts
 * @var array $gatewayHosts
 */

$ip = gethostbyname(gethostname());

if (array_key_exists($ip, $webHosts)) {

    $port = $webHosts[$ip]['port'];
    $count = isset($webHosts[$ip]['count']) ? $webHosts[$ip]['count'] : 1;
    $name = (isset($webHosts[$ip]['name']) && $webHosts[$ip]['name']) ? $webHosts[$ip]['name'] : 'web';

    // WebServer
    $web = new Worker("http://0.0.0.0:{$port}");
    // 日志
    $logPath = __DIR__ . "/Log/web_{$ip}.log";
    if (isset($webHosts[$ip]['log_path']) && $webHosts[$ip]['log_path']) {
        $logPath = $webHosts[$ip]['log_path'];
    }
    $web::$stdoutFile = $logPath;
    // 名称
    $web->name = $name;
    // WebServer进程数量
    $web->count = $count;

    define('WEBROOT', __DIR__ . DIRECTORY_SEPARATOR .  'Web');

    $web->onMessage = function (TcpConnection $connection, Request $request) use ($gatewayHosts, $ip) {
        $_GET = $request->get();
        $path = $request->path();
        if ($path === '/') {
            $html = exec_php_file(WEBROOT.'/index.php');
            $gatewayPort = $gatewayHosts[$ip]['port'];
            $html = str_replace('$gatewayPort', $gatewayPort, $html);
            $connection->send($html);
            return;
        }
        $file = realpath(WEBROOT. $path);
        if (false === $file) {
            $connection->send(new Response(404, array(), '<h3>404 Not Found</h3>'));
            return;
        }
        // Security check! Very important!!!
        if (strpos($file, WEBROOT) !== 0) {
            $connection->send(new Response(400));
            return;
        }
        if (\pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $connection->send(exec_php_file($file));
            return;
        }

        $if_modified_since = $request->header('if-modified-since');
        if (!empty($if_modified_since)) {
            // Check 304.
            $info = \stat($file);
            $modified_time = $info ? \date('D, d M Y H:i:s', $info['mtime']) . ' ' . \date_default_timezone_get() : '';
            if ($modified_time === $if_modified_since) {
                $connection->send(new Response(304));
                return;
            }
        }
        $connection->send((new Response())->withFile($file));
    };

    // 如果不是在根目录启动，则运行runAll方法
    if(!defined('GLOBAL_START'))
    {
        Worker::runAll();
    }
}

function exec_php_file($file) {
    \ob_start();
    // Try to include php file.
    try {
        include $file;
    } catch (\Exception $e) {
        echo $e;
    }
    return \ob_get_clean();
}
