<?php
declare (strict_types = 1);
namespace app\index\controller;

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
    private string $article_field = 'id, title, click, litpic, author, is_head, create_time, description';

    protected function initialize()
    {
        parent::initialize();
        $this->regionServices = $this->app->make(RegionServices::class);
        $this->articleServices = $this->app->make(ArticleServices::class);
    }

    /**
     * 系统环境
     * @return string
     */
    final public function info(): string
    {
        $content = phpinfo(INFO_MODULES);
        return $this->view::display((string) $content);
    }

    /**
     * 文章列表
     * @return string
     */
    final public function index(): string
    {
        $hotArt = $this->articleServices->getList(1, 7, $this->status, $this->article_field, ['is_head' => 'desc', 'id' => 'desc']);
        return $this->view::fetch('/index', ['hotart' => $hotArt]);
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
