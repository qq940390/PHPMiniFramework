<?php
/**
 * Response.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\helper;


class Format extends \pm\base\Component
{

    /**
     * JSON格式
     * @param $data
     * @return false|string
     */
    public static function toJSON($data)
    {
        return json_encode($data);
    }

    /**
     * JSONP格式
     * @param $data
     * @param string $callback
     * @return string
     */
    public static function toJSONP($data, $callback = 'callback')
    {
        return sprintf('%s(%s)', $_GET[$callback], json_encode($data));
    }

}