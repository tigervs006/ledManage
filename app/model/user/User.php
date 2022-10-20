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
namespace app\model\user;

use core\basic\BaseModel;
use app\model\auth\GroupModel;
use think\model\relation\HasOne;

class User extends BaseModel
{
    protected $pk = 'id';

    /**
     * 关联token模型
     * @return HasOne
     */
    public function token(): HasOne
    {
        return $this->hasOne(JwtToken::class, 'uid', 'id')->field('uid, user');
    }

    /**
     * 关联用户组模型
     * @return HasOne
     */
    public function group(): HasOne
    {
        return $this->hasOne(GroupModel::class, 'id', 'gid')->field('id, name');
    }
}
