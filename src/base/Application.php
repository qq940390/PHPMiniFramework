<?php
/**
 * 应用基类
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace hp\base;

use HP;

abstract class Application extends Component
{
    /**
     * @var Controller the currently active controller instance
     */
    public $controller;

    /**
     * @var string 控制器命名空间前缀
     */
    public $controllerNamespace = 'app\\controller';

    /**
     * @var string 默认控制器
     */
    public $defaultRoute = 'index';


    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        HP::$app = $this;

        //注册错误处理
        (new ErrorHandler())->register();
    }

    /**
     * 执行并输出内容
     */
    public function run()
    {
        //调用路由组件，处理 request
        $response = $this->handleRequest(new \hp\route\Router());
        echo $response;
    }

    abstract public function handleRequest($request);

    /**
     * 执行控制器方法
     * @param $route
     * @param array $params
     * @return mixed
     */
    public function runAction($route)
    {
        $parts = $this->createController($route);
        if (is_array($parts)) {
            list($controller, $actionID) = $parts;
            $oldController = HP::$app->controller;
            HP::$app->controller = $controller;
            /* @var \hp\base\Controller $controller */
            $result = $controller->runAction($actionID);
            if ($oldController !== null) {
                HP::$app->controller = $oldController;
            }

            return $result;
        }
    }

    public function createController($route)
    {
        if ($route === '') {
            $route = $this->defaultRoute;
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
        $className = $this->controllerNamespace . '\\' . $className;
        try {
            $controller = new $className();
            return $controller === null ? false : [$controller, $action];
        } catch (\Exception $e) {
            throw new UnknownClassException('Calling unknown classname: ' . $className);
            return false;
        }
    }
}