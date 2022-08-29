<?php
declare (strict_types = 1);
namespace app\services\channel;

use app\services\BaseServices;
use app\dao\channel\ChannelDao;
use core\exceptions\ApiException;

class ChannelServices extends BaseServices
{
    public function __construct(ChannelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 单个/批量删除
     * @return void
     * @param int|array|string $id
     */
    public function remove(int|array|string $id): void
    {
        $this->transaction(function () use ($id) {
            $this->dao->delete($id);
        });
    }

    /**
     * 新增/编辑栏目
     * @return void
     * @param array $data 数据
     * @param string $message 新增/编辑
     */
    public function saveChannel(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); // 释放$data中的id
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '栏目失败');
        });
    }

    /**
     * 获取父栏目信息
     * @return array
     * @param array $info 栏目信息
     * @param string $field 栏目字段
     */
    public function getParentInfo(array $info, string $field): array
    {
        static $infoArr = [];
        foreach ($info as $val) {
            if ($val['pid']) {
                // 查找父级栏目
                $pinfo = $this->dao->getOne(array_merge(['id' => $val['pid']], $this->status), $field)->toArray();
                $pinfo['path'] = ''; // 生成path键值用于组合栏目url时用
                $pinfo && self::getParentInfo(array($pinfo), $field);
                $pinfo && $infoArr[] = $pinfo;
            }
        }
        // 父级栏目可能是顶级栏目没有pid，需合并
        return array_merge($infoArr, $info);
    }

    /**
     * 整理组合栏目URL
     * @return array
     * @param array $data 数据
     * @param int $pid 父栏目id
     */
    public function getParentCrumbs(array $data, int $pid = 0): array
    {
        static $arrPath = [];
        static $separation = '/';
        foreach ($data as $val) {
            if ($val['pid'] == $pid) {
                $val['path'] = $separation .= $val['name'] . '/';
                self::getParentCrumbs($data, $val['id']);
                $arrPath[] = $val;
            }
        }
        // 根据pid实现升序排序
        array_multisort(array_column($arrPath, 'pid'), SORT_ASC, $arrPath);
        return $arrPath;
    }
}
