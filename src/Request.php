<?php

namespace oihub\swoole;

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
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setScriptUrl($_SERVER['SCRIPT_NAME']);
    }
}
