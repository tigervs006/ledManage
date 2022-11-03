<?php
/*
 * +----------------------------------------------------------------------------------
 * | https://www.tigervs.com
 * +----------------------------------------------------------------------------------
 * | Email: Kevin@tigervs.com
 * +----------------------------------------------------------------------------------
 * | Copyright (c) Shenzhen Tiger Technology Co., Ltd. 2018~2022. All rights reserved.
 * +----------------------------------------------------------------------------------
 */

declare (strict_types = 1);
namespace app\console\controller\system;

use core\exceptions\ApiException;
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
     * 默认配置
     * @return Json
     * @author Kevin
     * @createAt 2022/10/25 22:24
     */
    final public function default(): Json
    {
        $list = config('upload');
        unset($list['stores']); unset($list['default']);
        return $this->json->successful(compact('list'));
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
                    'pid'       => 0,
                    'path'      => 'attach',
                ], 'post', 'intvals');
            try {
                $this->validate(
                    $params,
                    [
                        'pid' => 'require|integer',
                        'path' => 'require|regex:[\w\/]+'
                    ],
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
            /* 组装上传路径 */
            $path = $params['path'] . '/' . date('Y-m-d');
            /* 获取验证规则 */
            $validator = $this->cateServices->verify($params['pid']);
            $fileInfo = $this->storage->to($path)->validate($validator)->move();
            !$fileInfo && throw new UploadException($this->storage->getError());
            $fileInfo && $attach = [
                'static_path'   => $fileInfo['url'],
                'type'          => $fileInfo['type'],
                'name'          => $fileInfo['name'],
                'pid'           => (int) $params['pid'],
                'storage'       => $fileInfo['storage'],
                'real_path'     => $fileInfo['realPath'],
                'path'          => $fileInfo['relativePath'],
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
        $this->services->destroy($post); /* 真实删除文件 */
        return $this->json->successful('File deleted successfully');
    }

    /**
     * 查找并删除文件
     * @return Json
     * @author Kevin
     * @createAt 2022/11/3 10:03
     */
    final public function findAndDelete(): Json
    {
        $attach = [];
        $path = $this->request->post('path');
        try {
            $this->validate(
                compact('path'),
                ['path' => 'require|array'],
                [
                    'path.require' => '文件路径不得为空',
                    'path.array' => '文件路径类型需为数组',
                ]
            );
        } catch (\think\exception\ValidateException $e) {
            throw new ApiException($e->getMessage());
        }

        foreach ($path as $url) {
            $attach[] = $this->services->getOne(['static_path' => $url], '*');
        }

        $attach && $this->services->destroy($attach);
        return $attach ? $this->json->successful('File deleted successfully') : $this->json->fail("File doesn't exist");
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
        $content = file_get_contents("php://input");
        $ext = $this->request->header('content-ext');
        $pid = $this->request->header('content-pid', '0');
        $path = $this->cateServices->value(['id' => $pid], 'dirname') . '/';
        try {
            $this->validate(
                compact('pid', 'ext'),
                ['pid' => 'require|integer', 'ext' => 'require'],
                [
                    'pid.integer' => '目录id须为正整数',
                    'pid.require' => '请完善需上传的目录',
                    'ext.require' => '请完善文件的扩展名',
                ]
            );
        } catch (\think\exception\ValidateException $e) {
            throw new UploadException($e->getMessage());
        }
        try {
            $fileInfo = $this->storage->to($path . date('Y-m-d'))->stream($content, $ext);
            $attach = [
                'static_path'   => $fileInfo['url'],
                'type'          => $fileInfo['type'],
                'name'          => $fileInfo['name'],
                'storage'       => $fileInfo['storage'],
                'real_path'     => $fileInfo['realPath'],
                'path'          => $fileInfo['relativePath'],
                'pid'           => $headers['content-pid'] ?? 0,
            ];
            /* 写入到附件表 */
            $fileInfo && $this->services->saveOne($attach);
            return $fileInfo ? $this->json->successful('uploaded done', $fileInfo) : $this->json->fail('upload failed');
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }
}
