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

namespace core\utils;

/**
 * 操作数组帮助类
 * Class Arr
 * @package core\utils
 */
class ArrHandler
{
    /**
     * @return array
     * @param array $data 数组
     * @param string|null $key 键值
     * @param string|null $type 排序方式
     */
    public function ArrMultisort(array $data, ?string $key = 'id', ?string $type = 'ASC'): array
    {
        switch ($type) {
            case 'DESC':
                array_multisort(array_column($data, $key), SORT_DESC, $data);
                break;
            default:
                array_multisort(array_column($data, $key), SORT_ASC, $data);
        }
        return $data;
    }

    /**
     * 根据特定键值对数组分组
     * @return array|\think\Collection
     * @param array|\think\Collection $data 数组
     * @param string|null $key 键值
     */
    public function ArrToGroup(array|\think\Collection $data, ?string $key = 'pid'): array|\think\Collection
    {
        $result = [];
        foreach ($data as $value) {
            $result[$value[$key]][] = $value;
        }
        return array_values($result);
    }
}
