<?php
/**
 * MysqliDriver.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\db\driver;



/**
 * DB驱动类 MySqliDriver
 *
 * @package pm\db\mysql
 */
class MySqliDriver
{

    /**
     * 构造函数
     *
     * @param array $config 配置
     * @return void
     */
    function __construct($config) {

    }



    /**
     * 获取第一个
     *
     * @param string $sql
     * @return array
     */
    private function fetchFirst($sql) {
        return $this->fetchArray($this->query($sql));
    }

    /**
     * 第一条结果
     *
     * @param string $sql
     * @return array
     */
    private function resultFirst($sql) {
        return $this->result($this->query($sql), 0);
    }



}
