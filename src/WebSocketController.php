<?php

namespace oihub\swoole;

use Yii;
use yii\helpers\FileHelper;

/**
 * Class WebSocketController.
 * 
 * @author sean <maoxfjob@163.com>
 */
class WebSocketController extends \oihub\swoole\Controller
{
    /**
     * @var int 运行模式.
     */
    public $mode = SWOOLE_BASE;
    /**
     * @var int Socket 类型.
     */
    public $socketType = SWOOLE_SOCK_TCP | SWOOLE_SSL;;
    /**
     * @var callback 握手事件.
     */
    public $onOpen;
    /**
     * @var callback 消息事件.
     */
    public $onMessage;
    /**
     * @var callback 开始事件.
     */
    public $onStart;
    /**
     * @var callback 关闭事件.
     */
    public $onClose;

    /**
     * {@inheritdoc}
     */
    public function actionStart()
    {
        if ($this->getPid() !== false) {
            $this->stderr('server already started');
            exit(1);
        }

        $pidDir = dirname($this->config['pid_file']);
        file_exists($pidDir) || FileHelper::createDirectory($pidDir);

        $logDir = dirname($this->config['log_file']);
        file_exists($logDir) || FileHelper::createDirectory($logDir);

        $this->config = array_merge([
            'daemonize' => false, // 守护进程执行.
            'task_worker_num' => 4, // task 进程的数量.
            'ssl_cert_file' => '',
            'ssl_key_file' => '',
            'pid_file' => '',
            'buffer_output_size' => 2 * 1024 * 1024, // 配置发送输出缓存区内存尺寸.
            'heartbeat_check_interval' => 60, // 心跳检测秒数.
            'heartbeat_idle_time' => 600, // 检查最近一次发送数据的时间和当前时间的差，大于则强行关闭.
        ], $this->config);

        $server = new SwooleWebSocketServer(
            $this->host,
            $this->port,
            $this->mode,
            $this->socketType,
            $this->config
        );

        $server->onOpen = $this->onOpen;
        $server->onMessage = $this->onMessage;
        $server->onStart = $this->onStart;
        $server->onClose = $this->onClose;

        $message = "server is running, listening {$this->host}:{$this->port}";
        $this->stdout($message . PHP_EOL);
        $server->run();
    }
}
