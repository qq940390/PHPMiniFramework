<?php
/**
 * Mysqli.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\db\mysql;

/**
 * DB驱动类 MySqli
 *
 * @copyright [JWP] wujinhai
 * @link http://wujinhai.cn/
 * @author wujinhai<940390@qq.com>
 * @package JWP\Helpers
 * @subpackage JDB
 * @version 1.0
 */
class MySqli
{

    /**
     * @var string 表前缀
     */
    var $prefix;

    /**
     * @var string 版本号
     */
    var $version = '';

    /**
     * @var int 查询数
     */
    var $querynum = 0;

    /**
     * @var int 从库id
     */
    var $slaveid = 0;

    /**
     * @var object 当前连接
     */
    var $curlink;

    /**
     * @var array 链接数组
     */
    var $link = array();

    /**
     * @var array 配置
     */
    var $config = array();

    /**
     * @var bool 调试模式
     */
    var $debug = false;

    /**
     * @var array 调试信息
     */
    var $sqldebug = array();



    /**
     * 构造函数
     *
     * @param array $config 配置
     * @return void
     */
    function __construct($config = array()) {
        if(!empty($config)) {
            $this->setConfig($config);
        }
    }


    /**
     * 设置配置
     *
     * @param array $config 配置
     * @return void
     */
    public function setConfig($config) {
        $this->config = &$config;
        $this->debug = $config['debug'];
        $this->prefix = $config['prefix'];
    }


    /**
     * 连接
     *
     * @return void
     */
    public function connect() {

        if(empty($this->config)) {
            $this->halt('config_db_not_found');
        }

        $this->link = $this->_dbconnect(
            $this->config['host'],
            $this->config['port'],
            $this->config['username'],
            $this->config['password'],
            $this->config['charset'],
            $this->config['dbname'],
            $this->config['pconnect']
        );
        $this->curlink = $this->link;

    }


    /**
     * 析构函数
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

        $link = new \mysqli();
        if(!$link->real_connect($host, $username, $password, $dbname, $port, null, MYSQLI_CLIENT_COMPRESS)) {
            $halt && $this->halt('notconnect', $this->errno());
        } else {
            $this->curlink = $link;
            if($this->version() > '4.1') {
                $link->set_charset($charset ? $charset : $this->config['charset']);
                $serverset = $this->version() > '5.0.1' ? 'sql_mode=\'\'' : '';
                $serverset && $link->query("SET $serverset");
            }
        }
        return $link;
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
        return $this->curlink->select_db($dbname);
    }


    /**
     * 获取数据
     *
     * @param string $query
     * @param int $resultType
     * @return array
     */
    public function fetchArray($query, $resultType = MYSQLI_ASSOC) {
        if($resultType == 'MYSQL_ASSOC') $resultType = MYSQLI_ASSOC;
        return $query ? $query->fetch_array($resultType) : null;
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
     * 获取第一个结果
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

        $resultmode = $unbuffered ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;

        if(!($query = $this->curlink->query($sql, $resultmode))) {
            if(in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
                $this->connect();
                return $this->curlink->query($sql, 'RETRY'.$silent);
            }
            if(!$silent) {
                $this->halt($this->error(), $this->errno(), $sql);
            }
        }

        if($this->debug) {
            $this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink);
        }

        $this->querynum++;
        return $query;
    }


    /**
     * 影响的行数
     *
     * @return int
     */
    public function affectedRows() {
        return $this->curlink->affected_rows;
    }


    /**
     * 错误信息
     *
     * @return mixed
     */
    private function error() {
        return (($this->curlink) ? $this->curlink->error : mysqli_error());
    }


    /**
     * 错误号
     *
     * @return mixed
     */
    private function errno() {
        return intval(($this->curlink) ? $this->curlink->errno : mysqli_errno());
    }


    /**
     * 结果
     *
     * @param string $query
     * @param integer $row
     * @return mixed
     */
    public function result($query, $row = 0) {
        if(!$query || $query->num_rows == 0) {
            return null;
        }
        $query->data_seek($row);
        $assocs = $query->fetch_row();
        return $assocs[0];
    }


    /**
     * 结果行数
     *
     * @param string $query
     * @return int
     */
    public function numRows($query) {
        $query = $query ? $query->num_rows : 0;
        return $query;
    }


    /**
     * 结果列数
     *
     * @param string $query
     * @return int
     */
    public function numFields($query) {
        return $query ? $query->field_count : null;
    }


    /**
     * 释放结果
     *
     * @param string $query
     * @return mixed
     */
    public function freeResult($query) {
        return $query ? $query->free() : false;
    }


    /**
     * 获取最后插入ID
     *
     * @return int
     */
    public function insertId() {
        return ($id = $this->curlink->insert_id) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }


    /**
     * 获取数据库行
     *
     * @param string $query
     * @return array
     */
    public function fetchRow($query) {
        $query = $query ? $query->fetch_row() : null;
        return $query;
    }


    /**
     * 获取字段
     *
     * @param string $query
     * @return array
     */
    public function fetchFields($query) {
        return $query ? $query->fetch_field() : null;
    }


    /**
     * 版本号
     *
     * @return string
     */
    public function version() {
        if(empty($this->version)) {
            $this->version = $this->curlink->server_info;
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
        return $this->curlink->escape_string($str);
    }


    /**
     * 关闭连接
     *
     * @return bool
     */
    public function close() {
        return $this->curlink->close();
    }


    /**
     * 抛出错误
     *
     * @param string $message
     * @param integer $code
     * @param string $sql
     * @return void
     */
    public function halt($message = '', $code = 0, $sql = '') {
        throw new \pm\exception\DbException($message, $code, $sql);
    }

}
