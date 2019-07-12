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
        $this->server->on('open', [$this, 'onOpen']);
        $this->server->on('message', [$this, 'onMessage']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);
        $this->server->on('close', [$this, 'onClose']);
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

    /**
     * 握手事件.
     * 
     * @param \swoole_websocket_server $server swoole 对象.
     * @param \swoole_http_request $request 请求.
     * @return void
     */
    public function onOpen($server, $request)
    {
        $response = call_user_func($this->onOpen, $request);
        $this->sendMessage($server, $response);
        $response->status === Response::STATUS_SUCCESS or $server->close($request->fd);
    }

    /**
     * 消息事件.
     * 
     * @param \swoole_websocket_server $server swoole 对象.
     * @param \swoole_websocket_frame $frame 数据帧.
     * @return void
     */
    public function onMessage($server, $frame)
    {
        $data = call_user_func($this->onMessage, $frame);
        $this->sendMessage($server, $data);
        echo $frame->data . PHP_EOL; // 输出调试信息.
    }

    /**
     * 关闭事件.
     * 
     * @param \swoole_server $server swoole 对象.
     * @param int $fd 客户端的文件描述符.
     * @return void
     */
    public function onClose($server, $fd)
    {
        call_user_func($this->onClose, $fd);
    }

    /**
     * 异步任务.
     * 
     * @param \swoole_server $server swoole 对象.
     * @param int $task_id 任务 ID.
     * @param int $src_worker_id 进程 ID.
     * @param mixed $data 任务内容.
     * @return void
     */
    public function onTask($server, $task_id, $src_worker_id, $data)
    { }

    /**
     * 异步任务结果.
     *
     * @param \swoole_server $server swoole 对象.
     * @param int $task_id 任务 ID.
     * @param mixed $data 结果内容.
     * @return void
     */
    public function onFinish($server, int $task_id, $data)
    { }

    /**
     * 发送消息.
     *
     * @param \swoole_websocket_server  $server swoole 对象.
     * @param Response $response 响应数据.
     * @return void
     */
    protected function sendMessage($server, $response)
    {
        array_map(function ($fd) use ($server, $response) {
            $server->push($fd, $response->message);
        }, $response->fds);
    }
}
