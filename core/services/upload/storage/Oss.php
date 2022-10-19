<?php
declare (strict_types = 1);
namespace core\services\upload\storage;

use OSS\OssClient;
use OSS\Core\OssException;
use core\basic\BaseUpload;
use core\exceptions\UploadException;
use think\exception\ValidateException;

/**
 * 阿里云OSS上传
 * Class OSS
 */
class Oss extends BaseUpload
{
    /**
     * accessKey
     * @var string
     */
    protected string $accessKey;

    /**
     * secretKey
     * @var string
     */
    protected string $secretKey;

    /**
     * 句柄
     * @var OssClient
     */
    protected OssClient $handle;

    /**
     * 上传域名
     * @var string
     */
    protected string $uploadUrl;

    /**
     * 存储空间名
     * @var string
     */
    protected string $storageName;

    /**
     * OOS所属地域
     * @var string
     */
    protected string $storageRegion;

    /**
     * 初始化
     * @return void
     * @param array $config 配置信息
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->uploadUrl = $config['uploadUrl'] ?? null;
        $this->accessKey = $config['accessKey'] ?? null;
        $this->secretKey = $config['secretKey'] ?? null;
        $this->storageName = $config['storageName'] ?? null;
        $this->storageRegion = $config['storageRegion'] ?? null;
    }

    /**
     * 初始化OSS
     * @return OssClient
     */
    protected function app(): OssClient
    {
        if (!$this->accessKey || !$this->secretKey) {
            throw new UploadException('Please configure accessKey or secretKey');
        }
        $this->handle = new OssClient($this->accessKey, $this->secretKey, $this->storageRegion);
        return $this->handle;
    }

    /**
     * 上传文件
     * @return bool|array
     * @throws OssException
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
        $fileName = $this->setFileName($fileHandle->getRealPath(), $fileHandle->getOriginalExtension());
        $filePath = $this->setUploadPath($fileName);
        try {
            $uploadInfo = $this->app()->uploadFile($this->storageName, $filePath, $fileHandle->getRealPath());
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Failed to upload to OSS');
            }
            $this->fileInfo['storage'] = 2;
            $this->fileInfo['name'] = $fileName;
            $this->fileInfo['type'] = $fileHandle->getMime();
            $this->fileInfo['uid'] = $uploadInfo['x-oss-request-id'];
            $this->fileInfo['relativePath'] = $filePath;
            $this->fileInfo['url'] = $this->uploadUrl . '/' . $filePath;
            $this->fileInfo['realPath'] = preg_replace('/http(s)?:/', '', $uploadInfo['info']['url']);
            return $this->fileInfo;
        } catch (UploadException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 上传文件流
     * @return bool|array
     * @throws OssException
     * @param string $fileContent
     * @param string|null $fileName
     */
    public function stream(string $fileContent, string $fileName = null): bool|array
    {
        try {
            if (!$fileName) {
                $fileName = $this->setFileName('attach');
            }
            $filePath = $this->setUploadPath($fileName);
            $uploadInfo = $this->app()->putObject($this->storageName, $filePath, $fileContent);
            if (!isset($uploadInfo['info']['url'])) {
                return $this->setError('Upload failure');
            }
            $this->fileInfo['storage'] = 2;
            $this->fileInfo['name'] = $fileName;
            $this->fileInfo['type'] = request()->header('Content-Type');
            $this->fileInfo['uid'] = $uploadInfo['x-oss-request-id'];
            $this->fileInfo['relativePath'] = $filePath;
            $this->fileInfo['url'] = $this->uploadUrl . '/' . $filePath;
            $this->fileInfo['realPath'] = preg_replace('/http(s)?:/', '', $uploadInfo['info']['url']);
            return $this->fileInfo;
        } catch (UploadException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 删除资源
     * @return bool|object
     * @param string $filePath
     */
    public function delete(string $filePath): bool|object
    {
        try {
            $fileExist = $this->app()->doesObjectExist($this->storageName, $filePath);
            $fileExist && $this->app()->deleteObject($this->storageName, $filePath);
            return $fileExist ?: throw new UploadException("Object doesn't exist");
        } catch (OssException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 获取OSS上传密钥
     * @return array
     * @throws \Exception
     * @param string $callbackUrl 回调地址
     * @param string $dir 目录
     */
    public function getTempKeys(string $callbackUrl = '', string $dir = ''): array
    {
        // TODO: Implement getTempKeys() method.
        $base64CallbackBody = base64_encode(json_encode([
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        ]));

        $policy = json_encode([
            'expiration' => $this->gmtIso8601(time() + 30),
            'conditions' =>
                [
                    [0 => 'content-length-range', 1 => 0, 2 => 1048576000],
                    [0 => 'starts-with', 1 => '$key', 2 => $dir]
                ]
        ]);
        $base64Policy = base64_encode($policy);
        $signature = base64_encode(hash_hmac('sha1', $base64Policy, $this->secretKey, true));
        return [
            'accessid' => $this->accessKey,
            'host' => $this->uploadUrl,
            'policy' => $base64Policy,
            'signature' => $signature,
            'expire' => time() + 30,
            'callback' => $base64CallbackBody,
            'type' => 'OSS'
        ];
    }

    /**
     * 获取ISO时间格式
     * @return string
     * @throws \Exception
     * @param int $time 时间戳
     */
    protected function gmtIso8601(int $time): string
    {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }
}
