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
namespace app\dao\auth;

use app\dao\BaseDao;
use app\model\auth\AuthModel;
use core\exceptions\ApiException;
use think\db\exception\DbException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

class AuthDao extends BaseDao
{

    protected function setModel(): string
    {
        return AuthModel::class;
    }

    /**
     * 查询用户菜单
     * @return \think\Collection
     * @param string $ids ids
     * @param null|array $where 条件
     */
    public function queryMenu(string $ids, ?array $where = []): \think\Collection
    {
        try {
            return $this->getModel()->whereIn('id', $ids)->when(count($where), function ($query) use ($where) {
                $query->where($where);
            })->order('id', 'asc')->select();
        } catch (DataNotFoundException|ModelNotFoundException|DbException $e) {
            throw new ApiException($e->getMessage());
        }
    }
}
