<?php
/**
 * MySqlDriver.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\db\driver;

use pm\exception\DbException;

/**
 * DB驱动类 MySqlDriver
 *
 * @package pm\db\mysql
 */
class MySqlDriver
{

    /**
     * @var string 表前缀
     */
    private $prefix;

    /**
     * @var string 版本号
     */
    private $version = '';

    /**
     * @var int 查询数
     */
    private $queryNum = 0;

    /**
     * @var object 当前连接
     */
    private $currentLink;

    /**
     * @var array 连接数组
     */
    private $links = [];

    /**
     * @var array 配置
     */
    private $config = [];

    /**
     * @var bool 调试模式
     */
    private $debug = false;

    /**
     * @var array 调试信息
     */
    private $sqlDebug = [];


    /**
     * 构造函数
     *
     * @param array $config 配置
     * @return void
     */
    function __construct($config) {
        $this->config = $config;
        $this->debug = $config['debug'];
        $this->prefix = $config['prefix'];
        $this->connect();
    }

    /**
     * 连接
     */
    private function connect() {

        if(empty($this->config)) {
            $this->halt('config_db_not_found');
        }

        $this->currentLink = $this->_dbconnect(
            $this->config['host'],
            $this->config['port'],
            $this->config['username'],
            $this->config['password'],
            $this->config['charset'],
            $this->config['dbname'],
            $this->config['pconnect']
        );
        $this->links[] = $this->currentLink;
    }

    /**
     * 构造数据库连接
     *
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $charset
     * @param string $dbname
     * @param int $pconnect
     * @param bool $halt
     * @return object
     */
    private function _dbconnect($host, $port, $username, $password, $charset, $dbname, $pconnect, $halt = true) {
        if($pconnect) {
            $link = @mysql_pconnect($host.':'.$port, $username, $password, MYSQL_CLIENT_COMPRESS);
        } else {
            $link = @mysql_connect($host.':'.$port, $username, $password, 1, MYSQL_CLIENT_COMPRESS);
        }
        if(!$link) {
            $halt && $this->halt('notconnect', $this->errno());
        } else {
            $this->currentLink = $link;
            if($this->version() > '4.1') {
                $charset = $charset ? $charset : $this->config['charset'];
                $serverset = $charset ? 'character_set_connection='.$charset.', character_set_results='.$charset.', character_set_client=binary' : '';
                $serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
                $serverset && mysql_query("SET $serverset", $link);
            }
            $dbname && @mysql_select_db($dbname, $link);
        }
        return $link;
    }

    /**
     * 错误信息
     *
     * @return mixed
     */
    private function error() {
        return (($this->currentLink) ? mysql_error($this->currentLink) : mysql_error());
    }

    /**
     * 错误号
     *
     * @return mixed
     */
    private function errno() {
        return intval(($this->currentLink) ? mysql_errno($this->currentLink) : mysql_errno());
    }

    /**
     * 抛出异常
     *
     * @param string $message
     * @param integer $code
     * @param string $sql
     * @throws DbException
     */
    private function halt($message = '', $code = 0, $sql = '') {
        throw new DbException($message, $code, $sql);
    }

    /**
     * 表名
     *
     * @param string $tablename
     * @return string
     */
    public function tableName($tablename) {
        return $this->prefix.$tablename;
    }

    /**
     * 选择数据库
     *
     * @param string $dbname
     * @return object
     */
    public function selectDb($dbname) {
        return mysql_select_db($dbname, $this->currentLink);
    }

    /**
     * 获取数据
     *
     * @param string $query
     * @param int $resultType
     * @return array
     */
    public function fetchArray($query, $resultType = MYSQL_ASSOC) {
        if($resultType == 'MYSQL_ASSOC') $resultType = MYSQL_ASSOC;
        return mysql_fetch_array($query, $resultType);
    }

    /**
     * 获取第一个
     *
     * @param string $sql
     * @return array
     */
    public function fetchFirst($sql) {
        return $this->fetchArray($this->query($sql));
    }

    /**
     * 第一条结果
     *
     * @param string $sql
     * @return array
     */
    public function resultFirst($sql) {
        return $this->result($this->query($sql), 0);
    }

    /**
     * 查询
     *
     * @param string $sql
     * @param bool $silent
     * @param bool $unbuffered
     * @return array
     */
    public function query($sql, $silent = false, $unbuffered = false) {
        if($this->debug) {
            $starttime = microtime(true);
        }

        if('UNBUFFERED' === $silent) {
            $silent = false;
            $unbuffered = true;
        } elseif('SILENT' === $silent) {
            $silent = true;
            $unbuffered = false;
        }

        $func = $unbuffered ? 'mysql_unbuffered_query' : 'mysql_query';

        if(!($query = $func($sql, $this->currentLink))) {
            if(in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
                $this->connect();
                return $this->query($sql, 'RETRY'.$silent);
            }
            if(!$silent) {
                $this->halt($this->error(), $this->errno(), $sql);
            }
        }

        if($this->debug) {
            $this->sqlDebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->currentLink);
        }

        $this->queryNum++;
        return $query;
    }

    /**
     * 影响的行数
     *
     * @return int
     */
    public function affectedRows() {
        return mysql_affected_rows($this->currentLink);
    }

    /**
     * 结果
     *
     * @param string $query
     * @param integer $row
     * @return mixed
     */
    public function result($query, $row = 0) {
        $query = @mysql_result($query, $row);
        return $query;
    }

    /**
     * 结果行数
     *
     * @param string $query
     * @return int
     */
    public function numRows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }

    /**
     * 结果列数
     *
     * @param string $query
     * @return int
     */
    public function numFields($query) {
        return mysql_num_fields($query);
    }

    /**
     * 释放结果
     *
     * @param string $query
     * @return mixed
     */
    public function freeResult($query) {
        return mysql_free_result($query);
    }

    /**
     * 获取最后插入ID
     *
     * @return int
     */
    public function insertId() {
        return ($id = mysql_insert_id($this->currentLink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    /**
     * 获取行数据
     *
     * @param string $query
     * @return array
     */
    public function fetchRow($query) {
        $query = mysql_fetch_row($query);
        return $query;
    }

    /**
     * 获取列数据
     *
     * @param string $query
     * @return array
     */
    public function fetchFields($query) {
        return mysql_fetch_field($query);
    }

    /**
     * 版本号
     *
     * @return string
     */
    public function version() {
        if(empty($this->version)) {
            $this->version = mysql_get_server_info($this->currentLink);
        }
        return $this->version;
    }

    /**
     * 字符串处理
     *
     * @param string $str
     * @return string
     */
    public function escapeString($str) {
        return addslashes($str);
    }

    /**
     * 关闭连接
     *
     * @return bool
     */
    public function close() {
        return mysql_close($this->currentLink);
    }

}
