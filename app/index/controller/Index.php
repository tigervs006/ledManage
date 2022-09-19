<?php
declare (strict_types = 1);
namespace app\index\controller;

use think\Collection;
use think\response\Json;
use core\basic\BaseController;
use app\services\system\RegionServices;
use app\services\article\ArticleServices;

class Index extends BaseController
{
    /**
     * @var RegionServices
     */
    private RegionServices $regionServices;

    /**
     * @var ArticleServices
     */
    private ArticleServices $articleServices;

    /**
     * @var string 区域字段
     */
    private string $region_field = 'id, cid, pid, code, name';

    /**
     * @var string 文档字段
     */
    private string $article_field = 'id, cid, title, click, litpic, author, is_head, create_time, description';

    protected function initialize()
    {
        parent::initialize();
        $this->regionServices = $this->app->make(RegionServices::class);
        $this->articleServices = $this->app->make(ArticleServices::class);
    }

    /**
     * 首页数据
     * @return string
     */
    final public function index(): string
    {
        return $this->view::fetch('/index', ['hotart' => $this->getArticle(), 'product' => $this->getProduct()]);
    }

    /**
     * 网站地图
     * @return string
     */
    final public function sitemap(): string
    {
        return $this->view::fetch('../sitemap');
    }

    /**
     * 获取商品列表
     * @return Collection
     */
    private function getProduct(): Collection
    {
        return $this->articleServices->getList(
            1,
            20,
            array(['status', '=', 1], ['cid', 'notin', '35,36']),
            $this->article_field,
            ['click' => 'desc', 'id' => 'desc'],
            null, null, ['channel']
        );
    }

    /**
     * 获取文档列表
     * @return array|Collection
     */
    private function getArticle(): array|Collection
    {
        $list = $this->articleServices->getList(
            1,
            36,
            array_merge($this->status, ['cid' => [35, 36]]),
            $this->article_field,
            ['is_head' => 'desc', 'id' => 'desc'],
            null, null, ['channel']
        );
        $result = [];
        if (!$list->isEmpty()) {
            $Arr = $this->app->make(\core\utils\ArrHandler::class);
            $list = $Arr->ArrMultisort($list->toArray(), 'click', 'DESC');
            $result = $Arr->ArrToGroup($list, 'cid'); /* 根据文档分类分组 */
        }
        return empty($result) ? [] : array_merge(array($list), $result);
    }

    /**
     * 行政区域
     * @return Json
     */
    final public function region(): Json
    {
        $pid = $this->request->get('pid/d', 0);
        $list = $this->regionServices->getChildCity($pid, $this->region_field);
        return $this->json->successful(compact('list'));
    }
}
