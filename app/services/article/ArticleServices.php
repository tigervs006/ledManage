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
use app\dao\article\ArticleDao;
use core\exceptions\ApiException;

class ArticleServices extends BaseServices
{
    private ArticleContentServices $articleContentService;

    /**
     * 构造函数
     * @param ArticleDao $dao
     */
    public function __construct(ArticleDao $dao)
    {
        $this->dao = $dao;
        $this->articleContentService = app()->make(ArticleContentServices::class);
    }

    /**
     * 删除文章
     * @return void
     * @param int|array|string $id
     */
    public function remove(int|array|string $id): void
    {
        $this->transaction(function () use ($id) {
            $result = $this->dao->delete($id) && $this->articleContentService->delete($id);
            !$result && throw new ApiException('删除文章失败');
        });
    }

    /**
     * 新增|编辑文章
     * @return void
     * @param array $data
     * @param string $message
     */
    public function saveArticle(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        $content = $data['content'];
        unset($data['id'], $data['content']);
        15 < count(explode(',', $data['keywords']))
        && throw new ApiException('文档关键词不得超过15个');
        $this->transaction(function () use ($id, $data, $content, $message) {
            if ($id) {
                $info = $this->dao->updateOne($id, $data, 'id');
                $res = $info && $this->articleContentService->updateOne($id, ['content' => $content], 'aid');
            } else {
                $info = $this->dao->saveOne($data);
                $res = $info && $this->articleContentService->saveOne(['aid' => $info->id, 'content' => $content]);
            }
            !$res && throw new ApiException($message . '文章失败');
        });
    }
}
