<?php
declare (strict_types = 1);
namespace app\console\controller\user;

use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use app\services\user\ClientServices;
use think\exception\ValidateException;
use app\services\system\RegionServices;

class ClientController extends BaseController
{
    /**
     * @var ClientServices
     */
    private ClientServices $services;

    /**
     * @var RegionServices
     */
    private RegionServices $regionServices;

    private string $validator = 'app\console\validate\FormValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ClientServices::class);
        $this->regionServices = $this->app->make(RegionServices::class);
    }

    /**
     * 获取客户列表
     * @return Json
     */
    final public function list(): Json
    {
        /** 模糊搜索 */
        $whereLike = [];
        /** 获取时间范围 */
        $betweenTime = [];
        /** 获取筛选条件 */
        $map = $this->request->only(['source'], 'get', 'trim');
        /** 获取时间段 */
        $dateRange = $this->request->only(['dateRange'], 'get', 'trim');
        /** 获取手机号码 */
        $mobile = $this->request->get('mobile/d', null, 'trim');
        /** 获取搜索用户 */
        $username = $this->request->get('username/s', null, 'trim');
        /** 获取排序条件 */
        $order = $this->request->only(['create_time'], 'get', 'strOrderFilter');
        /** 组装手机号搜索条件 */
        $mobile && array_push($whereLike, ['mobile', '%' . $mobile . '%']);
        /** 组装用户名搜索条件 */
        $username && array_push($whereLike, ['username', '%' . $username . '%']);
        /** 组装按时间段搜索条件  */
        $dateRange && $betweenTime = ['create_time', $dateRange['dateRange'][0], $dateRange['dateRange'][1]];

        $list = $this->services->getList($this->current, $this->pageSize, $map ?: null, '*', $order, $betweenTime, $whereLike);

        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($map ?: null, null, $betweenTime, $whereLike);
            return $this->json->successful(compact('list', 'total'));
        }
    }

    /**
     * 新增/编辑客户
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->only([
            'id',
            'city',
            'email',
            'mobile',
            'company',
            'message',
            'province',
            'username',
            'district',
        ], 'post', 'trim');

        /** 验证关键数据 */
        try {
            $this->validate($post, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }

        $message = '编辑';
        if (empty($post['id'])) {
            $message = '新增';
            $post['source'] = 0;
            $post['page'] = $this->request->url();
            $post['ipaddress'] = ip2long($this->request->ip());
        }
        /** 拼接省市区地址 */
        $city_name = $this->regionServices->value(['cid' => $post['city']], 'name');
        $district_name = $this->regionServices->value(['cid' => $post['district']], 'name');
        $province_name = $this->regionServices->value(['cid' => $post['province']], 'name');
        $post['address'] = "{$province_name}，{$city_name}，{$district_name}";

        $this->services->saveClient($post, $message . '客户失败');

        return $this->json->successful($message . '客户成功');
    }

    /**
     * 单个/批量删除
     * @return Json
     */
    final public function delete(): Json
    {
        $res = $this->services->delete($this->id);
        return !$res ? $this->json->fail('删除客户失败') : $this->json->successful('删除客户成功');
    }
}
