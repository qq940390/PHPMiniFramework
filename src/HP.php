<?php
/**
 * HP.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

require __DIR__ . '/BaseHP.php';

class HP extends \hp\BaseHP
{

}

spl_autoload_register(['HP', 'autoload'], true, true);