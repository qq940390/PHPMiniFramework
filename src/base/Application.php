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
     * @var \pm\web\Controller 实例当前的控制器
     */
    public $controller;

    /**
     * @var string 编码
     */
    public $charset = 'UTF-8';


    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        PM::$app = $this;

        //注册错误处理
        (new ErrorHandler())->register();

        PM::$db = PM::createObject($config['db']);

        parent::__construct($config);
    }

    /**
     * 执行并输出内容
     */
    public function run()
    {
        //调用路由组件，处理 request
        $this->handleRequest(new \pm\web\Request());
    }

    /**
     * 处理 request
     * @param $request
     */
    abstract public function handleRequest($request);

}