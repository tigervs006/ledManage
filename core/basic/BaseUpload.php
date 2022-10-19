<?php
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
     * @return mixed
     */
    protected function getConfig(): mixed
    {
        $config = Config::get($this->configFile . '.stores.' . $this->name, []);
        if (empty($config)) {
            $config['filesize'] = Config::get($this->configFile . '.filesize', []);
            $config['fileExt'] = Config::get($this->configFile . '.fileExt', []);
            $config['fileMime'] = Config::get($this->configFile . '.fileMime', []);
        }
        return $config;
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
     * @param string|null $path 路径
     * @param string $ext 文件扩展名
     */
    protected function setFileName(string $path = null, string $ext = 'png'): string
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
     * 获取图片地址
     * @param string $filePath
     * @param bool $is_parse_url
     * @return string
     */
    protected function getFilePath(string $filePath = '', bool $is_parse_url = false): string
    {
        $path = $filePath ?: $this->filePath;
        if ($is_parse_url) {
            $data = parse_url($path);
            //远程地址处理
            if (isset($data['host']) && isset($data['path'])) {
                if (file_exists(app()->getRootPath() . 'public' . $data['path'])) {
                    $path = $data['path'];
                }
            }
        }
        return $path;
    }

    /**
     * 获取文件类型和大小
     * @param string $url
     * @param bool $isData
     * @return array
     */
    protected function getFileHeaders(string $url, bool $isData = true): array
    {
        stream_context_set_default(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $header['size'] = 0;
        $header['type'] = 'image/jpeg';
        if (!$isData) {
            return $header;
        }
        try {
            $headerArray = get_headers(str_replace('\\', '/', $url), true);
            if (!isset($headerArray['Content-Length'])) {
                $header['size'] = 0;
            }
            if (!isset($headerArray['Content-Type'])) {
                $header['type'] = 'image/jpeg';
            }
            if (is_array($headerArray['Content-Length']) && count($headerArray['Content-Length']) == 2) {
                $header['size'] = $headerArray['Content-Length'][1];
            }
            if (is_array($headerArray['Content-Type']) && count($headerArray['Content-Type']) == 2) {
                $header['type'] = $headerArray['Content-Type'][1];
            }
        } catch (\Exception $e) {
        }
        return $header;
    }

    /**
     * 获取上传信息
     * @return array
     */
    public function getUploadInfo(): array
    {
        if (isset($this->fileInfo->filePath)) {
            if (!str_contains($this->fileInfo->filePath, 'http')) {
                $url = request()->domain() . $this->fileInfo->filePath;
            } else {
                $url = $this->fileInfo->filePath;
            }
            $headers = $this->getFileHeaders($url);
            return [
                'name' => $this->fileInfo->fileName,
                'real_name' => $this->fileInfo->realName ?? '',
                'size' => $headers['size'] ?? 0,
                'type' => $headers['type'] ?? 'image/jpeg',
                'dir' => $this->fileInfo->filePath,
                'time' => time(),
            ];
        } else {
            return [];
        }
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
     * @param string $fileContent
     * @param string|null $fileName
     */
    abstract public function stream(string $fileContent, string $fileName = null): bool|array;

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
