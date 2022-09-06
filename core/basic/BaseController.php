<?php
declare (strict_types = 1);
namespace core\basic;

use think\App;
use app\Request;
use think\Validate;
use think\facade\View;
use think\response\Redirect;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * 应用实例
     * @var App
     */
    protected App $app;

    /**
     * 界面渲染
     * @var View
     */
    protected View $view;

    /**
     * @var object
     */
    protected object $json;

    /**
     * 当前页
     * @var int
     */
    protected int $current;

    /**
     * 每页数量
     * @var int
     */
    protected int $pageSize;

    /**
     * Request实例
     * @var Request
     */
    protected Request $request;

    /**
     * 阅读量步长
     * @var int
     */
    protected int $incValue = 1;

    /**
     * 重定向
     * @var Redirect
     */
    protected Redirect $redirect;

    /**
     * @var int|array|string
     */
    protected int|array|string $id;

    /**
     * 批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 默认状态
     * @var array|int[]
     */
    protected array $status = ['status' => 1];

    /**
     * 默认排序
     * @var array|string[]
     */
    protected array $order = ['id' => 'desc'];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app, View $view, Request $request, Redirect $redirect)
    {
        $this->app     = $app;
        $this->view    = $view;
        $this->request = $request;
        $this->redirect = $redirect;
        // 初始化
        $this->initialize();
    }

    /**
     * initialize
     */
    protected function initialize()
    {
        $this->json = App('json');
        $this->id = $this->request->param('id', 0, 'trim');
        $this->current = $this->request->param('current/d', 1, 'trim');
        $this->pageSize = $this->request->param('pageSize/d', 15, 'trim');

        /* 如果是index应用 */
        if ('index' === App('http')->getName()) {
            $this->link();
            $this->channel();
        }
    }

    /**
     * 前端友链
     * @return void
     */
    private function link(): void
    {
        /** @var \app\services\link\LinkServices  $services */
        $services = $this->app->make(\app\services\link\LinkServices::class);
        $link = $services->getData($this->status, ['sort' => 'desc', 'id' => 'desc'], 'name, url');
        $this->view::assign(compact('link'));
    }

    /**
     * 网站栏目
     * &面包屑导航
     * @return void
     */
    private function channel(): void
    {
        /** @var \app\services\channel\ChannelServices $services */
        $services = $this->app->make(\app\services\channel\ChannelServices::class);
        $pathArr = explode('/', $this->request->pathinfo());
        // 过滤空值数组
        $pathFilter = array_filter($pathArr);
        // 最后一个数组值作为当前栏目名
        $channel = end($pathFilter);
        if ($channel) {
            $value = '';
            $key = 'name';
            // 以下字段在获取栏目SEO信息及获取面包屑导航都需要用到
            static $field = 'id, pid, name, title, cname, keywords, description';
            if (preg_match('/\d+/', $channel, $pathDetail)) { // 如果是详情页
                $key = 'id';
                /** @var \app\services\article\ArticleServices $artServices */
                $artServices = $this->app->make(\app\services\article\ArticleServices::class);
                $value = $artServices->value(['id' => $pathDetail[0]], 'cid'); // 获取父级栏目ID
            } else if (preg_match('/[a-zA-Z]+/', $channel, $pathCategory)) { // 如果是栏目页
                $value = $pathCategory[0];
            }
            // 获取当前栏目信息
            $pinfo = $services->getOne(array_merge([$key => $value], $this->status), $field);
            if ($pinfo) {
                $pinfoArr = $pinfo->toArray();
                // 获取父级栏目信息
                $pdata = $services->getParentInfo(array($pinfoArr), $field);
                // 通过父级栏目信息生成面包屑导航
                $crumbsData = $services->getParentCrumbs($pdata);
            }
        }
        // 获取所有栏目数据
        $channelData = $services->getData($this->status, ['id' => 'asc', 'sort' => 'desc'], 'id, pid, name, cname');
        // 获取网站栏目树状结构
        $result = $services->getTreeData($channelData, 0, null);
        $this->view::assign(['channel' => $result, 'crumbs' => $crumbsData ?? [], 'channelinfo' => $pinfo ?? []]);
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param array|string $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return bool
     */
    protected function validate(array $data, array|string $validate, array $message = [], bool $batch = false): bool
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 空控制器
     * @return string
     */
    public function __call($method, $args)
    {
        return "Method $method doesn't exist";
    }
}
