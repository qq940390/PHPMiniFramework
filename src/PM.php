<?php
/**
 * PM.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

require __DIR__ . '/BasePM.php';

class PM extends \pm\BasePM
{

}

spl_autoload_register(['PM', 'autoload'], true, true);