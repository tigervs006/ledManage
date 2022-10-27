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
namespace core\basic;

use think\facade\Config;

/**
 * Class BaseUpload
 * @package core\basic
 */
abstract class BaseUpload extends BaseStorage
{
    /**
     * 图片信息
     */
    protected array $fileInfo;

    /**
     * 要生成缩略图、水印的图片地址
     * @var string
     */
    protected string $filePath;

    /**
     * 验证配置
     * @var string
     */
    protected string $validate;

    /**
     * 保存路径
     * @var string
     */
    protected string $path = '';

    /**
     * 下载图片信息
     */
    protected array $downFileInfo;

    public function initialize(array $config):void {}

    /**
     * 设置处理缩略图、水印图片路径
     * @param string $filePath
     * @return $this
     */
    public function setFilepath(string $filePath): static
    {
        $this->filePath = str_starts_with($filePath, '.') ? substr($filePath, 1) : $filePath;
        return $this;
    }

    /**
     * 上传文件路径
     * @param string $path
     * @return $this
     */
    public function to(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 获取文件信息
     * @return array
     */
    public function getFileInfo(): array
    {
        return $this->fileInfo;
    }

    /**
     * 检测是否是图片
     * @param $filePath
     * @return bool
     */
    protected function checkImage($filePath): bool
    {
        /* 获取图像信息 */
        $info = @getimagesize($filePath);
        /* 检测图像合法性 */
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            return false;
        }
        return true;
    }

    /**
     * 获取系统配置
     * @return array
     */
    protected function getConfig(): array
    {
        /* 从缓存中读取配置 */
        $result = cache('config');
        $config['filesize'] = (int) $result['filesize'];
        $config['fileExt'] = json_decode($result['fileExt'], true);
        $config['fileMime'] = json_decode($result['fileMime'], true);
        if (empty($config)) { /* 如果缓存中配置为空，则从文件中读取配置 */
            $config['filesize'] = Config::get($this->configFile . '.filesize', []);
            $config['fileExt'] = Config::get($this->configFile . '.fileExt', []);
            $config['fileMime'] = Config::get($this->configFile . '.fileMime', []);
        }
        /* 过滤空值数组 */
        return array_filter($config);
    }

    /**
     * 设置验证规则
     * @param array|null $validate
     * @return $this
     */
    public function validate(?array $validate = null): static
    {
        if (is_null($validate)) {
            $validate = $this->getConfig();
        }
        $this->extractValidate($validate);
        return $this;
    }

    /**
     * 设置上传路径
     * @return string
     * @param string $fileName
     */
    protected function setUploadPath(string $fileName): string
    {
        return ($this->path ? $this->path . '/' : 'attach/' . date('Y-m-d', time()) . '/') . $fileName;
    }

    /**
     * 提取上传验证
     * @return void
     * @param array $validateArray
     */
    protected function extractValidate(array $validateArray): void
    {
        $validate = [];
        foreach ($validateArray as $key => $value) {
            $validate[] = $key . ':' . (is_array($value) ? implode(',', $value) : $value);
        }
        $this->validate = implode('|', $validate);
        unset($validate);
    }

    /**
     * 设置文件名
     * @return string
     * @param string $path 路径
     * @param string $ext 文件扩展名
     */
    protected function setFileName(string $path, string $ext): string
    {
        return ($path ? substr(md5($path), 0, 8) : '') . time() . '.' . $ext;
    }

    /**
     * 提取文件后缀以及之前部分
     * @param string $path
     * @return false|string[]
     */
    protected function getFileName(string $path): array|bool
    {
        $_empty = ['', ''];
        if (!$path) return $_empty;
        if (strpos($path, '?')) {
            $_tarr = explode('?', $path);
            $path = trim($_tarr[0]);
        }
        $arr = explode('.', $path);
        if (!is_array($arr) || count($arr) <= 1) return $_empty;
        $ext_name = trim($arr[count($arr) - 1]);
        $ext_name = !$ext_name ? 'jpg' : $ext_name;
        return [explode('.' . $ext_name, $path)[0], $ext_name];
    }

    /**
     * 实例化app
     * @return mixed
     */
    abstract protected function app(): mixed;

    /**
     * 文件上传
     * @return bool|array
     * @param string $file
     */
    abstract public function move(string $file = 'file'): bool|array;

    /**
     * 文件流上传
     * @return bool|array
     * @param string $ext 扩展名
     * @param string $fileContent
     */
    abstract public function stream(string $fileContent, string $ext): bool|array;

    /**
     * 删除文件
     * @return bool|object
     * @param string $filePath
     */
    abstract public function delete(string $filePath): bool|object;


    /**
     * 获取上传密钥
     * @return array
     */
    abstract public function getTempKeys(): array;
}
