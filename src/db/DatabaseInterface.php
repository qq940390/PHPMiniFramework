<?php
/**
 * DatabaseInterface.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\db;


interface DatabaseInterface
{
    /**
     * 初始化
     *
     * @param array $config
     * @return void
     */
    function __construct($config);

    /**
     * 获取完整表名
     *
     * @param string $table
     * @return string
     */
    public function table($table);

    /**
     * 删除数据
     *
     * @param string $table
     * @param array|string $condition
     * @param integer $limit
     * @return bool|array
     */
    public function delete($table, $condition, $limit = 0);

    /**
     * 插入数据
     *
     * @param string $table
     * @param array $data
     * @param bool $returnInsertId
     * @param bool $replace
     * @param bool $silent
     * @return mixed
     */
    public function insert($table, $data, $returnInsertId = false, $replace = false, $silent = false);

    /**
     * 更新数据
     *
     * @param string $table
     * @param array $data
     * @param mixed $condition
     * @param bool $lowPriority
     * @return mixed
     */
    public function update($table, $data, $condition, $lowPriority = false);

    /**
     * 最后插入的ID
     *
     * @return int
     */
    public function insertId();

    /**
     * 获取第一个数据
     *
     * @param string $sql
     * @param array $arg
     * @param bool $silent
     * @return mixed
     */
    public function fetchFirst($sql, $arg = [], $silent = false);

    /**
     * 获取所有数据
     *
     * @param string $sql
     * @param array $arg
     * @param string $keyField
     * @param bool $silent
     * @return mixed
     */
    public function fetchAll($sql, $arg = [], $keyField = '', $silent=false);

    /**
     * 查询
     *
     * @param string $sql
     * @param array $arg
     * @param bool $silent
     * @return mixed
     */
    public function query($sql, $arg = [], $silent = false);

    /**
     * 执行SQL语句
     * @param $sql
     * @param array $arg
     * @return mixed
     */
    public function excute($sql, $arg = []);
}