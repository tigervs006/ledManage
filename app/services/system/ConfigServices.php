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

use think\facade\Cache;
use app\dao\system\ConfigDao;
use app\services\BaseServices;
use core\exceptions\ApiException;

class ConfigServices extends BaseServices
{
    public function __construct(ConfigDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存数据
     * @return void
     * @param array $data
     */
    public function updateConfig(array $data): void
    {
        foreach ($data as $val) {
            3 === $val['id']
            && 15 < count(explode(',', $val['value']))
            && throw new ApiException('【首页关键词】不得超过15个，否则会被搜索引擎判断为堆砌关键词而被K站');
        }
        $this->dao->batchUpdateAll($data);
        /* 清除前端及后台缓存 */
        Cache::delete('config');
        Cache::delete('index');
    }
}
