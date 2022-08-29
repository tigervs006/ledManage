<?php
declare (strict_types = 1);
namespace app\console\controller\system;

use think\facade\Cache;
use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use think\exception\ValidateException;
use app\services\system\RegionServices;

class RegionController extends BaseController
{

    private RegionServices $services;

    private string $validator = 'app\console\validate\RegionValidator';

    private string $field = 'id, cid, pid, code, name, status, create_time';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(RegionServices::class);
    }

    /**
     * 获取地区列表
     * @return Json
     */
    final public function index(): Json
    {
        $pid = $this->request->get('pid/d', 0);
        $list = $this->services->getChildCity($pid, $this->field);
        return $this->json->successful(compact('list'));
    }

    /**
     * 获取地区树状列表
     * @return Json
     */
    final public function list(): Json
    {
        $list = Cache::remember('region', function () {
            $data = $this->services->getData($this->status, ['id' => 'asc']);
            return $data ? $this->services->getTreeRegion($data) : null;
        }, 3600 * 24 * 7);
        return $this->json->successful(compact('list'));
    }

    /**
     * 删除地区
     * @return Json
     */
    final public function delete(): Json
    {
        $this->services->remove($this->id);
        return $this->json->successful('删除地区成功');
    }

    /**
     * 新增/编辑
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->only(
            [
                'id',
                'pid',
                'code',
                'name',
            ], 'post', 'trim'
        );
        // 验证必要数据
        try {
            $this->validate($post, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        $message = '新增';
        $post['level'] = !$post['pid']
            ? $post['pid']
            : $this->services->value(['cid' => $post['pid']], 'level') + 1;
        $post['merger'] = !$post['pid']
            ? $post['name']
            : $this->services->value(['cid' => $post['pid']], 'name') . ',' . $post['name'];
        if (isset($post['id']) && is_numeric($post['id'])) {
            $message =  '编辑';
        } else {
            unset($post['id']);
            $post['cid'] = intval($this->services->getCityIdMax() + 1);
        }
        $this->services->saveRegion($post, $message);
        return $this->json->successful($message . '地区成功', compact('post'));
    }
}
