<?php

namespace oihub\swoole\websocket;

class Response
{
    /**
     * 成功.
     */
    const STATUS_SUCCESS = 200;
    /**
     * 失败.
     */
    const STATUS_ERROR = 500;

    /**
     * @var array 客户端的文件描述符.
     */
    public $fds;
    /**
     * @var string 消息.
     */
    public $message;
    /**
     * @var int 状态.
     */
    public $status = self::STATUS_SUCCESS;
}
