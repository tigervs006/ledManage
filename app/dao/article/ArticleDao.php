<?php
declare (strict_types = 1);
namespace app\dao\article;

use app\dao\BaseDao;
use app\model\article\Article;

class ArticleDao extends BaseDao
{
    public function setModel(): string
    {
        return Article::class;
    }
}
