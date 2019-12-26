<?php
/**
 * This source file is supported by JWP.
 *
 * @copyright [JWP] wujinhai
 * @link http://wujinhai.cn/
 * @author wujinhai<940390@qq.com>
 * @package JWP
 * @version 1.0
 */

namespace pm\helper;

/**
 * Class DBHelper
 *
 * @property $instance \pm\db\Database
 * @package pm\helper
 */
class DBHelper {

    protected $instance = null;

    function __construct($config)
    {
        if($this->instance == null) {
            $this->instance = new \pm\db\Database([
                'host' => $config['host'],  // 服务器地址
                'port' => $config['port'],  // 端口
                'username' => $config['username'],   // 用户
                'password' => $config['password'], // 密码
                'charset' => $config['charset'] ? $config['charset'] : 'utf8',    // 字符集
                'pconnect' => isset($config['pconnect']) ? $config['pconnect'] : 0,  // 是否持续连接
                'dbname' => $config['dbname'], // 数据库
                'prefix' => $config['prefix'], // 表名前缀
                'debug' => isset($config['debug']) ? $config['debug'] : false, //是否是Debug模式
            ]);
        }

        return $this->instance;
    }

}