<?php

namespace oihub\swoole;

/**
 * Class SwooleWebSocketServer.
 * 
 * @author sean <maoxfjob@163.com>
 */
class SwooleWebSocketServer
{
    /**
     * @var \swoole_websocket_server swoole 对象.
     */
    public $server;
    /**
     * @var callback 握手事件.
     */
    public $onOpen;
    /**
     * @var callback 消息事件.
     */
    public $onMessage;
    /**
     * @var callback 关闭事件.
     */
    public $onClose;

    /**
     * 构造函数.
     * 
     * @param string $host 监听地址.
     * @param int $port 监听端口.
     * @param int $mode 运行模式.
     * @param int $socketType Socket 类型.
     * @param array $config 配置.
     * @return void
     */
    public function __construct(
        string $host,
        int $port,
        int $mode,
        int $socketType,
        array $config = []
    ) {
        $this->server = new \swoole_websocket_server($host, $port, $mode, $socketType);
        $this->server->set($config);
        $this->server->on('open', function ($server, $request) {
            call_user_func($this->onOpen, $server, $request);
        });
        $this->server->on('message', function ($server, $frame) {
            call_user_func($this->onMessage, $server, $frame);
            echo $frame->data . PHP_EOL; // 输出调试信息.
        });
        $this->server->on('close', function ($server, $fd) {
            call_user_func($this->onClose, $server, $fd);
        });
        $this->server->on('task', function ($server, $task_id, $src_worker_id, $data) { });
        $this->server->on('finish', function ($server, $task_id, $data) { });
    }

    /**
     * 启动服务.
     * 
     * @return void
     */
    public function run(): void
    {
        $this->server->start();
    }
}
