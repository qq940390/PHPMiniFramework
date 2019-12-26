<?php
/**
 * ErrorHandle.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\base;


class ErrorHandler
{

    /**
     * 注册错误处理回调
     */
    public function register() {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * 清除之前的输出
     */
    public function clearOutput()
    {
        // the following manual level counting is to deal with zlib.output_compression set to On
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }

    /**
     * 判断是否致命错误
     *
     * @param array $error error got from error_get_last()
     * @return bool if error is one of fatal type
     */
    public function isFatalError($error)
    {
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
    }

    /**
     * 错误处理回调函数
     * @param $type
     * @param $message
     * @param $file
     * @param $line
     */
    public function handleError($type, $message, $file, $line) {
        $this->clearOutput();
        echo "<b>Error In $file:</b> " , '<br/>',$line, ': ', $message;
        exit(1);
    }

    /**
     * 异常处理回调函数
     * @param $exception
     */
    public function handleException($exception) {
        echo "<b>Exception:</b> " , $exception->getMessage();
    }

    /**
     * 致命错误处理回调函数
     * 同时也可用于判断脚本是否执行完
     */
    public function handleFatalError() {
        $error = error_get_last();
        if($this->isFatalError($error)) {
            $this->clearOutput();
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        } else {
            print_r($error);
        }
    }
}