<?php
declare (strict_types = 1);
namespace core\services\upload\storage;

use Qcloud\Cos\Client;
use QCloud\COSSTS\Sts;
use core\basic\BaseUpload;
use core\exceptions\UploadException;
use think\exception\ValidateException;

/**
 * 腾讯云COS文件上传
 * Class COS
 * @package core\services\upload\storage
 */
class Cos extends BaseUpload
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
     * @var Client
     */
    protected Client $handle;

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
     * COS所属地域
     * @var string
     */
    protected string $storageRegion;

    /**
     * 初始化
     * @return void
     * @param array $config
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->uploadUrl = $config['uploadUrl'] ?? '';
        $this->accessKey = $config['accessKey'] ?? null;
        $this->secretKey = $config['secretKey'] ?? null;
        $this->storageName = $config['storageName'] ?? null;
        $this->storageRegion = $config['storageRegion'] ?? null;
    }

    /**
     * 实例化cos
     * @return Client
     */
    protected function app(): Client
    {
        if (!$this->accessKey || !$this->secretKey) {
            throw new UploadException('Please configure accessKey and secretKey');
        }
        $this->handle = new Client(['region' => $this->storageRegion, 'credentials' => [
            'secretId' => $this->accessKey, 'secretKey' => $this->secretKey
        ]]);
        return $this->handle;
    }

    /**
     * 上传文件
     * @return bool|array
     * @param string|null $file
     * @param bool $isStream 是否为流上传
     * @param string|null $fileContent 流内容
     */
    protected function upload(string $file = null, bool $isStream = false, string $fileContent = null): bool|array
    {
        if (!$isStream) {
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
            $fileName = $this->setFileName($fileHandle->getRealPath(), $fileHandle->getOriginalExtension());
            $body = fopen($fileHandle->getRealPath(), 'rb');
        } else {
            $fileName = $file;
            $body = $fileContent;
        }
        try {
            $filePath = $this->setUploadPath($fileName);

            $uploadInfo = $this->app()->putObject([
                'Bucket' => $this->storageName,
                'Key' => $filePath,
                'Body' => $body
            ]);
            if (!isset($uploadInfo['Location'])) {
                return $this->setError('Failed to upload to COS');
            }
            $this->fileInfo['storage'] = 'COS';
            $this->fileInfo['name'] = $fileName;
            $this->fileInfo['uid'] = $uploadInfo['RequestId'];
            $this->fileInfo['relativePath'] = $uploadInfo['Key'];
            $this->fileInfo['url'] = $this->uploadUrl . '/' . $uploadInfo['Key'];
            $this->fileInfo['cosPath'] = '//' . $uploadInfo['Location'];
            return $this->fileInfo;
        } catch (UploadException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 文件上传
     * @return bool|array
     * @param string $file
     * @param bool $realName
     */
    public function move(string $file = 'file', bool $realName = false): bool|array
    {
        return $this->upload($file);
    }

    /**
     * 文件流上传
     * @return bool|array
     * @param string $fileContent
     * @param string|null $fileName
     */
    public function stream(string $fileContent, string $fileName = null): bool|array
    {
        if (!$fileName) {
            $fileName = $this->setFileName();
        }
        return $this->upload($fileName, true, $fileContent);
    }

    /**
     * 删除文件
     * @return bool|object
     * @param string $filePath
     */
    public function delete(string $filePath): bool|object
    {
        try {
            return $this->app()->deleteObject(['Bucket' => $this->storageName, 'Key' => $filePath]);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 生成临时签名
     * @return array
     * @throws \Exception
     */
    public function getTempKeys(): array
    {
        $sts = new Sts();
        $config = [
            'url' => 'https://sts.tencentcloudapi.com/',
            'domain' => 'sts.tencentcloudapi.com',
            'proxy' => '',
            'secretId' => $this->accessKey, // 固定密钥
            'secretKey' => $this->secretKey, // 固定密钥
            'bucket' => $this->storageName, // 换成你的 bucket
            'region' => $this->storageRegion, // 换成 bucket 所在园区
            'durationSeconds' => 1800, // 密钥有效期
            'allowPrefix' => '*', // 这里改成允许的路径前缀，可以根据自己网站的用户登录态判断允许上传的具体路径，例子： a.jpg 或者 a/* 或者 * (使用通配符*存在重大安全风险, 请谨慎评估使用)
            // 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
            'allowActions' => [
                // 简单上传
                'name/cos:PutObject',
                'name/cos:PostObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload'
            ]
        ];
        // 获取临时密钥，计算签名
        $result = $sts->getTempKeys($config);
        $result['url'] = $this->uploadUrl . '/';
        $result['type'] = 'COS';
        $result['bucket'] = $this->storageName;
        $result['region'] = $this->storageRegion;
        return $result;
    }
}
