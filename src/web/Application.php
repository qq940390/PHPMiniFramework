<?php
/**
 * Application.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace hp\web;

use HP;

class Application extends \hp\base\Application
{

    /**
     * @var Controller the currently active controller instance
     */
    public $controller;


    /**
     * 处理 request
     * @param $router \hp\route\Router
     * @return mixed
     */
    public function handleRequest($router)
    {
        $route = $router->resolve();
        $result = $this->runAction($route);
        return $result;
    }

}