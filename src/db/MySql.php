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

use pm\exception\DbException;

/**
 * MySql 数据库类
 *
 * @package pm\db
 */
class MySql {

    /**
     * @var object 数据库连接
     */
    public $db;


    /**
     * 初始化
     *
     * @param array $dbConfig
     * @return void
     */
    function __construct($dbConfig) {
        $dbClass = '\\pm\\db\\driver\\MySqlDriver';
        if(class_exists('\mysqli')) {
            $dbClass = '\\pm\\db\\driver\\MySqliDriver';
        }
        $dbConfig['class'] = $dbClass;
        $this->db = new $dbClass($dbConfig);
    }


    /**
     * 返回数据库对象实例
     *
     * @return object
     */
    public function instance() {
        return $this->db;
    }


    /**
     * 表名
     *
     * @param string $table
     * @return string
     */
    public function table($table) {
        return $this->db->tableName($table);
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
    public function delete($table, $condition, $limit = 0) {
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
        return $this->query($sql);
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
     */
    public function insert($table, $data, $returnInsertId = false, $replace = false, $silent = false) {

        $sql = $this->implode($data);

        $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

        $table = $this->table($table);
        $silent = $silent ? 'SILENT' : '';

        return $this->query("$cmd $table SET $sql", null, $silent, !$returnInsertId);
    }


    /**
     * 更新数据
     *
     * @param string $table
     * @param array $data
     * @param mixed $condition
     * @param bool $lowPriority
     * @return mixed
     */
    public function update($table, $data, $condition, $lowPriority = false) {
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
        $res = $this->query("$cmd $table SET $sql WHERE $where");
        return $res;
    }


    /**
     * 最后插入的ID
     *
     * @return int
     */
    public function insertId() {
        return $this->db->insertId();
    }


    /**
     * 获取数据
     *
     * @param object $resource
     * @param int $type
     * @return mixed
     */
    public function fetch($resource, $type = MYSQL_ASSOC) {
        return $this->db->fetchArray($resource, $type);
    }


    /**
     * 获取第一个数据
     *
     * @param string $sql
     * @param array $arg
     * @param bool $silent
     * @return mixed
     */
    public function fetchFirst($sql, $arg = array(), $silent = false) {
        $res = $this->query($sql, $arg, $silent, false);
        $ret = $this->db->fetchArray($res);
        $this->db->freeResult($res);
        return $ret ? $ret : array();
    }


    /**
     * 获取所有数据
     *
     * @param string $sql
     * @param array $arg
     * @param string $keyField
     * @param bool $silent
     * @return mixed
     */
    public function fetchAll($sql, $arg = array(), $keyField = '', $silent=false) {

        $data = array();
        $query = $this->query($sql, $arg, $silent, false);
        while ($row = $this->db->fetchArray($query)) {
            if ($keyField && isset($row[$keyField])) {
                $data[$row[$keyField]] = $row;
            } else {
                $data[] = $row;
            }
        }
        $this->db->freeResult($query);
        return $data;
    }


    /**
     * 获取结果
     *
     * @param object $resource
     * @param integer $row
     * @return mixed
     */
    public function result($resource, $row = 0) {
        return $this->db->result($resource, $row);
    }


    /**
     * 获取第一个结果
     *
     * @param string $sql
     * @param array $arg
     * @param bool $silent
     * @return mixed
     */
    public function resultFirst($sql, $arg = array(), $silent = false) {
        $res = $this->query($sql, $arg, $silent, false);
        $ret = $this->db->result($res, 0);
        $this->db->freeResult($res);
        return $ret;
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
    public function query($sql, $arg = array(), $silent = false) {
        if (!empty($arg)) {
            if (is_array($arg)) {
                $sql = $this->format($sql, $arg);
            } elseif ($arg === 'SILENT') {
                $silent = true;

            }
        }

        $ret = $this->db->query($sql, $silent);
        if ($ret) {
            $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
            if ($cmd === 'SELECT') {

            } elseif ($cmd === 'UPDATE' || $cmd === 'DELETE') {
                $ret = $this->db->affectedRows();
            } elseif ($cmd === 'INSERT') {
                $ret = $this->db->insertId();
            }
        }
        return $ret;
    }


    /**
     * 结果行数
     *
     * @param object $resource
     * @return int
     */
    public function numRows($resource) {
        return $this->db->numRows($resource);
    }


    /**
     * 影响的行数
     *
     * @return int
     */
    public function affectedRows() {
        return $this->db->affectedRows();
    }


    /**
     * 释放结果
     *
     * @param string $query
     * @return mixed
     */
    public function freeResult($query) {
        return $this->db->freeResult($query);
    }


    /**
     * 错误信息
     *
     * @return mixed
     */
    public function error() {
        return $this->db->error();
    }


    /**
     * 错误号
     *
     * @return mixed
     */
    public function errno() {
        return $this->db->errno();
    }


    /**
     * 处理字符串
     *
     * @param mixed $str
     * @param bool $noArray
     * @return mixed
     */
    public function quote($str, $noArray = false) {

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
    public function quoteField($field) {
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
    public function limit($start, $limit = 0) {
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
    public function order($field, $order = 'ASC') {
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
    public function field($field, $val, $glue = '=') {

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
    public function implode($array, $glue = ',') {
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
    public function implodeFieldValue($array, $glue = ',') {
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
    public function format($sql, $arg) {
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

}