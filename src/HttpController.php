<?php

namespace oihub\swoole;

use Yii;
use yii\web\Application;
use yii\helpers\FileHelper;

/**
 * Class HttpController.
 * 
 * @author sean <maoxfjob@163.com>
 */
class HttpController extends \oihub\swoole\Controller
{
    /**
     * @var int 运行模式.
     */
    public $mode = SWOOLE_PROCESS;
    /**
     * @var int Socket 类型.
     */
    public $socketType = SWOOLE_TCP;
    /**
     * @var string 项目根路径.
     */
    public $rootDir = '';
    /**
     * @var string 访问目录.
     */
    public $web = '';

    /**
     * {@inheritdoc}
     */
    public function actionStart()
    {
        if ($this->getPid() !== false) {
            $this->stderr('server already started');
            exit(1);
        }

        $pidDir = dirname($this->config['pid_file']);
        file_exists($pidDir) || FileHelper::createDirectory($pidDir);

        $logDir = dirname($this->config['log_file']);
        file_exists($logDir) || FileHelper::createDirectory($logDir);

        require($this->rootDir . '/vendor/autoload.php');
        require($this->rootDir . '/config/bootstrap.php');
        $config = require($this->rootDir . '/config/main.php');

        $this->config = array_merge([
            'document_root' => $this->rootDir . DIRECTORY_SEPARATOR . $this->web,
            'enable_static_handler' => true,
        ], $this->config);

        $server = new SwooleHttpServer(
            $this->host,
            $this->port,
            $this->mode,
            $this->socketType,
            $this->config
        );

        $server->app = function ($request) use ($config) {
            $begin = microtime(true);

            $components = &$config['components'];
            $components['request']['class'] = Request::className();

            try {
                (new Application($config))->run();
            } catch (\Swoole\ExitException $e) {
                $this->stderr($e->getMessage() . PHP_EOL);
            } catch (\Exception $e) {
                Yii::$app->getErrorHandler()->handleException($e);
            }
            return Yii::$app->response;
        };

        $message = "server is running, listening {$this->host}:{$this->port}";
        $this->stdout($message . PHP_EOL);
        $server->run();
    }
}
