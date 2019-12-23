<?php
/**
 * Application.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace wp\web;

use WP;

class Application extends \wp\base\Application
{

    /**
     * @var Controller the currently active controller instance
     */
    public $controller;


    /**
     * 处理 request
     * @param Request $request
     * @return mixed
     */
    public function handleRequest($request)
    {
        $route = $request->resolve();
        $result = $this->runAction($route);
        return $result;
    }

}