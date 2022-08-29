<?php
declare (strict_types = 1);
namespace core\utils;

class StringHandler
{
    /**
     * 替换特殊符号
     * @return string
     * @param string $str 字符串
     * @param string|null $separator 分隔符
     */
    public static function strSymbol(string $str, ?string $separator = ','): string
    {
        // 处理中文符号及空格
        $pattern = "/[\x{3002}\x{ff1b}\x{ff0c}\x{002d}\x{002e}\x{002f}\x{ff1a}\x{201c}\x{201d}\x{ff01}\x{ff08}\x{ff09}\x{3001}\x{ff1f}\x{300a}\x{300b}\s]+/u";
        return preg_replace($pattern, $separator, $str);
    }

    /**
     * 截取特定字符串
     * @return string
     * @param string $str 字符串
     * @param string $needle 节点
     * @param bool|null $before_needle 截取位置
     */
    public static function strNeedleExtract(string $str, string $needle, ?bool $before_needle = true): string
    {
        return stristr($str, $needle, $before_needle);
    }
}
