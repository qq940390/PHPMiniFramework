<?php
/**
 * Application.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\web;

class Application extends \pm\base\Application
{

    /**
     * 处理 request
     * @param \pm\web\Request $request
     * @throws \ReflectionException
     * @throws \pm\exception\UnknownClassException
     */
    public function handleRequest($request)
    {
        $route = $request->resolve();
        $this->runAction($route);
    }

    /**
     * 获取当前控制器
     * @return \pm\base\Controller
     */
    public function getController() {
        return $this->controller;
    }

}