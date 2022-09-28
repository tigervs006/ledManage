<?php
declare (strict_types = 1);
namespace core\services;

use Exception;
use think\facade\Db;
use think\facade\Cache;
use think\db\exception\BindParamException;

/**
 * 数据库备份工具类
 * Class MysqlBackupService
 * @package core\services
 */
class MysqlBackupService
{
    /**
     * 文件指针
     * @var resource
     */
    private $fp;

    /**
     * 备份文件信息
     * @var array
     */
    private array $file;

    /**
     * 打开文件大小
     * @var int|float
     */
    private int|float $size = 0;

    /**
     * 数据备份配置
     * @var array
     */
    private array $config = [
        'part' => 20971520,
    ];

    /**
     * 数据库配置
     * @var array
     */
    private array $dbconfig = [];

    /**
     * 数据库备份构造方法
     * @throws Exception
     * @param array $config 配置信息
     */
    public function __construct(array $config)
    {
        /* 初始化文件名 */
        $this->setFile();
        /* 初始化连接参数 */
        $this->setDbConn();
        /* 设置脚本执行时间 */
        $this->setTimeout();
        /* 初始化默认配置 */
        $this->config = array_merge($config, $this->config);
        /* 检查文件是否可写 */
        if (!$this->checkPath($this->config['path'])) {
            throw new Exception("The current directory is not writable");
        }
    }

    /**
     * 设置脚本运行超时时间
     * @return $this
     * 0表示不限制，支持连贯操作
     */
    public function setTimeout(int $time = 0): static
    {
        if (!is_null($time)) {
            set_time_limit($time) || ini_set("max_execution_time", (string) $time);
        }
        return $this;
    }

    /**
     * 设置数据库连接参数
     * @return $this
     * @param array $dbconfig 数据库连接配置信息
     */
    public function setDbConn(array $dbconfig = []): static
    {
        if (empty($dbconfig)) {
            $this->dbconfig = config('database.connections.' . config('database.default'));
        } else {
            $this->dbconfig = $dbconfig;
        }
        return $this;
    }

    /**
     * 设置备份文件名
     * @return $this
     * @param null $file
     */
    public function setFile($file = null): static
    {
        if (is_null($file)) {
            $this->file = ['name' => date('Ymd-His'), 'part' => 1];
        } else {
            if (!array_key_exists("name", $file) && !array_key_exists("part", $file)) {
                $this->file = $file['1'];
            } else {
                $this->file = $file;
            }
        }
        return $this;
    }

    /**
     * 连接数据
     * @return \think\db\ConnectionInterface
     */
    public static function connect(): \think\db\ConnectionInterface
    {
        return Db::connect();
    }

    /**
     * 查询表引擎
     * @return string
     * @param string $tablename 表名
     */
    public function engines(string $tablename): string
    {
        $db = self::connect();
        $res = $db->query("SHOW CREATE TABLE {$tablename}");
        $tableInfo = $res[0]['Create Table'];
        preg_match('/ENGINE\=(\w+)/i', $tableInfo, $matches);
        return $matches[count($matches) -1];
    }

    /**
     * 数据库表列表
     * @return array
     * @param int $type
     * @param string|null $table
     */
    public function dataList(?string $table = null, int $type = 1): array
    {
        $db = self::connect();
        if (is_null($table)) {
            $list = $db->query("SHOW TABLE STATUS");
        } else {
            if ($type) {
                $list = $db->query("SHOW FULL COLUMNS FROM {$table}");
            } else {
                $list = $db->query("show columns from {$table}");
            }
        }
        return array_map('array_change_key_case', $list);
    }

    /**
     * 数据库备份文件列表
     * @return array
     */
    public function fileList(): array
    {
        if (!is_dir($this->config['path'])) {
            mkdir($this->config['path'], 0755, true);
        }
        $path = realpath($this->config['path']);
        $flag = \FilesystemIterator::KEY_AS_FILENAME;
        $glob = new \FilesystemIterator($path, $flag);
        $list = array();
        foreach ($glob as $name => $file) {
            if (preg_match('/^\\d{8,8}-\\d{6,6}-\\d+\\.sql(?:\\.gz)?$/', $name)) {
                $info['filename'] = $name;
                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                $part = $name[6];
                if (isset($list["{$date} {$time}"])) {
                    $info = $list["{$date} {$time}"];
                    $info['part'] = max($info['part'], $part);
                    $info['size'] = $info['size'] + $file->getSize();
                } else {
                    $info['part'] = $part;
                    $info['size'] = $file->getSize();
                }
                $extension = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $info['compress'] = $extension === 'SQL' ? '-' : $extension;
                $info['time'] = strtotime("{$date} {$time}");
                $list["{$date} {$time}"] = $info;
            }
        }
        return $list;
    }

    /**
     * @return array|false|string
     * @param int $time
     * @throws Exception
     * @param string $type
     */
    public function getFile(string $type = '', int $time = 0): bool|array|string
    {
        if (!is_numeric($time)) {
            throw new Exception("{$time} Illegal data type");
        }
        switch ($type) {
            case 'time':
                $name = date('Ymd-His', $time) . '-*.sql*';
                $path = realpath($this->config['path']) . DS . $name;
                return glob($path);
            case 'timeverif':
                $name = date('Ymd-His', $time) . '-*.sql*';
                $path = realpath($this->config['path']) . DS . $name;
                $files = glob($path);
                $list = array();
                foreach ($files as $name) {
                    $basename = basename($name);
                    $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
                    $gz = preg_match('/^\\d{8}-\\d{6}-\\d+\\.sql.gz$/', $basename);
                    $list[$match[6]] = array($match[6], $name, $gz);
                }
                $last = end($list);
                if (count($list) === $last[0]) {
                    return $list;
                } else {
                    throw new Exception("File {$files['0']} may be damaged, please check again");
                }
            case 'pathname':
                return "{$this->config['path']}{$this->file['name']}-{$this->file['part']}.sql";
            case 'filename':
                return "{$this->file['name']}-{$this->file['part']}.sql";
            case 'filepath':
                return $this->config['path'];
            default:
                return array('pathname' => "{$this->config['path']}{$this->file['name']}-{$this->file['part']}.sql", 'filename' => "{$this->file['name']}-{$this->file['part']}.sql", 'filepath' => $this->config['path'], 'file' => $this->file);
        }
    }

    /**
     * 删除备份文件
     * @return int
     * @param int $time
     * @throws Exception
     */
    public function delFile(int $time): int
    {
        if ($time) {
            $file = $this->getFile('time', $time);
            array_map("unlink", $file);
            if (count($this->getFile('time', $time))) {
                throw new Exception("File {$time} deleted failed");
            } else {
                return $time;
            }
        } else {
            throw new Exception("{$time} Time parameter is incorrect");
        }
    }

    /**
     * 下载备份
     * @return int|false|string
     * @param int $time
     * @param int $part
     * @param int $isFile
     * @throws Exception
     */
    public function downloadFile(int $time, int $part = 0, int $isFile = 0): int|false|string
    {
        $file = $this->getFile('time', $time);
        $fileName = $file[$part];
        if (file_exists($fileName)) {
            if ($isFile) {
                $key = password_hash($fileName, PASSWORD_DEFAULT);
                Cache::set($key, ['path' => $fileName, 'fileName' => substr(strstr($fileName, 'backup'), 15)], 300);
                return $key;
            }
            ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($fileName));
            header('Access-Control-Allow-Origin: ' . request()->domain());
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Disposition: attachment; filename=' . basename($fileName));
            return readfile($fileName);
        } else {
            throw new Exception("{$time} File is abnormal");
        }
    }

    /**
     * 还原数据
     * @param $start
     * @return int|bool|array
     */
    public function import($start): int|bool|array
    {
        $db = self::connect();
        if ($this->config['compress']) {
            $gz = gzopen($this->file[1], 'r');
            $size = 0;
        } else {
            $size = filesize($this->file[1]);
            $gz = fopen($this->file[1], 'r');
        }
        $sql = '';
        if ($start) {
            $this->config['compress'] ? gzseek($gz, $start) : fseek($gz, $start);
        }
        for ($i = 0; $i < 1000; $i++) {
            $sql .= $this->config['compress'] ? gzgets($gz) : fgets($gz);
            if (preg_match('/.*;$/', trim($sql))) {
                if (false !== $db->execute($sql)) {
                    $start += strlen($sql);
                } else {
                    return false;
                }
                $sql = '';
            } elseif ($this->config['compress'] ? gzeof($gz) : feof($gz)) {
                return 0;
            }
        }
        return array($start, $size);
    }

    /**
     * 写入初始数据
     * @return bool
     */
    public function Backup_Init(): bool
    {
        $sql = "-- -----------------------------\n";
        $sql .= "-- Think MySQL Data Transfer \n";
        $sql .= "-- \n";
        $sql .= "-- Host     : " . $this->dbconfig['hostname'] . "\n";
        $sql .= "-- Port     : " . $this->dbconfig['hostport'] . "\n";
        $sql .= "-- Database : " . $this->dbconfig['database'] . "\n";
        $sql .= "-- \n";
        $sql .= "-- Part : #{$this->file['part']}\n";
        $sql .= "-- Date : " . date("Y-m-d H:i:s") . "\n";
        $sql .= "-- -----------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        return $this->write($sql);
    }

    /**
     * 备份表结构
     * @return bool|int
     * @param int $start
     * @param string $sql
     * @param string $table
     * @throws BindParamException
     */
    public function backup(string $table, int $start, string $sql = ''): bool|int
    {
        $db = self::connect();
        if (0 == $start) {
            $result = $db->query("SHOW CREATE TABLE `{$table}`");
            $sql .= "\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= trim($result[0]['Create Table']) . ";\n\n";
        }
        /* 数据总数 */
        $result = $db->query("SELECT COUNT(*) AS count FROM `{$table}`");
        $count = $result['0']['count'];
        /* 备份表数据 */
        if ($count) {
            /* 写入数据注释 */
            if (0 == $start) {
                $sql .= "-- -----------------------------\n";
                $sql .= "-- Records of `{$table}`\n";
                $sql .= "-- -----------------------------\n";
            }
            /* 备份数据记录 */
            $result = $db->query("SELECT * FROM `{$table}` LIMIT :MIN, 1000", ['MIN' => $start]);
            foreach ($result as $row) {
                $row = array_map(function ($val) {
                    if (is_null($val)) {
                        return 'NULL';
                    } else if (is_numeric($val)) {
                        return (int) $val;
                    } else {
                        return "'" . str_replace(["\r", "\n"], ['\\r', '\\n'], addslashes($val)) . "'";
                    }
                }, $row);
                $sql .= "INSERT INTO `{$table}` VALUES (" . implode(", ", $row) . ");\n";
            }
            if (false === $this->write($sql)) {
                return false;
            }
            /* 更多数据 */
            if ($count > $start + 1000) {
                return $this->backup($table, $start + 1000);
            }
        }
        return 0; /* 下一张表 */
    }

    /**
     * 优化表
     * @return mixed
     * @throws Exception
     * @param array|string $tables
     */
    public function optimize(array|string $tables): mixed
    {
        $db = self::connect();
        if (is_array($tables)) {
            $tables = implode('`,`', $tables);
            $list = $db->query("OPTIMIZE TABLE `{$tables}`");
        } else {
            $list = $db->query("OPTIMIZE TABLE {$tables}");
        }
        if ($list) {
            return $list;
        } else {
            throw new Exception("data sheet'{$tables}'Repair mistakes please try again!");
        }
    }

    /**
     * 修复表
     * @return array
     * @throws Exception
     * @throws BindParamException
     * @param string|array|null $tables
     */
    public function repair(string|array $tables = null): array
    {
        $db = self::connect();
        if (is_array($tables)) {
            $tables = implode('`,`', $tables);
            $list = $db->query("REPAIR TABLE `{$tables}`");
        } else {
            $list = $db->query("REPAIR TABLE {$tables}");
        }
        if ($list) {
            return $list;
        } else {
            throw new Exception("data sheet'{$tables}'Repair mistakes please try again!");
        }
    }

    /**
     * 写入SQL语句
     * @return int|false
     * @param string $sql
     */
    private function write(string $sql): int|false
    {
        $size = strlen($sql);
        $size = $this->config['compress'] ? $size / 2 : $size;
        $this->open($size);
        return $this->config['compress'] ? @gzwrite($this->fp, $sql) : @fwrite($this->fp, $sql);
    }

    /**
     * 打开一个卷，用于写入数据
     * @param int|float $size 写入数据的大小
     */
    private function open(int|float $size): void
    {
        if ($this->fp) {
            $this->size += $size;
            if ($this->size > $this->config['part']) {
                $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
                $this->fp = null;
                $this->file['part']++;
                session('backup_file', $this->file);
                $this->Backup_Init();
            }
        } else {
            $backuppath = $this->config['path'];
            $filename = "{$backuppath}{$this->file['name']}-{$this->file['part']}.sql";
            if ($this->config['compress']) {
                $filename = "{$filename}.gz";
                $this->fp = @gzopen($filename, "a{$this->config['level']}");
            } else {
                $this->fp = @fopen($filename, 'a');
            }
            $this->size = filesize($filename) + $size;
        }
    }

    /**
     * 检查目录是否可写
     * @return boolean
     * @param string $path
     */
    protected function checkPath(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }
        if (mkdir($path, 0755, true)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 析构方法
     */
    public function __destruct()
    {
        $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
    }
}
