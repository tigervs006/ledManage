<?php
/*
 * +----------------------------------------------------------------------------------
 * | https://www.tigervs.com
 * +----------------------------------------------------------------------------------
 * | Email: Kevin@tigervs.com
 * +----------------------------------------------------------------------------------
 * | Copyright (c) Shenzhen Tiger Technology Co., Ltd. 2018~2022. All rights reserved.
 * +----------------------------------------------------------------------------------
 */

declare (strict_types = 1);

if (!function_exists('getPath')) {
    /**
     * 获取pathinfo
     * @return bool|string
     */
    function getPath(): bool|string
    {
        $pathinfo = request()->pathinfo();
        /* 处理带分页的路径 */
        $path = preg_filter('/\/page\/\d+\.html/' ,'', $pathinfo);
        $pathArr = array_filter(explode('/', $path ?? $pathinfo));
        return end($pathArr);
    }
}

if(!function_exists('intvals')) {
    /**
     * 转换数字字符串
     * @return mixed
     * @param mixed $val
     */
    function intvals(mixed $val): mixed
    {
        return is_numeric($val) ? intval($val) : $val;
    }
}

if (!function_exists('msectime')) {
    /**
     * 获取毫秒数
     * @return float
     */
    function msectime(): float
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }
}

if(!function_exists('filterSearch')) {
    /**
     * 模糊搜索条件
     * @return array
     * @param array $map
     */
    function filterSearch(array $map): array
    {
        $search = [];
        foreach ($map as $key => $val) {
            $search[] = [$key, 'like', '%' . $val . '%'];
        }
        return $search;
    }
}

if(!function_exists('filterParams')) {
    /**
     * 组装搜索条件
     * @return array
     * @author Kevin
     * @param bool $json
     * @param array $params
     * @param array $where 条件
     * @param string|null $jsonField
     * @createAt 2022/11/11 8:57
     */
    function filterParams(array $params, bool $json = false, string $jsonField = null, array $where = []): array
    {
        foreach ($params as $key => $val) {
            $where[] = match (true) {
                is_array($val) => ($json && $jsonField) ? ["$jsonField->$key", 'in', $val] : [$key, 'in', $val],
                is_numeric($val) => ($json && $jsonField) ? ["$jsonField->$key", '=', $val] : [$key, '=', $val],
                default => ($json && $jsonField) ? ["$jsonField->$key", 'like', "%$val%"] : [$key, 'like', "%$val%"],
            };
        }
        return $where;
    }
}

if (!function_exists('formatBytes')) {
    /**
     * 格式化字节大小
     * @return string
     * @param  number $size 字节数
     * @param  string $delimiter 数字和单位分隔符
     */
    function formatBytes($size, string $delimiter = ''): string
    {
        $units = array(' Byte', ' KB', ' MB', ' GB', ' TB', ' PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('strOrderFilter')) {
    /**
     * 排序字段字符串截取
     * @return string
     * @param string $str 字符串
     * @param string|null $needle 节点
     * @param bool|null $before_needle 节点前面/后面
     */
    function strOrderFilter(string $str, ?string $needle = 'end', ?bool $before_needle = true): string
    {
        return stristr($str, $needle, $before_needle);
    }
}

if (!function_exists('pathToDeatil')) {
    /**
     * 根据路径获取route_name
     * @return string
     * @param string $path
     */
    function pathToDeatil(string $path): string
    {
        $arr = explode('/', $path);
        return array_shift($arr) . 'Detail';
    }
}
