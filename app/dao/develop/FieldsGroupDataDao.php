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

declare (strict_types=1);
namespace app\dao\develop;

use app\dao\BaseDao;
use app\model\develop\FieldsGroupData;

/**
 * class FieldsGroupDataDao
 * @createAt 2022/10/30 20:28
 * @package app\dao\develop
 */
class FieldsGroupDataDao extends BaseDao
{

    /**
     * @inheritDoc
     */
    protected function setModel(): string
    {
        return FieldsGroupData::class;
    }
}
