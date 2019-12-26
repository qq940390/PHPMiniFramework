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
     * 执行并输出内容
     */
    public function run()
    {
        //调用路由组件，处理 request
        /* @var $response \pm\web\Response */
        $response = $this->handleRequest(new \pm\web\Request());

        $response->send();
    }

    /**
     * 处理 request
     * @param $request
     * @return mixed|Response|null
     */
    public function handleRequest($request)
    {
        /* @var $request \pm\web\Request */
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