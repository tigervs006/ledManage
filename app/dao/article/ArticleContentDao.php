<?php
declare (strict_types = 1);
namespace app\dao\article;

use app\dao\BaseDao;
use app\model\article\ArticleContent;

class ArticleContentDao extends BaseDao
{
    public function setModel(): string
    {
        return ArticleContent::class;
    }
}