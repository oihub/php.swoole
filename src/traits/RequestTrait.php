<?php

namespace oihub\swoole\traits;

use Yii;

/**
 * Trait RequestTrait.
 * 
 * @author sean <maoxfjob@163.com>
 */
trait RequestTrait
{
    /**
     * 请求处理.
     * 
     * @param \swoole_http_request $request 请求.
     * @return void
     */
    protected function parse(\swoole_http_request $request): void
    {
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_FILES = $request->files ?? [];
        $_COOKIE = $request->cookie ?? [];

        $server = $request->server ?? [];
        $header = $request->header ?? [];
        foreach ($server as $key => $value) {
            $_SERVER[strtoupper($key)] = $value;
            unset($server[$key]);
        }
        foreach ($header as $key => $value) {
            $_SERVER['HTTP_' . strtoupper($key)] = $value;
        }
        $_SERVER['SERVER_SOFTWARE'] = 'swoole/' . SWOOLE_VERSION;
    }
}
