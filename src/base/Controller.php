<?php
/**
 * 基控制器类
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\base;

/**
 * Class Controller
 *
 * @package pm\base
 */
class Controller extends Component
{

    /**
     * @var string 默认action
     */
    public $defaultAction = 'actionIndex';


    /**
     * 执行 action
     * @param $actionID
     * @throws \ReflectionException
     */
    public function runAction($actionID)
    {
        if($actionID) {
            //将 user-add 形式替换成 UserAdd 形式
            $actionID = 'action'.preg_replace_callback('%-([a-z0-9_])%i', function ($matches) {
                return ucfirst($matches[1]);
            }, ucfirst($actionID));
        } else {
            $actionID = $this->defaultAction;
        }

        $inlineAction = new \ReflectionMethod(self::className(), $actionID);
        if($inlineAction) {
            $inlineAction->invoke($this, $actionID);
        } else {
            throw new \pm\exception\UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$actionID()");
        }
    }

    /**
     * 视图渲染输出
     * @param $viewName
     * @param array $data
     */
    public function render($viewName, $data = [])
    {

    }

    /**
     * JSON渲染输出
     * @param array $data
     * @param bool|string $jsonpCallback JSONP的callback回调函数名
     */
    public function renderAjax($data, $jsonpCallback = false)
    {
        echo $jsonpCallback === false ? \pm\helper\Format::toJSON($data) : \pm\helper\Format::toJSONP($data, $jsonpCallback);
    }
}