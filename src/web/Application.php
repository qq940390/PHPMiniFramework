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
     * @var string JSONP callback function
     */
    public $jsonCallback = '';


    /**
     * init
     */
    public function init()
    {
        $this->jsonCallback = $_GET['callback'];
    }

    /**
     * 处理 request
     * @param $request
     * @return mixed|Response|null
     */
    public function handleRequest($request)
    {
        $route = $request->resolve();
        $result = $this->runAction($route);

        if($result instanceof Response) {
            return $result;
        } else {
            $response = new Response();
            $response->data = $result;
            return $response;
        }
    }

}