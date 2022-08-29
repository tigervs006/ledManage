<?php

namespace core\services;

use think\facade\Config;
use core\services\upload\Upload;
use core\exceptions\UploadException;

/**
 * Class UploadService
 * @package core\services
 */
class UploadService
{

    /**
     * @var array
     */
    protected static array $upload = [];

    /**
     * @return Upload
     * @param int|null $type
     */
    public static function init(int $type = null): Upload
    {
        if (is_null($type)) {
            $type = (int) Config::get('index.upload_type');
        }
        $config = [];
        switch ($type) {
            case 1: // 本地
                break;
            case 2: // OSS
                $config = [
                    'storageName' => Config::get('index.alioss_bucket'),
                    'storageRegion' => Config::get('index.alioss_endpoint'),
                    'accessKey' => Config::get('index.alioss_accessKey_id'),
                    'secretKey' => Config::get('index.alioss_accessKey_secret'),
                ];
                break;
            case 3: // COS
                $config = [
                    'storageName' => Config::get('index.txcos_bucket'),
                    'accessKey' => Config::get('index.txcos_secret_id'),
                    'secretKey' => Config::get('index.txcos_secret_key'),
                    'storageRegion' => Config::get('index.txcos_region'),
                ];
                break;
            default:
                throw new UploadException('您已关闭上传功能');
        }

        /* 设置CDN域名 */
        1 < $type && $config['uploadUrl'] = Config::get('index.uploadUrl');
        return new Upload($type, $config);
    }
}
