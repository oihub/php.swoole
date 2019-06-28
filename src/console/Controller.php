<?php

namespace oihub\swoole\console;

use Yii;

/**
 * Class Controller.
 * 
 * @author sean <maoxfjob@163.com>
 */
abstract class Controller extends \yii\console\Controller
{
    /**
     * @var string 监听地址.
     */
    public $host = '0.0.0.0';
    /**
     * @var int 监听端口.
     */
    public $port = 9999;
    /**
     * @var int 运行模式.
     */
    public $mode;
    /**
     * @var int Socket 类型.
     */
    public $socketType;
    /**
     * @var array 配置.
     */
    public $config = [];

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        YII_DEBUG and include(dirname(__DIR__) . '/component/Debug.php');
        return parent::beforeAction($action);
    }

    /**
     * 启动.
     * @return mixed
     */
    abstract public function actionStart();

    /**
     * 终止.
     * @return void
     */
    public function actionStop()
    {
        $this->sendSignal(SIGTERM);
        $message = "server is stopped, stop listening {$this->host}:{$this->port}";
        $this->stdout($message . PHP_EOL);
    }

    /**
     * 重启.
     * @return void
     */
    public function actionRestart()
    {
        $this->sendSignal(SIGTERM);
        $time = 0;
        while (posix_getpgid($this->getPid()) && $time <= 10) {
            usleep(100000);
            $time++;
        }
        if ($time > 100) {
            $this->stderr('server stopped timeout' . PHP_EOL);
            exit(1);
        }
        if ($this->getPid() === false) {
            $this->stdout('server is stopped success' . PHP_EOL);
        } else {
            $this->stderr('server stopped error, please handle kill process' . PHP_EOL);
        }
        $this->actionStart();
    }

    /**
     * 重启.
     * @return void
     */
    public function actionReload()
    {
        $this->actionRestart();
    }

    /**
     * 重启 task_worke.
     * @return void
     */
    public function actioReloadTask()
    {
        $this->sendSignal(SIGUSR2);
    }

    /**
     * 标准输出.
     * @param string $string 字符串.
     * @return void
     */
    public function stdout($string)
    {
        $prefix = date('Y-m-d H:i:s') . ' ';
        $prefix .= '[' . $_SERVER['REMOTE_ADDR'] . ']';
        parent::stdout($prefix . ' ' . $string);
    }

    /**
     * 错误输出.
     * @param string $string 字符串.
     * @return void
     */
    public function stderr($string)
    {
        $prefix = date('Y-m-d H:i:s') . ' ';
        $prefix .= '[' . $_SERVER['REMOTE_ADDR'] . ']';
        parent::stderr($prefix . ' ' . $string);
    }

    /**
     * 发送信号.
     * @param mixed $sig 信号.
     * @return void
     */
    protected function sendSignal($sig)
    {
        if ($pid = $this->getPid()) {
            posix_kill($pid, $sig);
        } else {
            $this->stdout('server is not running!' . PHP_EOL);
            exit(1);
        }
    }

    /**
     * 得到进程 ID.
     * @return mixed
     */
    protected function getPid()
    {
        $pid_file = $this->config['pid_file'];
        if (file_exists($pid_file)) {
            $pid = file_get_contents($pid_file);
            if (posix_getpgid($pid)) {
                return $pid;
            } else {
                unlink($pid_file);
            }
        }
        return false;
    }
}
