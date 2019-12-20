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
    protected $config;

    public function __construct($cfg)
    {
        $this->config = $cfg;
    }

    public function run(){
        if(HP::$router == null) HP::$router = new \hp\route\Router();
        HP::$router->run();
    }

}