<?php

namespace oihub\swoole\component;

use Yii;

/**
 * Class Response.
 * 
 * @author sean <maoxfjob@163.com>
 */
class Response extends \yii\web\Response
{
    /**
     * {@inheritdoc}
     */
    public function send()
    {
        if ($this->isSent) {
            return;
        }
        $this->trigger(self::EVENT_BEFORE_SEND);
        $this->prepare();
        $this->trigger(self::EVENT_AFTER_PREPARE);
        // $this->sendHeaders();
        // $this->sendContent();
        $this->trigger(self::EVENT_AFTER_SEND);
        $this->isSent = true;
    }
}
