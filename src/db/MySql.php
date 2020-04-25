<?php
/**
 * Database.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm\db;

use PM;

use pm\base\Component;
use pm\exception\DbException;

/**
 * MySql 数据库类
 *
 * @package pm\db
 */
class MySql extends Component implements DatabaseInterface
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
     * 初始化
     *
     * @param array $config
     * @throws DbException
     */
    function __construct($config)
    {
        $this->config = $config;
        $this->debug = $config['debug'];
        $this->prefix = $config['prefix'];
        $this->connect();
        parent::__construct();
    }

    /**
     * 连接数据库
     * @throws DbException
     */
    private function connect() {

        if(empty($this->config)) {
            $this->halt('config_db_not_found');
        }

        $this->currentLink = new \mysqli();
        if(!$this->currentLink->real_connect($this->config['host'], $this->config['username'], $this->config['password'], $this->config['dbname'], $this->config['port'], null, MYSQLI_CLIENT_COMPRESS)) {
            $this->halt('notconnect', $this->errno());
        } else {
            $this->currentLink->set_charset($this->config['charset']);
            $serverSet = $this->version() > '5.0.1' ? 'sql_mode=\'\'' : '';
            $serverSet && $this->currentLink->query("SET $serverSet");
        }
        $this->links[] = $this->currentLink;
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
     * 版本号
     *
     * @return string
     */
    private function version() {
        if(empty($this->version)) {
            $this->version = $this->currentLink->server_info;
        }
        return $this->version;
    }

    /**
     * 获取完整表名
     *
     * @param string $table
     * @return string
     */
    public function table($table)
    {
        return $this->prefix.$table;
    }

    /**
     * 删除数据
     *
     * @param string $table
     * @param array|string $condition
     * @param integer $limit
     * @return bool|array
     * @throws DbException
     */
    public function delete($table, $condition, $limit = 0)
    {
        if (empty($condition)) {
            return false;
        } elseif (is_array($condition)) {
            if (count($condition) == 2 && isset($condition['where']) && isset($condition['arg'])) {
                $where = $this->format($condition['where'], $condition['arg']);
            } else {
                $where = $this->implodeFieldValue($condition, ' AND ');
            }
        } elseif(is_string($condition)) {
            $where = $condition;
        }
        $limit = intval($limit);
        $sql = "DELETE FROM " . $this->table($table) . " WHERE $where " . ($limit > 0 ? "LIMIT $limit" : '');
        return $this->_query($sql);
    }

    /**
     * 插入数据
     *
     * @param string $table
     * @param array $data
     * @param bool $returnInsertId
     * @param bool $replace
     * @param bool $silent
     * @return mixed
     * @throws DbException
     */
    public function insert($table, $data, $returnInsertId = false, $replace = false, $silent = false)
    {

        $sql = $this->implode($data);

        $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

        $table = $this->table($table);
        $silent = $silent ? 'SILENT' : '';

        return $this->_query("$cmd $table SET $sql", $silent);
    }

    /**
     * 更新数据
     *
     * @param string $table
     * @param array $data
     * @param mixed $condition
     * @param bool $lowPriority
     * @return mixed
     * @throws DbException
     */
    public function update($table, $data, $condition, $lowPriority = false)
    {
        $sql = $this->implode($data);
        if(empty($sql)) {
            return false;
        }
        $cmd = "UPDATE " . ($lowPriority ? 'LOW_PRIORITY' : '');
        $table = $this->table($table);
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = $this->implode($condition, ' AND ');
        } else {
            $where = $condition;
        }
        $res = $this->_query("$cmd $table SET $sql WHERE $where");
        return $res;
    }

    /**
     * 最后插入的ID
     *
     * @return int
     */
    public function insertId()
    {
        return ($id = $this->currentLink->insert_id) >= 0 ? $id : $this->result($this->_query("SELECT last_insert_id()"), 0);
    }

    /**
     * 获取数据
     *
     * @param string $query
     * @param int $resultType
     * @return array
     */
    private function fetchArray($query, $resultType = MYSQLI_ASSOC) {
        if($resultType == 'MYSQL_ASSOC') $resultType = MYSQLI_ASSOC;
        return $query ? $query->fetch_array($resultType) : null;
    }

    /**
     * 释放结果
     *
     * @param string $query
     * @return mixed
     */
    private function freeResult($query) {
        return $query ? $query->free() : false;
    }

    /**
     * 获取数据
     *
     * @param object $resource
     * @param int $type
     * @return mixed
     */
    private function fetch($resource, $type = MYSQL_ASSOC)
    {
        return $this->fetchArray($resource, $type);
    }

    /**
     * 获取第一个数据
     *
     * @param string $sql
     * @param array $arg
     * @param bool $silent
     * @return mixed
     * @throws DbException
     */
    public function fetchFirst($sql, $arg = [], $silent = false)
    {
        $res = $this->_query($sql, $silent, false);
        $ret = $this->fetchArray($res);
        $this->freeResult($res);
        return $ret ? $ret : [];
    }

    /**
     * 获取所有数据
     *
     * @param string $sql
     * @param array $arg
     * @param string $keyField
     * @param bool $silent
     * @return mixed
     * @throws DbException
     */
    public function fetchAll($sql, $arg = [], $keyField = '', $silent=false)
    {

        $data = [];
        $query = $this->_query($sql, $silent, false);
        while ($row = $this->fetchArray($query)) {
            if ($keyField && isset($row[$keyField])) {
                $data[$row[$keyField]] = $row;
            } else {
                $data[] = $row;
            }
        }
        $this->freeResult($query);
        return $data;
    }

    /**
     * 获取结果
     *
     * @param string $query
     * @param integer $row
     * @return mixed
     */
    private function result($query, $row = 0) {
        if(!$query || $query->num_rows == 0) {
            return null;
        }
        $query->data_seek($row);
        $assocs = $query->fetch_row();
        return $assocs[0];
    }

    /**
     * 获取第一个结果
     *
     * @param string $sql
     * @param array $arg
     * @param bool $silent
     * @return mixed
     */
    public function resultFirst($sql, $arg = [], $silent = false)
    {
        $res = $this->_query($sql, $arg, $silent, false);
        $ret = $this->result($res, 0);
        $this->freeResult($res);
        return $ret;
    }

    /**
     * 查询
     *
     * @param string $sql
     * @param bool $silent
     * @param bool $unbuffered
     * @return array
     */
    private function _query($sql, $silent = false, $unbuffered = false) {
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

        if(!($query = $this->currentLink->query($sql, $resultmode))) {
            if(in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
                $this->connect();
                return $this->currentLink->query($sql, 'RETRY'.$silent);
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
     * 查询
     *
     * @param string $sql
     * @param array $arg
     * @param bool $silent
     * @return mixed
     * @throws DbException
     */
    public function query($sql, $arg = [], $silent = false)
    {
        if (!empty($arg)) {
            if (is_array($arg)) {
                $sql = $this->format($sql, $arg);
            } elseif ($arg === 'SILENT') {
                $silent = true;

            }
        }

        $ret = $this->_query($sql, $silent);
        if ($ret) {
            $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
            if ($cmd === 'SELECT') {

            } elseif ($cmd === 'UPDATE' || $cmd === 'DELETE') {
                $ret = $this->affectedRows();
            } elseif ($cmd === 'INSERT') {
                $ret = $this->insertId();
            }
        }
        return $ret;
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @param array $arg
     * @return mixed
     */
    public function excute($sql, $arg = [])
    {

    }

    /**
     * 结果行数
     *
     * @param object $resource
     * @return int
     */
    public function numRows($resource)
    {
        return $resource ? $resource->num_rows : 0;
    }

    /**
     * 影响的行数
     *
     * @return int
     */
    public function affectedRows() {
        return $this->currentLink->affected_rows;
    }

    /**
     * 错误信息
     *
     * @return mixed
     */
    private function error()
    {
        return (($this->currentLink) ? $this->currentLink->error : mysqli_error());
    }

    /**
     * 错误号
     *
     * @return mixed
     */
    private function errno()
    {
        return intval(($this->currentLink) ? $this->currentLink->errno : mysqli_errno());
    }

    /**
     * 处理字符串
     *
     * @param mixed $str
     * @param bool $noArray
     * @return mixed
     */
    private function quote($str, $noArray = false)
    {

        if (is_string($str))
            return '\'' . addslashes($str) . '\'';

        if (is_int($str) or is_float($str))
            return '\'' . $str . '\'';

        if (is_array($str)) {
            if($noArray === false) {
                foreach ($str as &$v) {
                    $v = $this->quote($v, true);
                }
                return $str;
            } else {
                return '\'\'';
            }
        }

        if (is_bool($str))
            return $str ? '1' : '0';

        return '\'\'';
    }

    /**
     * 处理列
     *
     * @param mixed $field
     * @return mixed
     */
    private function quoteField($field)
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $field[$k] = $this->quoteField($v);
            }
        } else {
            if (strpos($field, '`') !== false)
                $field = str_replace('`', '', $field);
            $field = '`' . $field . '`';
        }
        return $field;
    }

    /**
     * 处理数量
     *
     * @param int $start
     * @param integer $limit
     * @return string
     */
    private function limit($start, $limit = 0)
    {
        $limit = intval($limit > 0 ? $limit : 0);
        $start = intval($start > 0 ? $start : 0);
        if ($start > 0 && $limit > 0) {
            return " LIMIT $start, $limit";
        } elseif ($limit) {
            return " LIMIT $limit";
        } elseif ($start) {
            return " LIMIT $start";
        } else {
            return '';
        }
    }

    /**
     * 处理排序
     *
     * @param string $field
     * @param string $order
     * @return string
     */
    private function order($field, $order = 'ASC')
    {
        if(empty($field)) {
            return '';
        }
        $order = strtoupper($order) == 'ASC' || empty($order) ? 'ASC' : 'DESC';
        return $this->quoteField($field) . ' ' . $order;
    }

    /**
     * 获取列
     *
     * @param string $field
     * @param mixed $val
     * @param string $glue
     * @return mixed
     * @throws DbException
     */
    private function field($field, $val, $glue = '=')
    {

        $field = $this->quoteField($field);

        if (is_array($val)) {
            $glue = $glue == 'notin' ? 'notin' : 'in';
        } elseif ($glue == 'in') {
            $glue = '=';
        }

        switch ($glue) {
            case '=':
                return $field . $glue . $this->quote($val);
                break;
            case '-':
            case '+':
                return $field . '=' . $field . $glue . $this->quote((string) $val);
                break;
            case '|':
            case '&':
            case '^':
                return $field . '=' . $field . $glue . $this->quote($val);
                break;
            case '>':
            case '<':
            case '<>':
            case '<=':
            case '>=':
                return $field . $glue . $this->quote($val);
                break;

            case 'like':
                return $field . ' LIKE(' . $this->quote($val) . ')';
                break;

            case 'in':
            case 'notin':
                $val = $val ? implode(',', $this->quote($val)) : '\'\'';
                return $field . ($glue == 'notin' ? ' NOT' : '') . ' IN(' . $val . ')';
                break;

            default:
                throw new DbException('Not allow this glue between field and value: "' . $glue . '"');
        }
    }

    /**
     * 组合数组
     *
     * @param array $array
     * @param string $glue
     * @return string
     */
    private function implode($array, $glue = ',')
    {
        $sql = $comma = '';
        $glue = ' ' . trim($glue) . ' ';
        foreach ($array as $k => $v) {
            $sql .= $comma . $this->quoteField($k) . '=' . $this->quote($v);
            $comma = $glue;
        }
        return $sql;
    }

    /**
     * 组合列的值
     *
     * @param array $array
     * @param string $glue
     * @return string
     */
    private function implodeFieldValue($array, $glue = ',')
    {
        return $this->implode($array, $glue);
    }

    /**
     * 格式化
     *
     * @param string $sql
     * @param array $arg
     * @return string
     * @throws DbException
     */
    public function format($sql, $arg)
    {
        $count = substr_count($sql, '%');
        if (!$count) {
            return $sql;
        } elseif ($count > count($arg)) {
            throw new DbException('SQL string format error! This SQL need "' . $count . '" vars to replace into.', 0, $sql);
        }

        $len = strlen($sql);
        $i = $find = 0;
        $ret = '';
        while ($i <= $len && $find < $count) {
            if ($sql{$i} == '%') {
                $next = $sql{$i + 1};
                if ($next == 't') {
                    $ret .= $this->table($arg[$find]);
                } elseif ($next == 's') {
                    $ret .= $this->quote(is_array($arg[$find]) ? serialize($arg[$find]) : (string) $arg[$find]);
                } elseif ($next == 'f') {
                    $ret .= sprintf('%F', $arg[$find]);
                } elseif ($next == 'd') {
                    $ret .= intval($arg[$find]);
                } elseif ($next == 'i') {
                    $ret .= $arg[$find];
                } elseif ($next == 'n') {
                    if (!empty($arg[$find])) {
                        $ret .= is_array($arg[$find]) ? implode(',', $this->quote($arg[$find])) : $this->quote($arg[$find]);
                    } else {
                        $ret .= '0';
                    }
                } else {
                    $ret .= $this->quote($arg[$find]);
                }
                $i++;
                $find++;
            } else {
                $ret .= $sql{$i};
            }
            $i++;
        }
        if ($i < $len) {
            $ret .= substr($sql, $i);
        }
        return $ret;
    }

    /**
     * 选择数据库
     *
     * @param string $databaseName
     * @return object
     */
    public function selectDb($databaseName) {
        return $this->currentLink->select_db($databaseName);
    }
    /**
     * 结果列数
     *
     * @param string $query
     * @return int
     */
    private function numFields($query) {
        return $query ? $query->field_count : null;
    }

    /**
     * 获取行数据
     *
     * @param string $query
     * @return array
     */
    private function fetchRow($query) {
        $query = $query ? $query->fetch_row() : null;
        return $query;
    }

    /**
     * 获取列数据
     *
     * @param string $query
     * @return array
     */
    private function fetchFields($query) {
        return $query ? $query->fetch_field() : null;
    }

    /**
     * 字符串处理
     *
     * @param string $str
     * @return string
     */
    private function escapeString($str) {
        return $this->currentLink->escape_string($str);
    }

    /**
     * 关闭连接
     *
     * @return bool
     */
    private function close() {
        return $this->currentLink->close();
    }
}