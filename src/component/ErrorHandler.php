<?php

namespace oihub\swoole\component;

use Yii;
use yii\base\ExitException;
use yii\helpers\VarDumper;

/**
 * Class ErrorHandler.
 * 
 * @author sean <maoxfjob@163.com>
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * 异常状态.
     */
    const STATUS_ERROR_SERVER = 500;
    /**
     * 异常信息.
     */
    const ISV_ERROR_SERVER = 'server.server-exception.';

    /**
     * Handles uncaught PHP exceptions.
     *
     * This method is implemented as a PHP exception handler.
     *
     * @param \Exception $exception the exception that is not caught
     */
    public function handleException($exception)
    {
        if ($exception instanceof ExitException) {
            return;
        }

        $this->exception = $exception;

        // disable error capturing to avoid recursive errors while handling exceptions
        $this->unregister();

        // set preventive HTTP status code to 500 in case error handling somehow fails and headers are sent
        // HTTP exceptions will override this value in renderException()
        if (PHP_SAPI !== 'cli') {
            http_response_code(500);
        }

        try {
            $this->logException($exception);
            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            $this->renderException($exception);
            if (!YII_ENV_TEST) {
                \Yii::getLogger()->flush(true);
                if (defined('HHVM_VERSION')) {
                    flush();
                }
            }
        } catch (\Exception $e) {
            // an other exception could be thrown while displaying the exception
            $this->handleFallbackExceptionMessage($e, $exception);
        } catch (\Throwable $e) {
            // additional check for \Throwable introduced in PHP 7
            $this->handleFallbackExceptionMessage($e, $exception);
        }

        $this->exception = null;
    }

    /**
     * Handles exception thrown during exception processing in [[handleException()]].
     * @param \Exception|\Throwable $exception Exception that was thrown during main exception processing.
     * @param \Exception $previousException Main exception processed in [[handleException()]].
     * @since 2.0.11
     */
    protected function handleFallbackExceptionMessage($exception, $previousException)
    {
        $msg = "An Error occurred while handling another error:\n";
        $msg .= (string) $exception;
        $msg .= "\nPrevious exception:\n";
        $msg .= (string) $previousException;
        if (YII_DEBUG) {
            if (PHP_SAPI === 'cli') {
                echo $msg . "\n";
            } else {
                echo '<pre>' . htmlspecialchars($msg, ENT_QUOTES, Yii::$app->charset) . '</pre>';
            }
        } else {
            echo 'An internal server error occurred.';
        }
        $msg .= "\n\$_SERVER = " . VarDumper::export($_SERVER);
        error_log($msg);
        if (defined('HHVM_VERSION')) {
            flush();
        }
    }

    /**
     * Converts an exception into an array.
     * @param \Exception|\Error $exception the exception being converted.
     * @return array the array representation of the exception.
     */
    protected function convertExceptionToArray($exception)
    {
        if (YII_ENV_DEV || $exception instanceof \yii\web\HttpException) {
            $array = [
                'name' => $exception->getName(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ];
        } else {
            $array = [
                'name' => 'Exception',
                'message' => static::ISV_ERROR_SERVER,
                'code' => static::STATUS_ERROR_SERVER,
            ];
        }

        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }

        if (YII_DEBUG) {
            $array['type'] = get_class($exception);
            $array['file'] = $exception->getFile();
            $array['line'] = $exception->getLine();
            $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
            if ($exception instanceof \yii\db\Exception) {
                $array['error-info'] = $exception->errorInfo;
            }
        }

        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->convertExceptionToArray($prev);
        }
        return $array;
    }
}
