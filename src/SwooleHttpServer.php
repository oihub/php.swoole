<?php

namespace oihub\swoole;

use Yii;

/**
 * Class SwooleHttpServer.
 * 
 * @author sean <maoxfjob@163.com>
 */
class SwooleHttpServer extends \oihub\swoole\SwooleServer
{
    /**
     * @var \swoole_http_server swoole 对象.
     */
    public $server;
    /**
     * @var callback 回调函数.
     */
    public $app;

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
        $this->server = new \swoole_http_server($host, $port, $mode, $socketType);
        $this->server->set($config);
        $this->server->on('request', [$this, 'onRequest']);
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
     * 请求事件.
     * 
     * @param \swoole_http_request $request 请求.
     * @param \swoole_http_response $response 响应.
     * @return void
     */
    public function onRequest($request, $response)
    {
        call_user_func_array($this->app, [$request]);
    }
}
