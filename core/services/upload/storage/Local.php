<?php
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
     * 默认存放路径
     * @var string
     */
    protected string $defaultPath;

    public function initialize(array $config): void
    {
        parent::initialize($config);
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
        return str_replace('\\', '/', $root . 'uploads/' . $path);
    }

    /**
     * 检查上传目录不存在则生成
     * @return bool
     * @param $dir
     */
    protected function validDir($dir): bool
    {
        return is_dir($dir) == true || mkdir($dir, 0777, true) == true;
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
        if (!$fileHandle) {
            return $this->setError('Upload file does not exist');
        }
        if ($this->validate) {
            try {
                $error = [
                    $file . '.filesize' => 'Upload filesize error',
                    $file . '.fileExt' => 'Upload fileExt error',
                    $file . '.fileMime' => 'Upload fileMine error'
                ];
                validate([$file => $this->validate], $error)->check([$file => $fileHandle]);
            } catch (ValidateException $e) {
                return $this->setError($e->getMessage());
            }
        }
        if ($realName) {
            $fileName = Filesystem::putFileAs($this->path, $fileHandle, $fileHandle->getOriginalName());
        } else {
            $fileName = Filesystem::putFile($this->path, $fileHandle);
        }
        if (!$fileName) {
            return $this->setError('Failed to upload to Local');
        }
        $filePath = Filesystem::path($fileName);
        $this->fileInfo['storage'] = 'Local';
        $this->fileInfo['originalName'] = $fileHandle->getOriginalName();
        $this->fileInfo['uid'] = rand(100000, 100000000);
        $this->fileInfo['name'] = (new File($filePath))->getFilename();
        $this->fileInfo['url'] = $this->defaultPath . '/' . str_replace('\\', '/', $fileName);
        return $this->fileInfo;
    }

    /**
     * 文件流上传
     * @return bool|array
     * @param string $fileContent
     * @param string|null $fileName
     */
    public function stream(string $fileContent, string $fileName = null): bool|array
    {
        $realName = $this->setFileName();
        $dir = $this->uploadDir($this->path);
        if (!$this->validDir($dir)) {
            return $this->setError('Failed to generate upload directory, please check the permission!');
        }
        $fileName = $dir . '/' . $realName;
        file_put_contents($fileName, $fileContent);
        $this->fileInfo['name'] = $realName;
        $this->fileInfo['uid'] = rand(100000, 100000000);
        $this->fileInfo['url'] = $this->defaultPath . '/' . $this->path . '/' . $realName;
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
                return true;
            } catch (UploadException $e) {
                return $this->setError($e->getMessage());
            }
        }
        return false;
    }
}
