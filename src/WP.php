<?php
/**
 * WP.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

require __DIR__ . '/BaseWP.php';

class WP extends \wp\BaseWP
{

}

spl_autoload_register(['WP', 'autoload'], true, true);