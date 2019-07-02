<?php

namespace oihub\swoole\server;

use Yii;
use oihub\swoole\traits\RequestTrait;

/**
 * Class SwooleHttpServer.
 * 
 * @author sean <maoxfjob@163.com>
 */
class SwooleHttpServer
{
    use RequestTrait;
    
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
        // $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
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
    public function onRequest(
        \swoole_http_request $request,
        \swoole_http_response $response
    ) {
        // 拦截请求.
        // $this->reject($request, $response);
        // 请求处理.
        $this->requestHandle($request);
        // 回调程序.
        $appResponse = call_user_func_array($this->app, [$request]);
        // 响应请求.
        $this->resolve($appResponse, $response);
    }

    /**
     * 拦截请求.
     * 
     * @param \swoole_http_request $request 请求.
     * @param \swoole_http_response $response 响应.
     * @return void
     */
    protected function reject(
        \swoole_http_request $request,
        \swoole_http_response $response
    ): void {
        $uri = $request->server['request_uri'];
        $response->status(200);
        $response->end('');
    }

    /**
     * 响应请求.
     * 
     * @param object $appResponse 程序响应结果.
     * @param \swoole_http_response $response 响应.
     * @return void
     */
    protected function resolve(
        object $appResponse,
        \swoole_http_response $response
    ): void {
        $statusCode = $appResponse->getStatusCode();
        $response->status($statusCode);

        $headers = $appResponse->getHeaders();
        foreach ($headers as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $response->header($name, end($values));
        }

        $cookies = $appResponse->getCookies();
        foreach ($cookies as $cookie) {
            $response->cookie(
                $cookie->name,
                $cookie->value,
                $cookie->expire,
                $cookie->path,
                $cookie->domain,
                $cookie->secure,
                $cookie->httpOnly
            );
        }

        if ($appResponse->stream === null) {
            $response->end($appResponse->content);
            return;
        }

        set_time_limit(0); // Reset time limit for big files.
        $chunkSize = 8 * 1024 * 1024; // 8MB per chunk.

        if (is_array($appResponse->stream)) {
            list($handle, $begin, $end) = $appResponse->stream;
            fseek($handle, $begin);
            while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
                $pos + $chunkSize > $end and $chunkSize = $end - $pos + 1;
                $response->write(fread($handle, $chunkSize));
                flush();
            }
            fclose($handle);
        } else {
            while (!feof($appResponse->stream)) {
                $response->write(fread($appResponse->stream, $chunkSize));
                flush();
            }
            fclose($appResponse->stream);
        }
        $response->end(null);
    }
}
