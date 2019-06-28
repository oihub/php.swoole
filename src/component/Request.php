<?php

namespace oihub\swoole\component;

use Yii;

/**
 * Class Request.
 * 
 * @author sean <maoxfjob@163.com>
 */
class Request extends \yii\web\Request
{
    /**
     * @var \swoole_http_request Swoole 请求.
     */
    public $swooleRequest;
    /**
     * @var \swoole_websocket_frame 数据帧信息.
     */
    public $websocketFrame;
    /**
     * @var int 客户端的文件描述符.
     */
    public $fd;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setScriptUrl($_SERVER['SCRIPT_NAME']);
    }
}
