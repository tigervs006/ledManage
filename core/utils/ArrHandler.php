<?php

namespace core\utils;

/**
 * 操作数组帮助类
 * Class Arr
 * @package core\utils
 */
class ArrHandler
{
    /**
     * 对数组增加默认值
     * @return array
     * @param array $keys
     * @param array $configList
     */
    public static function getDefaultValue(array $keys, array $configList = []): array
    {
        $value = [];
        foreach ($keys as $val) {
            if (is_array($val)) {
                $k = $val[0] ?? '';
                $v = $val[1] ?? '';
            } else {
                $k = $val;
                $v = '';
            }
            $value[$k] = $configList[$k] ?? $v;
        }
        return $value;
    }

    /**
     * 获取ivew菜单列表
     * @return array
     * @param array $data
     */
    public static function getMenuIviewList(array $data): array
    {
        return ArrHandler::toIviewUi(ArrHandler::getTree($data));
    }

    /**
     * 转化iviewUi需要的key值
     * @return array
     * @param array $data
     */
    public static function toIviewUi(array $data): array
    {
        $newData = [];
        foreach ($data as $k => $v) {
            $temp = [];
            $temp['path'] = $v['menu_path'];
            $temp['title'] = $v['menu_name'];
            $temp['icon'] = $v['icon'];
            $temp['header'] = $v['header'];
            $temp['is_header'] = $v['is_header'];
            if ($v['is_show_path']) {
                $temp['auth'] = ['hidden'];
            }
            if (!empty($v['children'])) {
                $temp['children'] = self::toIviewUi($v['children']);
            }
            $newData[] = $temp;
        }
        return $newData;
    }

    /**
     * 获取树型菜单
     * @return array
     * @param array $data
     * @param int $pid
     * @param int $level
     */
    public static function getTree(array $data, int $pid = 0, int $level = 1): array
    {
        $childs = self::getChild($data, $pid, $level);
        $dataSort = array_column($childs, 'sort');
        array_multisort($dataSort, SORT_DESC, $childs);
        foreach ($childs as $key => $navItem) {
            $resChild = self::getTree($data, $navItem['id']);
            if (null != $resChild) {
                $childs[$key]['children'] = $resChild;
            }
        }
        return $childs;
    }

    /**
     * 获取子菜单
     * @return array
     * @param array $arr
     * @param int $id
     * @param int $lev
     */
    private static function getChild(array &$arr, int $id, int $lev): array
    {
        $child = [];
        foreach ($arr as  $value) {
            if ($value['pid'] == $id) {
                $value['level'] = $lev;
                $child[] = $value;
            }
        }
        return $child;
    }

    /**
     * 格式化数据
     * @return array
     * @param array $array
     * @param array $value
     * @param int $default
     */
    public static function setValeTime(array $array, array $value, int $default = 0): array
    {
        foreach ($array as $item) {
            if (!isset($value[$item]))
                $value[$item] = $default;
            else if (is_string($value[$item]))
                $value[$item] = (float)$value[$item];
        }
        return $value;
    }

    /**
     * 数组转字符串去重复
     * @return array|bool
     * @param array $data
     */
    public static function unique(array $data): array|bool
    {
        return array_unique(explode(',', implode(',', $data)));
    }

    /**
     * 获取数组中去重复过后的指定key值
     * @param array $list
     * @param string $key
     * @return array
     */
    public static function getUniqueKey(array $list, string $key): array
    {
        return array_unique(array_column($list, $key));
    }

    /**
     * 获取数组钟随机值
     * @return bool|array
     * @param array $data
     */
    public static function getArrayRandKey(array $data): bool|array
    {
        if (!$data) {
            return false;
        }
        $mun = rand(0, count($data));
        if (!isset($data[$mun])) {
            return self::getArrayRandKey($data);
        }
        return $data[$mun];
    }
}
