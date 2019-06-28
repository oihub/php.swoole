<?php

/**
 * 输出打印.
 * @param mixed $var 变量.
 * @return void
 */
function dump($var)
{
    $body = (is_array($var) || is_object($var)) ? print_r($var, true) : $var;
    $prefix = date('Y-m-d H:i:s') . ' ';
    $prefix .= '[' . $_SERVER['REMOTE_ADDR'] . ']';
    if (isset(\Yii::$app->getResponse()->swooleResponse)) {
        $response = \Yii::$app->getResponse()->swooleResponse;
        $response->header('Content-Type', 'text/html;charset=utf-8');
        $response->end($body);
        throw new \Swoole\ExitException($body);
    } else {
        fwrite(\STDOUT, $prefix . ' ' . $body . PHP_EOL);
    }
}
