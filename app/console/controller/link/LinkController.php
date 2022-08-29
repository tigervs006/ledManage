<?php
declare (strict_types = 1);
namespace app\console\controller\link;

use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use app\services\link\LinkServices;
use think\exception\ValidateException;

class LinkController extends BaseController
{
    /**
     * @var LinkServices
     */
    private LinkServices $services;

    private string $validator = 'app\console\validate\LinkValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(LinkServices::class);
    }

    /**
     * 获取友链列表
     * @return Json
     */
    final public function list(): Json
    {
        $whereLike = [];
        $betweenTime = [];
        /** 获取搜索条件 */
        $map = $this->request->only(['status'], 'get', 'trim');
        /** 获取搜索标题 */
        $name = $this->request->get('name/s', null, 'trim');
        /** 获取时间范围 */
        $dateRange = $this->request->only(['dateRange'], 'get', 'trim');
        /** 获取联络方式 */
        $contact = $this->request->get('contact/s', null, 'trim');
        /** 获取排序条件 */
        $order = $this->request->only(['id', 'sort', 'create_time'], 'get', 'strOrderFilter');
        /** 组装文章标题搜索条件 */
        $name && array_push($whereLike, ['name', '%' . $name . '%']);
        /** 组装联系方式搜索条件 */
        $contact && array_push($whereLike, ['contact', '%' . $contact . '%']);
        /** 组装按时间段搜索条件 */
        $dateRange && $betweenTime = ['create_time', $dateRange['dateRange'][0], $dateRange['dateRange'][1]];
        /** 获取友情链接列表 */
        $list = $this->services->getList($this->current, $this->pageSize, $map ?: null, '*', $order ?: $this->order, $betweenTime, $whereLike);

        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($map ?: null, null, $betweenTime, $whereLike);
            return $this->json->successful(compact('list', 'total'));
        }
    }

    final public function save(): Json
    {
        $post = $this->request->only(
            [
                'id',
                'url',
                'sort',
                'name',
                'status',
                'contact',
                'description',
            ], 'post', 'trim'
        );
        $message = '新增';
        /* 释放由EditableProTable随机生成的字符串id */
        if (isset($post['id']) && is_numeric($post['id'])) {
            $message =  '编辑';
        } else {
            unset($post['id']);
        }
        /* 验证数据 */
        try {
            $this->validate($post, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        /* 保存数据 */
        $this->services->saveLink($post, $message);
        return $this->json->successful($message . '友情链接成功');
    }

    /**
     * 单个/批量删除
     * @return Json
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除友链失败');
        });
        return $this->json->successful('删除友链成功');
    }

    /**
     * 设置友情链接状态
     * @return Json
     */
    final public function setStatus(): Json
    {
        $status = $this->request->post(['status']);
        $message = $status['status'] ? '启用' : '禁用';
        $this->services->updateOne($this->id, $status);
        return $this->json->successful($message . '友情链接成功');
    }
}
