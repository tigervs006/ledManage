<?php
declare (strict_types = 1);
namespace app\index\controller;

use think\Collection;
use core\basic\BaseController;
use app\services\channel\ChannelServices;
use app\services\article\ArticleServices;

class Industry extends BaseController
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
        $this->view::assign('hotart', $this->hortArt()); // 获取热门文章
        $this->channelServices = $this->app->make(ChannelServices::class);
    }

    /**
     * 文章内容
     * @return string
     */
    final public function index(): string
    {
        $map = array(['status', '=', 1], ['cid', 'in', '35,36']);
        $info = $this->services->get($this->id, null, ['content']);
        // 阅读量自增
        $info && $this->services->setInc($info['id'], $this->incValue);
        // 上/下一篇文章
        $prenext = $this->services->getPrenext($info['id'], $map, 'id, cid, title');
        return $this->view::fetch('../industry/index', compact('info', 'prenext'));
    }

    /**
     * 文章列表
     * @return string
     * @throws \Throwable
     */
    final public function list(): string
    {
        /* 获取当前栏目信息 */
        $info = $this->channelServices->listInfo();
        $map = array_merge($this->status, ['cid' => $info['ids']]);
        $list = $this->services->getPaginate($map, $this->current, $this->pageSize, $info['fullpath'], $this->field, $this->order, ['channel']);
        return $this->view::fetch('../industry/list', compact('list'));
    }

    /**
     * 热门文章
     * @return array|Collection
     */
    final public function hortArt(): array|Collection
    {
        return $this->services->getList(
            1,
            10,
            array(['status', '=', 1], ['cid', 'in', '35,36']),
            'id, cid, click, title, litpic, create_time',
            ['click' => 'desc'], null, null, ['channel']);
    }
}
