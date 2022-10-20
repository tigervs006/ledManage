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
namespace app\model\article;

use core\basic\BaseModel;
use app\model\system\Record;
use app\model\channel\Channel;
use app\model\product\Product;
use think\model\relation\HasOne;

class Article extends BaseModel
{
    protected $pk = 'id';

    // 只读字段
    protected $readonly = ['create_time'];

    /**
     * 关联推送记录
     * @return HasOne
     */
    public function record(): HasOne
    {
        return $this->hasOne(Record::class, 'aid', 'id');
    }

    /**
     * 关联商品模型
     * @return HasOne
     */
    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'id');
    }

    /**
     * 关联内容模型
     * @return HasOne
     */
    public function content(): HasOne
    {
        return $this->hasOne(ArticleContent::class, 'aid', 'id')->withoutField('delete_time');
    }

    /**
     * 关联栏目模型
     * @return HasOne
     */
    public function channel(): HasOne
    {
        return $this->hasOne(Channel::class, 'id', 'cid')->field('id, name, cname, dirname, fullpath');
    }
}
