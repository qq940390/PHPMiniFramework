<?php
/**
 * 应用基类
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\base;

use PM;

/**
 * Class Application
 *
 * @package pm\base
 */
abstract class Application extends Module
{
    /**
     * @var Controller the currently active controller instance
     */
    public $controller;


    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        PM::$app = $this;
        PM::$db = \pm\helper\DBHelper::getInstance($config['db']);

        //注册错误处理
        (new ErrorHandler())->register();
    }

    /**
     * 处理 request
     * @param $request
     * @return mixed
     */
    abstract public function handleRequest($request);

}