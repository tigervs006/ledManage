<?php
declare (strict_types = 1);
namespace app\console\controller\system;

use think\response\Json;
use core\basic\BaseController;
use core\services\UploadService;
use core\exceptions\UploadException;
use app\services\system\AttachServices;
use app\services\system\AttachCateServices;

class AttachController extends BaseController
{
    /**
     * @var AttachServices
     */
    private AttachServices $services;

    /**
     * @var AttachCateServices
     */
    private AttachCateServices $cateServices;

    /**
     * @var \core\services\upload\Upload
     */
    private \core\services\upload\Upload $storage;

    public function initialize()
    {
        parent::initialize();
        $this->storage = UploadService::init();
        $this->services = $this->app->make(AttachServices::class);
        $this->cateServices = $this->app->make(AttachCateServices::class);
    }

    /**
     * 文件列表
     * @return Json
     */
    final public function list(): Json
    {
        /* 获取所有目录 */
        $cateData = $this->cateServices->getData();
        /* 查找子目录id */
        $ids = $this->services->getChildrenIds($cateData, $this->id);
        $map = array('pid' => array_merge(array((int) $this->id), $ids));
        $list = $this->services->getList($this->current, $this->pageSize, $map ?? null);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($map ?? null);
            return $this->json->successful(compact('total', 'list'));
        }
    }

    /**
     * 文件上传
     * @return Json
     * @throws \Exception
     */
    final public function upload(): Json
    {
            $params = $this->request->only(
                [
                    'pid' => 0,
                    'path' => 'attach',
                ], 'post', 'trim');
            try {
                $this->validate(
                    $params,
                    ['pid' => 'require|integer', 'path' => 'require|regex:[\w\/]+'],
                    [
                        'pid.require'       => '请选择文件所属目录',
                        'pid.integer'       => '文件所属目录ID必须是正整数',
                        'path.require'      => '请设置文件上传路径',
                        'path.regex'        => '文件上传路径只能是字母、数字和下划线及破折号的组合',
                    ]
                );
            } catch (\think\exception\ValidateException $e) {
                throw new UploadException($e->getError());
            }
            /* 组装文件路径 */
            $path = $params['path'] . '/' . date('Y-m-d');
            $fileInfo = $this->storage->to($path)->validate()->move();
            !$fileInfo && throw new UploadException($this->storage->getError());
            $fileInfo && $attach = [
                'type'      => $fileInfo['type'],
                'name'      => $fileInfo['name'],
                'static_path' => $fileInfo['url'],
                'pid'       => (int) $params['pid'],
                'storage'   => $fileInfo['storage'],
                'real_path' => $fileInfo['realPath'],
                'path'      => $fileInfo['relativePath'],
            ];
            /* 写入到附件表 */
            $fileInfo && $this->services->saveOne($attach);
            return $this->json->successful('uploaded done', $fileInfo);
    }

    /**
     * 删除文件
     * @return Json
     */
    final public function remove(): Json
    {
        $post = $this->request->post('attach');
        array_walk($post, function ($val) {
            /* 从存储中删除文件 */
            UploadService::init($val['storage'])->delete($val['path']);
            /* 从数据库删除记录 */
            $this->services->delete($val['id'], 'id', true);
        });
        return $this->json->successful('File deleted successfully');
    }

    /**
     * 批量移动文件
     * @return Json
     */
    final public function moveCate(): Json
    {
        $post = $this->request->only(
            [
                'id',
                'pid',
            ], 'post', 'trim'
        );
        $data = ['pid' => $post['pid']];
        $result = $this->services->batchUpdate($post['id'], $data, 'id');
        return $result ? $this->json->successful('File move category successfully') : $this->json->fail();
    }

    /**
     * 二进制上传
     * @return Json
     */
    final public function upStream(): Json
    {
        try {
            $content = file_get_contents("php://input");
            $params = $this->request->only(['pid' => 0], 'post', 'trim');
            $fileInfo = $this->storage->to('attach/' . date('Y-m-d'))->validate()->stream($content);
            $attach = [
                'type'      => $fileInfo['type'],
                'name'      => $fileInfo['name'],
                'static_path' => $fileInfo['url'],
                'pid'       => (int) $params['pid'],
                'storage'   => $fileInfo['storage'],
                'real_path' => $fileInfo['realPath'],
                'path'      => $fileInfo['relativePath'],
            ];
            /* 写入到附件表 */
            $this->services->saveOne($attach);
            return $fileInfo ? $this->json->successful('uploaded done', $fileInfo) : $this->json->fail('upload failed');
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }
}
