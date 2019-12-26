<?php
/**
 * Response.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\base;


abstract class Response extends Component
{

    /**
     * Sends the response to client.
     */
    abstract public function send();

}