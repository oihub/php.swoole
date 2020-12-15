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
     * @var callback 开始事件.
     */
    public $onStart;
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
        $log = '';
        $log .= '[监听地址: ' . $host . '] ';
        $log .= '[监听端口: ' . $port . '] ';
        $log .= '[运行模式: ' . $mode . '] ';
        $log .= '[运行类型: ' . $socketType . '] ';
        $this->echo('', '服务开启', $log);

        $this->server = new \swoole_websocket_server($host, $port, $mode, $socketType);
        $this->server->set($config);
        $this->server->on('open', function ($server, $request) {
            $this->echo($request->fd, '连接', json_encode($request->server));
            call_user_func($this->onOpen, $server, $request);
        });
        $this->server->on('message', function ($server, $frame) {
            if ($frame->data != 'ping') {
                $this->echo($frame->fd, '接收消息', $frame->data);
            }
            call_user_func($this->onMessage, $server, $frame);
        });
        $this->server->on('WorkerStart', function ($server, $worker_id) {
            call_user_func($this->onStart, $server, $worker_id);
        });
        $this->server->on('close', function ($server, $fd) {
            $this->echo($fd, '关闭');
            call_user_func($this->onClose, $server, $fd);
        });
        $this->server->on('task', function ($server, $task_id, $src_worker_id, $data) {
        });
        $this->server->on('finish', function ($server, $task_id, $data) {
        });
    }

    public function echo($fd, $type, $message = '')
    {
        $data[] = date('H:i:s');
        $data[] = $fd;
        $data[] = $type;
        $data[] = $message;
        echo join(' ', $data) . PHP_EOL;
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
