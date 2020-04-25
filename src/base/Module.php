<?php
/**
 * Module.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\base;


use PM;

class Module extends Component
{
    /**
     * @var string 默认控制器
     */
    public $defaultController = 'index';


    /**
     * 执行控制器方法
     * @param $route
     * @throws \ReflectionException
     * @throws \pm\exception\UnknownClassException
     */
    public function runAction($route)
    {
        $parts = $this->createController($route);
        if (is_array($parts)) {
            list($controller, $actionID) = $parts;
            /* @var \pm\base\Controller $controller */
            PM::$app->controller = $controller;
            PM::$app->controller->runAction($actionID);
        }
    }

    /**
     * 创建控制器
     * @param $route
     * @return array|bool
     * @throws \ReflectionException
     * @throws \pm\exception\UnknownClassException
     */
    public function createController($route)
    {
        if ($route === '') {
            $route = $this->defaultController;
        }

        $route = trim($route, '/');

        if (strpos($route, '/') !== false) {
            list($id, $action) = explode('/', $route, 2);
        } else {
            $id = $route;
            $action = '';
        }
        //将 user-add 形式替换成 UserAdd 形式
        $className = preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
            return ucfirst($matches[1]);
        }, ucfirst($id));
        $className = 'app\\controller\\' . $className;
        $controller = new \ReflectionClass($className);
        if(!$controller) {
            throw new \pm\exception\UnknownClassException('Calling unknown classname: ' . $className);
            return false;
        }
        if($controller->isInstantiable()) {
            $controller = $controller->newInstance();
        }
        return [$controller, $action];
    }
}