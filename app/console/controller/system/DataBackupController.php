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

use think\facade\Cache;
use think\response\Json;
use core\basic\BaseController;
use think\db\exception\BindParamException;
use app\services\system\DataBackupServices;

class DataBackupController extends BaseController
{
    /**
     * @var null|array|string
     */
    private null|array|string $tables;

    /**
     * @var DataBackupServices
     */
    private DataBackupServices $services;

    public function initialize()
    {
        parent::initialize();
        $this->tables = $this->request->post('tables');
        $this->services = $this->app->make(DataBackupServices::class);
    }

    /**
     * 读取列表
     * @return Json
     */
    final public function index(): Json
    {
        $list = $this->services->getDataList();
        return $this->json->successful($list);
    }

    /**
     * 查看表结构
     * @return Json
     * @param string $tablename
     */
    final public function read(string $tablename): Json
    {
        $list = $this->services->getRead($tablename);
        return $this->json->successful($list);
    }

    /**
     * 优化数据表
     * @return Json
     * @throws \Exception
     */
    final public function optimize(): Json
    {
        $this->services->getDbBackup()->optimize($this->tables);
        return $this->json->successful('优化成功');
    }

    /**
     * 修复数据表
     * @return Json
     * @throws BindParamException
     */
    final public function repair(): Json
    {
        $res = $this->services->getDbBackup()->repair($this->tables);
        return 'OK' === $res[0]['Msg_text'] ? $this->json->successful('修复成功') : $this->json->fail($res[0]['Msg_text']);
    }

    /**
     * 备份数据表
     * @return Json
     * @throws BindParamException
     */
    final public function backup(): Json
    {
        $res = $this->services->backup($this->tables);
        return $res ? $this->json->fail('数据备份失败' . $res) : $this->json->successful('数据备份成功');
    }

    /**
     * 获取备份记录
     * @return Json
     */
    final public function record(): Json
    {
        $list = $this->services->getBackup();
        return empty($list['list']) ? $this->json->fail() : $this->json->successful($list);
    }

    /**
     * 删除备份记录
     * @return Json
     * @throws \Exception
     */
    public function delete(): Json
    {
        $post = ['filename/d', null, 'intval'];
        $filename = $this->request->post(...$post);
        $this->services->getDbBackup()->delFile($filename);
        return $this->json->successful('删除备份记录成功...');
    }

    /**
     * 恢复备份记录
     * @return Json
     * @throws \Exception
     */
    public function import(): Json
    {
        $param = $this->request->only(
            [
                'gz',
                'part'  => 0,
                'time'  => 0,
                'start' => 0,
            ], 'post', 'intval');
        $db = $this->services->getDbBackup();
        if (is_numeric($param['time']) && !$param['start']) {
            $list = $db->getFile('timeverif', $param['time']);
            if (is_array($list)) {
                Cache::set('backup_list', $list, 300);
                return $this->json->successful('初始化完成！', array('part' => 1, 'start' => 1));
            } else {
                return $this->json->fail('备份文件可能已经损坏');
            }
        } else if (is_numeric($param['part']) && is_numeric($param['start'])) {
            $list = Cache::get('backup_list');
            $start = $db->setFile($list)->import($param['start']);
            if (false === $start) {
                return $this->json->fail('还原数据出错！');
            } elseif (0 === $start) {
                if (isset($list[++$param['part']])) {
                    $data = array('part' => $param['part'], 'start' => 0);
                    return $this->json->successful("正在还原...#{$param['part']}", $data);
                } else {
                    Cache::delete('backup_list');
                    return $this->json->successful('数据还原完成！');
                }
            } else {
                $data = array('part' => $param['part'], 'start' => $start[0]);
                if ($start[1]) {
                    $rate = floor(100 * ($start[0] / $start[1]));
                    return $this->json->successful("正在还原...#{$param['part']}({$rate}%)", $data);
                } else {
                    $data['gz'] = 1;
                    return $this->json->successful("正在还原...#{$param['part']}", $data);
                }
            }
        } else {
            return $this->json->fail('参数错误！');
        }
    }

    /**
     * 下载备份记录
     * @return Json
     * @throws \Exception
     */
    public function download(): Json
    {
        $param = $this->request->only(
            [
                'time',
                'part' => 0,
                'isFile' => 1,
            ], 'get', 'intval');
        $key = $this->services->getDbBackup()->downloadFile(...$param);
        return $this->json->successful(compact('key'));
    }
}
