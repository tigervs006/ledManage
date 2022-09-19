<?php
declare (strict_types = 1);
namespace app\index\controller;

use think\Collection;
use core\basic\BaseController;
use app\services\channel\ChannelServices;
use app\services\article\ArticleServices;

class Testing extends BaseController
{
    /**
     * @var ArticleServices
     */
    private ArticleServices $services;

    /**
     * @var ChannelServices
     */
    private ChannelServices $channelServices;

    /**
     * @var string
     */
    private string $field = 'id, cid, click, title, author, litpic, create_time, description';

    protected function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ArticleServices::class);
        $this->view::assign('hotArt', $this->hortArt()); // 获取热门文章
        $this->channelServices = $this->app->make(ChannelServices::class);
    }

    /**
     * 测试内容
     * @return string
     */
    final public function index(): string
    {
        $info = $this->services->get($this->id, null, ['content']);
        // 阅读量自增
        $info && $this->services->setInc($info['id'], $this->incValue);
        // 上 / 下一篇
        $prenext = $this->services->getPrenext($info['id'], null, 'id, cid, title');
        return $this->view::fetch('../testing/index', compact('info', 'prenext'));
    }

    /**
     * 测试列表
     * @return string
     */
    final public function list(): string
    {
        $name = ['name' => getPath()];
        $pid = $this->channelServices->value($name, 'pid');
        is_null($pid) && abort(404, "page doesn't exist");
        $map = !$pid
            ? $this->status
            : array_merge($this->status, ['cid' => $this->channelServices->value($name)]);
        $list = $this->services->getPaginate($map, $this->pageSize, $this->field, $this->order, ['channel']);
        return $this->view::fetch('../testing/list', compact('list'));
    }

    /**
     * 热门文档
     * @return array|Collection
     */
    final public function hortArt(): array|Collection
    {
        return $this->services->getList(
            $this->current,
            $this->pageSize,
            $this->status,
            'id, cid, click, title, litpic, create_time',
            ['click' => 'desc'], null, null, ['channel']);
    }
}