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
namespace core\services\upload\storage;

use think\File;
use think\facade\Config;
use core\basic\BaseUpload;
use think\facade\Filesystem;
use core\exceptions\UploadException;
use think\exception\ValidateException;

/**
 * 本地上传
 * Class Local
 * @package core\services\upload\storage
 */
class Local extends BaseUpload
{
    /**
     * @var string
     */
    private string $uploadUrl;

    /**
     * 默认存放路径
     * @var string
     */
    protected string $defaultPath;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->uploadUrl = $config['uploadUrl'] ?? '';
        $this->defaultPath = Config::get('filesystem.disks.' . Config::get('filesystem.default') . '.url');
    }

    protected function app(): bool
    {
        return $this->setError('本地上传无需配置');
    }

    public function getTempKeys(): array
    {
        return [];
    }

    /**
     * 生成上传文件目录
     * @return string
     * @param $path
     * @param null $root
     */
    public function uploadDir($path, $root = null): string
    {
        if ($root === null) $root = app()->getRootPath() . 'public/';
        return str_replace('\\', '/', $root . 'storage/' . $path);
    }

    /**
     * 检查上传目录不存在则生成
     * @return bool
     * @param $dir
     */
    protected function validDir($dir): bool
    {
        return is_dir($dir) == true || mkdir($dir, 0755, true) == true;
    }

    /**
     * 文件上传
     * @return array|bool
     * @param string $file
     * @param bool $realName
     */
    public function move(string $file = 'file', bool $realName = false): bool|array
    {
        $fileHandle = app()->request->file($file);
        $size = formatBytes(config($this->configFile . '.filesize', 2097152));
        if (!$fileHandle) {
            return $this->setError('Upload file does not exist');
        }
        if ($this->validate) {
            try {
                $error = [
                    $file . '.fileExt' => 'Upload fileExt error',
                    $file . '.fileMime' => 'Upload fileMine error',
                    $file . '.filesize' => "Upload filesize more than max ${size}"
                ];
                validate([$file => $this->validate], $error)->check([$file => $fileHandle]);
            } catch (ValidateException $e) {
                return $this->setError($e->getMessage());
            }
        }
        if ($realName) {
            $fileName = Filesystem::putFileAs($this->path, $fileHandle, $fileHandle->getOriginalName());
        } else {
            $fileName = Filesystem::putFile($this->path, $fileHandle, 'uniqid');
        }
        if (!$fileName) {
            return $this->setError('Failed to upload to Local');
        }
        $filePath = Filesystem::path($fileName);
        $this->fileInfo['storage'] = 1;
        $this->fileInfo['uid'] = rand(100000, 100000000);
        $this->fileInfo['name'] = (new File($filePath))->getFilename();
        $this->fileInfo['type'] = (new File($filePath))->getMime();
        $this->fileInfo['url'] = $this->defaultPath . '/' . str_replace('\\', '/', $fileName);
        $this->fileInfo['realPath'] = $this->defaultPath . '/' . str_replace('\\', '/', $fileName);
        $this->fileInfo['relativePath'] = $this->defaultPath . '/' . str_replace('\\', '/', $fileName);
        return $this->fileInfo;
    }

    /**
     * 文件流上传
     * @return bool|array
     * @param string $ext 扩展名
     * @param string $fileContent
     */
    public function stream(string $fileContent, string $ext): bool|array
    {
        $realName = $this->setFileName((string) time(), $ext);
        $dir = $this->uploadDir($this->path);
        if (!$this->validDir($dir)) {
            return $this->setError('Failed to generate upload directory');
        }
        $fileName = $dir . '/' . $realName;
        file_put_contents($fileName, $fileContent);
        $this->fileInfo['storage'] = 1;
        $this->fileInfo['name'] = $realName;
        $this->fileInfo['type'] = request()->header('Content-Type');
        $this->fileInfo['uid'] = rand(100000, 100000000);
        $this->fileInfo['url'] = $this->defaultPath . '/' . $this->path . '/' . $realName;
        $this->fileInfo['realPath'] = $this->defaultPath . '/' . $this->path . '/' . $realName;
        $this->fileInfo['relativePath'] = $this->defaultPath . '/' . $this->path . '/' . $realName;
        return $this->fileInfo;
    }

    /**
     * 文件流下载保存图片
     * @return bool|array
     * @param string $fileContent
     * @param string|null $key
     */
    public function down(string $fileContent, string $key = null): bool|array
    {
        if (!$key) {
            $key = $this->setFileName();
        }
        $dir = $this->uploadDir($this->path);
        if (!$this->validDir($dir)) {
            return $this->setError('Failed to generate upload directory, please check the permission!');
        }
        $fileName = $dir . '/' . $key;
        file_put_contents($fileName, $fileContent);
        $this->downFileInfo['downloadInfo'] = new File($fileName);
        $this->downFileInfo['downloadRealName'] = $key;
        $this->downFileInfo['downloadFileName'] = $key;
        $this->downFileInfo['downloadFilePath'] = $this->defaultPath . '/' . $this->path . '/' . $key;
        return $this->downFileInfo;
    }

    /**
     * 删除文件
     * @return bool
     * @param string $filePath
     */
    public function delete(string $filePath): bool
    {
        $path = substr($filePath, stripos($filePath,'/') + 1);
        if (file_exists($path)) {
            try {
                unlink($path);
                $dirpath = preg_replace('/(?!.*\/).*/', '', $path);
                /* todo: 是否考虑向上递归删除空文件夹？ */
                if (2 >= count(scandir($dirpath))) return rmdir($dirpath);
                return true;
            } catch (UploadException $e) {
                return $this->setError($e->getMessage());
            }
        }
        return false;
    }
}
