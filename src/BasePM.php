<?php
/**
 * BasePM.php
 *
 * @author wujinhai, 940390@qq.com
 * @website http://wujinhai.cn
 * @github https://github.com/qq940390
 * @copyright Copyright (C) 2019 wujinhai
 */

namespace pm;

class BasePM
{
    /**
     * @var \pm\web\Application
     */
    public static $app;

    /**
     * @var array registered path aliases
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = [
        '@pm' => __DIR__,
    ];


    /**
     * 自动加载器回调函数
     * 参考Yii2
     * @param $className
     */
    public static function autoload($className)
    {
        if (strpos($className, '\\') !== false) {
            //从别名路径数组中取对应的命名空间，判断类是否存在，仅支持PSR-4
            $classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include $classFile;
    }

    /**
     * 获取别名路径
     * 参考Yii2
     * @param $alias
     * @return bool|mixed|string
     */
    public static function getAlias($alias)
    {
        if (strncmp($alias, '@', 1)) {
            //不是一个别名
            return $alias;
        }

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            }

            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return $path . substr($alias, strlen($name));
                }
            }
        }

        return false;
    }

    /**
     * 设置一个别名路径
     * 参考Yii2
     * @param $alias
     * @param $path
     */
    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    /**
     * 配置对象的属性
     * @param $object object
     * @param $properties array
     * @return object
     */
    public static function configure($object, $properties)
    {
        if(empty($properties)) return $object;
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

}