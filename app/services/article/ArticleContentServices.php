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
namespace app\services\article;

use app\services\BaseServices;
use app\dao\article\ArticleContentDao;

/**
 * Class ArticleContentServices
 * @package app\services\article
 * @method \core\basic\BaseModel saveOne(array $data) 新增内容
 * @method \core\basic\BaseModel updateOne(int|array|string $id, array $data, ?string $key = null) 更新内容
 */
class ArticleContentServices extends BaseServices
{
    /**
     * 构造函数
     * @param ArticleContentDao $dao
     */
    public function __construct(ArticleContentDao $dao)
    {
        $this->dao = $dao;
    }
}
