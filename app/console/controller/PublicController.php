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
namespace app\console\controller;

use think\facade\Cache;
use core\utils\JwtAuth;
use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use core\exceptions\AuthException;
use app\services\user\UserServices;
use app\services\auth\GroupServices;
use app\services\user\ClientServices;
use app\services\system\RegionServices;
use app\services\user\JwtTokenServices;
use app\services\system\SystemLogServices;

class PublicController extends BaseController
{
    /**
     * JwtAuth
     * @var JwtAuth
     */
    private JwtAuth $jwtAuth;

    /**
     * 用户类
     * @var UserServices
     */
    private UserServices $userServices;

    /**
     * 用户组类
     * @var GroupServices
     */
    private GroupServices $groupServices;

    /**
     * Token类
     * @var JwtTokenServices
     */
    private JwtTokenServices $jwtServices;

    /**
     * 行政区域类
     * @var RegionServices
     */
    private RegionServices $regionServices;

    /**
     * 客户信息类
     * @var ClientServices
     */
    private ClientServices $clientServices;

    /**
     * @var SystemLogServices
     */
    private SystemLogServices $logServices;

    public function initialize()
    {
        parent::initialize();
        $this->jwtAuth = $this->app->make(JwtAuth::class);
        $this->userServices = $this->app->make(UserServices::class);
        $this->groupServices = $this->app->make(GroupServices::class);
        $this->jwtServices = $this->app->make(JwtTokenServices::class);
        $this->clientServices = $this->app->make(ClientServices::class);
        $this->regionServices = $this->app->make(RegionServices::class);
        $this->logServices = $this->app->make(SystemLogServices::class);
    }

    /**
     * 用户登录
     * @return Json
     */
    final public function login(): Json
    {
        $ipAddress = $this->request->ip();
        $data = $this->request->post(['name', 'password'], null, 'trim');

        try {
            $this->validate(
                $data,
                ['name' => 'require', 'password' => 'require'],
                ['name.require' => '用户名不得为空', 'password.require' => '密码不得为空']
            );
        } catch (\think\exception\ValidateException $e) {
            throw new AuthException($e->getError());
        }

        $userInfo = $this->userServices->getOne(['name' => $data['name']], null);
        is_null($userInfo) && throw new AuthException('用户名不存在，请重新输入');
        /* 查询所在用户组状态 */
        $groupStatus = $this->groupServices->value(['id' => $userInfo['gid']], 'status');
        !$groupStatus && throw new AuthException('所属的用户组已被禁用，请联系后台管理员');
        !$userInfo['status'] && throw new AuthException("用户：${data['name']} 已禁用");
        !password_verify($data['password'], $userInfo['password']) && throw new AuthException('密码验证失败');
        /* 签发access_token */
        $access_token = $this->jwtAuth->createToken($userInfo['id'], $userInfo['gid'], $userInfo['name']);
        /* 签发refresh_token */
        $refresh_token = $this->jwtAuth->createToken(null, null, $userInfo['name'], true);
        /* 更新登录时间和ip地址 */
        $this->userServices->updateOne($userInfo['id'], ['ipaddress' => ip2long($ipAddress), 'last_login' => time()]);
        /* 获取数据库token信息 */
        $userToken = $this->jwtServices->value(['uid' => $userInfo['id']], 'uid');
        $tokenData = ['uid' => $userInfo['id'], 'user' => $userInfo['name'], 'access_token' => $access_token['token'], 'refresh_token' => $refresh_token['token']];
        /* 同步token到数据库中 */
        $userToken ? $this->jwtServices->updateOne($userInfo['id'], $tokenData) : $this->jwtServices->saveOne($tokenData);

        $info = [
            'uid' => $userInfo['id'],
            'gid' => $userInfo['gid'],
            'name' => $userInfo['name'],
            'access_token' => $access_token['token'],
            'refresh_token' => $refresh_token['token'],
            'expiresAt' => $access_token['expiresAt'] * 1000
        ];

        /* 记录用户登录日志 */
        $this->logServices->actionLogRecord($info, 2, '用户登录');

        return $this->json->successful('Login successful', compact('info'));
    }

    /**
     * 退出登录
     * @return Json
     */
    final public function logout(): Json
    {
        $token = $this->request->tokenInfo();
        /* 记录退出登录日志 */
        $this->logServices->actionLogRecord($token, 2, '退出登录');
        return $this->json->successful('Logout successful');
    }

    /**
     * 表单提交
     * @return Json
     */
    final public function submitForm(): Json
    {
        $post = $this->request->only([
            'city',
            'email',
            'mobile',
            'message',
            'username',
            'province',
            'district',
            'company' => '未知',
        ], 'post', 'trim');

        /* 数据验证 */
        $validator = 'app\console\validate\FormValidator';
        try {
            $this->validate($post, $validator);
        } catch (\think\exception\ValidateException $e) {
            throw new ApiException($e->getError());
        }

        /* 留言来源 */
        $post['source'] = 1;
        /* 留言的ip */
        $ipAddress = $this->request->ip();
        /* 留言时间 */
        $nowTime = date('Y-m-d H:i:s');
        /* ip转int类型 */
        $post['ipaddress'] = ip2long($ipAddress);
        /* 获取留言页面 */
        $post['page'] = $this->request->header('referer');
        /* 组装省市区地址 */
        $city_name = $this->regionServices->value(['cid' => $post['city']], 'name');
        $district_name = $this->regionServices->value(['cid' => $post['district']], 'name');
        $province_name = $this->regionServices->value(['cid' => $post['province']], 'name');
        $post['address'] = "{$province_name}，{$city_name}，{$district_name}";

        /* 写入到数据库 */
        $cid = $this->clientServices->value(['ipaddress' => $post['ipaddress']], 'id') ?: null;
        $cid && $post['id'] = $cid; // 相同ip的留言将视为更新留言信息
        $this->clientServices->saveClient($post, '留言失败！请检查各项信息是否正确填写');

        /* 入库后发送邮件 */
        if (config('index.mail_service')) {
            /* 邮件模板 */
            $mailBody =
                /** @lang text */
                <<<TEMPLATE
                    姓名：{$post['username']}<br/>
                    电话：{$post['mobile']}<br/>
                    邮箱：{$post['email']}<br/>
                    ip地址：{$ipAddress}<br/>
                    留言时间：{$nowTime}<br/>
                    所在城市：{$province_name}，{$city_name}，{$district_name}<br/>
                    公司名称：{$post['company']}<br/>
                    留言页面：{$post['page']}<br/>
                    留言信息：{$post['message']}
                TEMPLATE;
            (new \core\utils\MailHandler())->sendMail($mailBody);
        }

        $message = isset($cid) ? '更新信息成功' : '留言成功';
        return $this->json->successful("{$message}，我们将会在24小时内联系您");
    }

    /**
     * 清除日志
     * @return Json
     */
    final public function clearLog(): Json
    {
        $result = \think\facade\Log::clear();
        return $result ? $this->json->successful('Clear log successfully') : $this->json->fail('Clear log failed');
    }

    /**
     * refreshToken
     * @return Json
     */
    final public function refreshToken(): Json
    {
        /* 从旧的access_token获取信息 */
        $tokenInfo = $this->request->tokenInfo();
        $access_token = $this->jwtAuth->createToken($tokenInfo['uid'], $tokenInfo['gid'], $tokenInfo['aud']);
        $info = [
            'uid' => $tokenInfo['uid'],
            'gid' => $tokenInfo['gid'],
            'name' => $tokenInfo['aud'],
            'access_token' => $access_token['token'],
            'expiresAt' => $access_token['expiresAt'] * 1000
        ];
        $this->logServices->actionLogRecord($info, 2, '刷新令牌');
        return $this->json->successful('refresh token successed', compact('info'));
    }

    /**
     * 清除缓存
     * @return Json
     */
    final public function refreshCache(): Json
    {
        $key = $this->request->post('key/s', '', 'trim');
        /* 如果没有指定缓存key，则默认清空所有缓存 */
        Cache::has($key) ? Cache::delete($key) : Cache::clear();
        return $this->json->successful('清除缓存成功');
    }
}
