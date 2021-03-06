<?php
/**
 * Request.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\web;

use PM;

/**
 * Class Request
 *
 * @package pm\web
 */
class Request extends \pm\base\Request
{

    /**
     * 处理访问链接，返回路由字符串
     * @return bool|string
     */
    public function resolve()
    {
        //访问的是根路径 /
        if(dirname($_SERVER['SCRIPT_NAME']) == $_SERVER['REQUEST_URI'] || $_SERVER['SCRIPT_NAME'] == $_SERVER['REQUEST_URI']) {
            $route = '/';
        } else {
            $queryRoute = $this->getQueryRoute();
            $pathMatch = explode('?', $_SERVER['REQUEST_URI']);
            $pathRoute = strtr($pathMatch[0], [$_SERVER['SCRIPT_NAME'] => '', dirname($_SERVER['SCRIPT_NAME']) => '']);
            $route = strlen($pathRoute) > 2 ? $pathRoute : $queryRoute;
        }
        $route = $route ? $route : PM::$app->defaultController;
        return $route;
    }

    /**
     * 获取get形式的路由字符串
     * @return bool|string
     */
    public function getQueryRoute()
    {
        if(!$_SERVER['QUERY_STRING']) return false;
        parse_str($_SERVER['QUERY_STRING'], $param);
        if(isset($param['r'])) {
            return '/'.ltrim($param['r'], '/');
        }
        return false;
    }

}