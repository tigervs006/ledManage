<?php
declare (strict_types = 1);

// 应用公共文件

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
