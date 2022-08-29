<?php
declare (strict_types = 1);
namespace app\services\product;

use app\services\BaseServices;
use app\dao\product\ProductDao;
use core\exceptions\ApiException;

class ProductServices extends BaseServices
{
    private ProductDetailServices $productDetailServices;

    public function __construct(ProductDao $dao)
    {
        $this->dao = $dao;
        $this->productDetailServices = app()->make(ProductDetailServices::class);
    }

    /**
     * 删除商品
     * @return void
     * @param int|array|string $id
     */
    public function remove(int|array|string $id): void
    {
        $this->transaction(function () use ($id) {
            $result = $this->dao->delete($id) && $this->productDetailServices->delete($id);
            !$result && throw new ApiException('删除商品失败');
        });
    }

    /**
     * 新增/编辑商品
     * @return void
     * @param array $data
     * @param string $message
     */
    public function saveProduct(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        $content = $data['content'];
        unset($data['id'], $data['content']);
        15 < count(explode(',', $data['keywords']))
        && throw new ApiException('商品关键词不得超过15个');
        $this->transaction(function () use ($id, $data, $content, $message) {
            if ($id) {
                $info = $this->dao->updateOne($id, $data, 'id');
                $res = $info && $this->productDetailServices->updateOne($id, ['content' => $content], 'gid');
            } else {
                $info = $this->dao->saveOne($data);
                $res = $info && $this->productDetailServices->saveOne(['gid' => $info->id, 'content' => $content]);
            }
            !$res && throw new ApiException($message . '商品失败');
        });
    }
}
