<?php
/**
 * 基本类
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace wp\base;

class BaseObject
{

    /**
     * @return string 获取调用者的类名
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * BaseObject constructor.
     *
     * @param array $config
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 初始化对象
     */
    public function init()
    {
    }

    /**
     * 返回对象的属性
     *
     * @param string $name 属性名
     * @return mixed 属性值
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
    }

    /**
     * 设置对象的属性
     *
     * @param string $name 属性名
     * @param mixed $value 属性值
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
    }

    /**
     * 检查属性名是否设置
     *
     * @param string $name 属性名
     * @return bool
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    /**
     * 设置对象属性为空值
     *
     * @param string $name 属性名
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        }
    }

    /**
     * 魔术方法 __call
     * @param string $name
     * @param array $params
     * @throws UnknownMethodException when calling unknown method
     * @return mixed the method return value
     */
    public function __call($name, $params)
    {
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * 是否包含某个方法
     *
     * @param string $name 方法名
     * @return bool
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}