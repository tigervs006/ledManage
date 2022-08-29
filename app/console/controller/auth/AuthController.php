<?php
declare (strict_types = 1);
namespace app\console\controller\auth;

use think\facade\Route;
use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use app\services\auth\AuthServices;
use think\exception\ValidateException;

class AuthController extends BaseController
{
    private AuthServices $services;

    private string $validator = 'app\console\validate\MenuValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(AuthServices::class);
    }

    /**
     * 获取菜单列表
     * @return Json
     */
    final public function list(): Json
    {
        $map = $this->request->only(
            [
                'status',
                'hideInMenu',
                'hideChildrenInMenu'
            ], 'get', 'trim');
        $data = $this->services->getData($map ?: null, ['id' => 'asc']);
        $list = $this->services->getTreeMenu($data);
        return $list ? $this->json->successful(compact('list')) : $this->json->fail();
    }

    /**
     * 新增/编辑菜单
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->only([
            'id',
            'pid',
            'name',
            'icon',
            'sort',
            'type',
            'paths',
            'routes',
            'locale',
            'status',
            'hideInMenu',
            'hideChildrenInMenu',
        ], 'post', 'trim');
        $message = '新增'; // 设置message的默认值
        // 释放由EditableProTable随机生成的字符串id
        if (isset($post['id']) && is_numeric($post['id'])) {
            $message =  '编辑';
        } else {
            unset($post['id']);
        }
        // 验证必要数据
        try {
            $this->validate($post, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        $this->services->saveMenu($post, $message);
        return $this->json->successful($message . '菜单成功');
    }

    /**
     * 删除菜单
     * @return Json
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除菜单失败');
        });
        return $this->json->successful('删除菜单成功');
    }

    /**
     * 设置菜单状态
     * @return Json
     */
    final public function setStatus(): Json
    {
        $data = $this->request->only(
            [
                'status',
                'hideInMenu',
                'hideChildrenInMenu'
            ], 'post', 'trim'
        );

        /* 隐藏菜单参数始终取反值 */
        isset($data['hideInMenu']) && $data['hideInMenu'] = (int) !$data['hideInMenu'];
        isset($data['hideChildrenInMenu']) && $data['hideChildrenInMenu'] = (int) !$data['hideChildrenInMenu'];

        $this->services->updateOne($this->id, $data);
        return $this->json->successful('设置菜单成功');
    }

    /**
     * 获取路由列表
     * @return json
     */
    public function getRouteList(): Json
    {
        $list = [];
        /* 获取所有的路由规则 */
        $ruleList = Route::getRuleList();
        foreach ($ruleList as $item) {
            $item['method'] = strtoupper($item['method']);
            $item['route_name'] = $item['option']['route_name'] ?? '未定义';
            unset($item['option'], $item['pattern'], $item['domain'], $item['name'], $item['route']);
            $list[] = $item;
        }
        return $this->json->successful(compact('list'));
    }
}
