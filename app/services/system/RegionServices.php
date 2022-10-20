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
namespace app\services\system;

use app\dao\system\RegionDao;
use app\services\BaseServices;
use core\exceptions\ApiException;

/**
 * @method getCityIdMax()
 */
class RegionServices extends BaseServices
{
    public function __construct(RegionDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 单个/批量删除
     * @return void
     * @param int|array|string $id
     */
    public function remove(int|array|string $id): void
    {
        $this->transaction(function () use ($id) {
            $this->dao->delete($id);
        });
    }

    /**
     * 新增/编辑栏目
     * @return void
     * @param array $data 数据
     * @param string $message 新增/编辑
     */
    public function saveRegion(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); // 释放$data中的id
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '地区失败');
        });
    }

    /**
     * 获取下级区域
     * @return array
     * @param int $pid
     * @param array $list
     * @param string $field
     */
    public function getChildCity(int $pid, string $field, array $list = []): array
    {
        $pname = $this->value(['cid' => $pid], 'name') ?? '中国';
        $data = $this->getData(['pid' => $pid], ['id' => 'asc'], $field);
        $hasChild = 0 == $pid || $this->value(['cid' => $pid], 'pid') == 0 ? 1 : 0;
        foreach ($data as $item) {
            $hasChild && $item['children'] = [];
            $item['pname'] = $pname;
            $list[] = $item;
        }
        return $list;
    }

    /**
     * 获取树状结构数据
     * @return array
     * @param int|null $pid
     * @param string|null $pname
     * @param \think\Collection $data
     */
    public function getTreeRegion(\think\Collection $data, ?int $pid = 0, ?string $pname = '中国'): array
    {
        $tree = [];
        foreach ($data as $item) {
            if ($pid == $item['pid']) {
                $item['pname'] = $pname;
                $children = self::getTreeRegion($data, $item['cid'], $item['name']);
                $children && $item['children'] = $children;
                $tree[] = $item;
            }
        }
        return $tree;
    }

}
