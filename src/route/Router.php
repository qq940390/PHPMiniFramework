<?php
/**
 * Router.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace hp\route;

class Router
{
    public static $route = '';
    public static $controller = '';
    public static $action = '';

    public function __construct(){

    }

    public function run(){
        //访问的是根路径 /
        if(dirname($_SERVER['SCRIPT_NAME']) == $_SERVER['REQUEST_URI'] || $_SERVER['SCRIPT_NAME'] == $_SERVER['REQUEST_URI']) {
            static::$route = '/';
        } else {
            $queryRoute = $this->getQueryRoute();
            $pathMatch = explode('?', $_SERVER['REQUEST_URI']);
            $pathRoute = strtr($pathMatch[0], [$_SERVER['SCRIPT_NAME'] => '', dirname($_SERVER['SCRIPT_NAME']) => '']);
            static::$route = strlen($pathRoute) > 2 ? $pathRoute : $queryRoute;
        }

        $route = explode('/', ltrim(static::$route, '/'));
        static::$controller = $route[0] ? $route[0] : 'index';
        static::$action = 'action'.ucfirst($route[1] ? $route[1] : 'index');

        $className = '\\app\\controller\\'.static::$controller;
        $obj = new $className();
        if(!method_exists($obj, static::$action)) {
            exit('Method Not Exists');
        }

        echo call_user_func_array(array($obj, static::$action), []);
    }

    public function getQueryRoute() {
        if(!$_SERVER['QUERY_STRING']) return false;
        parse_str($_SERVER['QUERY_STRING'], $param);
        if(isset($param['r'])) {
            return '/'.ltrim($param['r'], '/');
        }
        return false;
    }
}