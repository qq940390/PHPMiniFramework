<?php
/**
 * 基控制器类
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace wp\base;

class Controller extends Component
{

    /**
     * @var string 默认action
     */
    public $defaultAction = 'index';


    /**
     * 执行 action
     * @param string $actionID
     * @param array $params
     * @return mixed
     */
    public function runAction($actionID)
    {
        //将 user-add 形式替换成 UserAdd 形式
        $actionID = preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
            return ucfirst($matches[1]);
        }, ucfirst($actionID));

        $actionID = 'action'.$actionID;
        if($this->hasMethod($actionID)) {
            return $this->$actionID();
        } else {
            throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$actionID()");
        }
    }
}