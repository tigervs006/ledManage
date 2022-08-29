<?php
declare (strict_types = 1);
namespace app\console\controller\user;

use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use app\services\auth\AuthServices;
use app\services\user\UserServices;
use app\services\auth\GroupServices;
use think\exception\ValidateException;

class UserController extends BaseController
{
    /**
     * @var UserServices
     */
    private UserServices $services;

    private string $validator = 'app\console\validate\UserValidator.';

    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * @var GroupServices
     */
    private GroupServices $groupServices;

    /**
     * 提取字段
     * @var string
     */
    private string $field = 'id, gid, name, cname, status, email, avatar, mobile, ipaddress, last_login, create_time';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(UserServices::class);
        $this->authServices = $this->app->make(AuthServices::class);
        $this->groupServices = $this->app->make(GroupServices::class);
    }

    /**
     * 获取用户信息
     * @return Json
     */
    final public function index(): Json
    {
        $info = $this->services->getOne(['id' => $this->id], $this->field)->toArray();
        if (!is_null($info)) {
            /* 获取所属用户组权限菜单 */
            $userMenu = $this->groupServices->value(['id' => $info['gid']], 'menu');
            $userAuth = $this->authServices->queryMenu($userMenu);
            foreach ($userAuth as $val) {
                /* 提取用户的按钮权限 */
                2 == $val['type'] && $info['btnRules'][] = $val['name'];
            }
        }
        return is_null($info) ? $this->json->fail() : $this->json->successful(compact('info'));
    }

    /**
     * 新增/编辑用户
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->only(
            [
                'id',
                'gid',
                'name',
                'scene',
                'cname',
                'email',
                'mobile',
                'avatar',
                'password',
                'oldPassword',
                'confirmPassword'
            ], 'post', 'trim');
        // 过滤空值字段
        $data = array_filter($post, function ($val) {
            // 避免过滤0、boolean值
            return !("" === $val || null === $val);
        });
        $scene = isset($data['id']) ? 'edit' : 'save';
        $message = isset($data['id']) ? '编辑' : '新增';
        if (isset($data['id']) && isset($data['scene'])) {
            $scene = $data['scene'];
            if (isset($data['oldPassword'])) {
                $initPassword = $this->services->value(['id' => $data['id']], 'password');
                !password_verify($data['oldPassword'], $initPassword) && throw new ApiException('原密码验证失败');
            }
        }
        // 验证必要数据
        try {
            $this->validate($data, $this->validator . $scene);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        $this->services->saveUser($data, $message);
        return $this->json->successful($message . '用户成功');
    }

    /**
     * 获取用户列表
     * @return Json
     */
    final public function list(): Json
    {
        /** 模糊搜索 */
        $whereLike = [];
        /** 搜索时间段 */
        $betweenTime = [];
        /** 获得map条件 */
        $map = $this->request->only(['gid', 'status'], 'get');
        /** 获取搜索用户 */
        $name = $this->request->get('name/s', null, 'trim');
        /** 获取时间范围 */
        $dateRange = $this->request->only(['dateRange'], 'get', 'trim');
        /** 搜索手机号码 */
        $mobile = $this->request->get('mobile/d', null, 'trim');
        /** 获取排序字段 */
        $order = $this->request->only(['create_time', 'last_login'], 'get', 'strOrderFilter');
        /** 组装用户名搜索条件 */
        $name && array_push($whereLike, ['name', '%' . $name . '%']);
        /** 组装手机号搜索条件 */
        $mobile && array_push($whereLike, ['mobile', '%' . $mobile . '%']);
        /** 组装时间段搜索条件  */
        $dateRange && $betweenTime = ['create_time', $dateRange['dateRange'][0], $dateRange['dateRange'][1]];
        $list = $this->services->getList($this->current, $this->pageSize, $map?: null, $this->field, $order, $betweenTime, $whereLike, ['group']);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            /** 计算数据总量 */
            $total = $this->services->getCount($map ?: null, null, $betweenTime, $whereLike);
            return $this->json->successful(compact('total', 'list'));
        }
    }

    /**
     * 单个/批量删除
     * @return Json
     */
    final public function delete(): Json
    {
        $res = $this->services->delete($this->id);
        return !$res ? $this->json->fail('删除用户失败') : $this->json->successful('删除用户成功');
    }

    /**
     * 用户状态
     * @return Json
     */
    final public function setStatus(): Json
    {
        $post = $this->request->post(['status']);
        $this->services->updateOne($this->id, $post, 'id');
        return $this->json->successful($post['status'] ? '启用成功' : '禁用成功');
    }
}
