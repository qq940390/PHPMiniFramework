<?php
/**
 * DbException.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\exception;

/**
 * DB异常处理类
 *
 * @copyright [JWP] wujinhai
 * @link http://wujinhai.cn/
 * @author wujinhai<940390@qq.com>
 * @package JWP\Helpers
 * @subpackage JDB
 * @version 1.0
 */
class DbException extends Exception{

    /**
     * @var string SQL语句
     */
    public $sql;



    /**
     * 构造函数
     *
     * @param string $message
     * @param integer $code
     * @param string $sql
     * @return void
     */
    public function __construct($message, $code = 0, $sql = '') {
        $this->sql = $sql;
        parent::__construct($message, $code);
    }


    /**
     * 获取SQL语句
     *
     * @return string
     */
    public function getSql() {
        return $this->sql;
    }

}
