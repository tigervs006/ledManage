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
namespace app\services\system;

use think\facade\Db;
use think\facade\Env;
use app\services\BaseServices;
use core\services\MysqlBackupService;
use think\db\exception\BindParamException;

/**
 * 数据库备份
 * Class SystemDatabackupServices
 * @package app\services\system
 */
class DataBackupServices extends BaseServices
{

    /**
     *
     * @var MysqlBackupService
     */
    protected MysqlBackupService $dbBackup;

    /**
     * 构造方法
     */
    public function __construct()
    {
        $config = array(
            [
                /* 压缩级别 */
                'level'     => 9,
                /* 启用压缩 */
                'compress'  => 1,
                /* 备份路径 */
                'path'      => app()->getRootPath() . 'backup/sqldata/'
            ]
        );
        $this->dbBackup = app()->make(MysqlBackupService::class, $config);
    }

    /**
     * 获取表引擎
     * @return string
     * @param string $tablename
     */
    public function getEngines(string $tablename): string
    {
        return $this->dbBackup->engines($tablename);
    }

    /**
     * 获取数据库列表
     * @return array
     */
    public function getDataList(): array
    {
        $list = $this->dbBackup->dataList();
        foreach ($list as $k => $v) {
            $v['id'] = $k + 1;
            $v['size'] = formatBytes($v['data_length'] + $v['index_length']);
            $list[$k] = $v;
        }
        return ['list' => $list, 'total' => count($list)];
    }

    /**
     * 获取表详情
     * @return array
     * @param string $tablename
     */
    public function getRead(string $tablename): array
    {
        $database = Env::get("database.database");
        $list = Db::query("select * from information_schema.columns where table_name = '" . $tablename . "' and table_schema = '" . $database . "'");
        foreach ($list as $key => $f) {
            $list[$key]['id'] = $key + 1;
            $list[$key]['EXTRA'] = ($f['EXTRA'] == 'auto_increment' ? '是' : ' ');
        }
        return ['list' => $list, 'total' => count($list)];
    }

    /**
     * @return MysqlBackupService
     */
    public function getDbBackup(): MysqlBackupService
    {
        return $this->dbBackup;
    }

    /**
     * 备份表
     * @return string
     * @param array|string $tables
     * @throws BindParamException
     */
    public function backup(array|string $tables): string
    {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
        $data = '';
        ini_set ("memory_limit","-1");
        foreach ($tables as $t) {
            $res = $this->dbBackup->backup($t, 0);
            if (!$res && $res != 0) {
                $data .= $t . '|';
            }
        }
        return $data;
    }

    /**
     * 获取备份列表
     * @return array
     */
    public function getBackup(): array
    {
        $data = [];
        $files = $this->dbBackup->fileList();
        foreach ($files as $key => $t) {
            $data[$key]['backtime'] = $key;
            $data[$key]['id'] = $t['time'];
            $data[$key]['part'] = $t['part'];
            $data[$key]['time'] = $t['time'];
            $data[$key]['compress'] = $t['compress'];
            $data[$key]['filename'] = $t['filename'];
            $data[$key]['size'] = formatBytes($t['size']);
        }
        /* 根据时间降序 */
        krsort($data);
        return ['list' => array_values($data), 'total' => count($data)];
    }
}
