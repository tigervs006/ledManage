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

use think\facade\Route;

/** 自定义接口 */
Route::group(function () {
    Route::group('public', function () {
        Route::post('refresh_token', 'refreshToken')->option(['route_name' => '刷新令牌'])
            ->middleware([app\http\middleware\AuthTokenMiddleware::class]);
    })->prefix('publicController/');
})->option(['https' => true])->pattern(['id' => '\d+']);

/** 无授权接口 */
Route::group(function () {
    Route::group('public', function () {
        Route::post('login', 'login')->option(['route_name' => '用户登录']);
        Route::post('logout', 'logout')->option(['route_name' => '用户登出']);
        Route::post('submit', 'submitForm')->option(['route_name' => '表单留言']);
        Route::get('download', function ($key) {
            $file = cache($key);
            return !is_null($file)
                ? download($file['path'], $file['fileName'])
                : response('Resource not found!', 404);
        })->pattern(['key' => '\S+'])->option(['route_name' => '文件下载']);
    })->prefix('publicController/');
})->option(['https' => true])->pattern(['id' => '\d+']);

/** 需授权接口 */
Route::group(function () {
    // Tags部分
    Route::group('tags', function () {
        Route::get('<id?>$', 'index')->option(['route_name' => '获取Tag信息']);
        Route::get('list', 'list')->option(['route_name' => 'Tag列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑Tag']);
        Route::post('del', 'delete')->option(['route_name' => '删除Tag']);
    })->prefix('tags.tagsController/');
    // 文章部分
    Route::group('article', function () {
        Route::get('<id?>$', 'index')->option(['route_name' => '获取文章内容']);
        Route::get('list', 'list')->option(['route_name' => '文章列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑文章']);
        Route::post('del', 'delete')->option(['route_name' => '删除文章']);
        Route::get('author', 'getAuthor')->option(['route_name' => '获取文章作者']);
        Route::post('status', 'setStatus')->option(['route_name'  => '设置文章状态']);
    })->prefix('article.articleController/');
    // 商品部分
    Route::group('product', function () {
        Route::get('<id?>$', 'index')->option(['route_name' => '获取商品详情']);
        Route::get('list', 'list')->option(['route_name' => '商品列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑商品']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置商品状态']);
        Route::post('del', 'delete')->option(['route_name' => '删除商品']);
    })->prefix('product.productController/');
    // 用户部分
    Route::group('user', function () {
        Route::get('<id?>$', 'index')->option(['route_name' => '获取用户信息']);
        Route::get('list', 'list')->option(['route_name' => '用户列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑用户']);
        Route::post('del', 'delete')->option(['route_name' => '删除用户']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置用户状态']);
    })->prefix('user.userController/');
    // 客户管理
    Route::group('client', function () {
        Route::get('list', 'list')->option(['route_name' => '客户列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑客户']);
        Route::post('del', 'delete')->option(['route_name' => '删除客户']);
    })->prefix('user.clientController/');
    // 栏目部分
    Route::group('channel', function () {
        Route::get('<id?>$', 'index')->option(['route_name' => '栏目详情']);
        Route::get('list', 'list')->option(['route_name' => '栏目列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑栏目']);
        Route::post('del', 'delete')->option(['route_name' => '删除栏目']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置栏目状态']);
        Route::get('cate', 'getCate')->option(['route_name' => '获取指定分类的栏目']);
    })->prefix('channel.channelController/');
    Route::group('module', function () {
        Route::get('list', 'list')->option(['route_name' => '模型列表']);
        Route::post('del', 'delete')->option(['route_name' => '删除模型']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑模型']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置模型状态']);
    })->prefix('channel.moduleController/');
    // 友链部分
    Route::group('link', function () {
        Route::get('list', 'list')->option(['route_name' => '友链列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑友链']);
        Route::post('del', 'delete')->option(['route_name' => '删除友链']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置友链状态']);
    })->prefix('link.linkController/');
    // 用户权限菜单
    Route::group('auth', function () {
        Route::get('list', 'list')->option(['route_name' => '菜单列表']);
        Route::post('del', 'delete')->option(['route_name' => '删除菜单']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑菜单']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置菜单状态']);
        Route::get('routes', 'getRouteList')->option(['route_name' => '获取路由列表']);
    })->prefix('auth.authController/');
    // 用户组权限列表
    Route::group('group', function () {
        Route::get('list', 'list')->option(['route_name' => '用户组列表']);
        Route::post('del', 'delete')->option(['route_name' => '删除用户组']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑用户组']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置用户组状态']);
    })->prefix('auth.groupController/');
    // 公共接口
    Route::group('public', function () {
        Route::post('clear_log', 'clearLog')->option(['route_name' => '清除错误日志']);
        Route::post('refresh_cache', 'refreshCache')->option(['route_name' => '刷新缓存']);
    })->prefix('publicController/');
    // 行政区域
    Route::group('region', function () {
        Route::get('list', 'list')->option(['route_name' => '行政区域列表']);
        Route::post('del', 'delete')->option(['route_name' => '删除行政区域']);
        Route::get('lists', 'index')->option(['route_name' => '懒加载行政区列表']);
        Route::post('save', 'save')->option(['route_name' => '新增/编辑行政区域']);
        Route::post('status', 'setStatus')->option(['route_name' => '设置行政区域状态']);
    })->prefix('system.regionController/');
    // 个人中心
    Route::group('account', function () {
        Route::get('menu', 'menu')->option(['route_name' => '用户菜单列表']);
        Route::get('fakelist', 'fakeList')->option(['route_name' => '评论列表']);
    })->prefix('user.accountController/');
    // 系统配置
    Route::group('system', function () {
        Route::get('list', 'list')->option(['route_name' => '系统配置项列表']);
        Route::post('save', 'save')->option(['route_name' => '编辑系统配置项']);
    })->prefix('system.configController/');
    // 操作日志
    Route::group('system', function () {
        Route::get('record', 'list')->option(['route_name' => '操作日志列表']);
    })->prefix('system.systemLogsController/');
    /* 数据看板 */
    Route::group('dashboard', function () {
        Route::get('monitor', 'monitorController/index')->option(['route_name' => '监控页']);
        Route::get('analysis', 'analysisController/index')->option(['route_name' => '分析页']);
        Route::get('notice', 'workplaceController/notice')->option(['route_name' => '通知信息']);
        Route::get('workplace', 'workplaceController/index')->option(['route_name' => '工作台']);
        Route::get('activities', 'workplaceController/activities')->option(['route_name' => '活动页']);
    })->prefix('dashboard.');
    /* 数据备份 */
    Route::group('system', function () {
        Route::get('database/info', 'read')->option(['route_name' => '查看表结构']);
        Route::get('database/list', 'index')->option(['route_name' => '读取数据列表']);
        Route::post('database/backup', 'backup')->option(['route_name' => '备份数据表']);
        Route::post('database/repair', 'repair')->option(['route_name' => '修复数据表']);
        Route::post('database/revert', 'import')->option(['route_name' => '还原数据表']);
        Route::get('database/record', 'record')->option(['route_name' => '获取备份记录']);
        Route::post('database/remove', 'delete')->option(['route_name' => '删除备份记录']);
        Route::post('database/optimize', 'optimize')->option(['route_name' => '优化数据表']);
        Route::post('database/download', 'download')->option(['route_name' => '下载数据备份']);
    })->prefix('system.dataBackupController/');
    /* 文件管理 */
    Route::group('attach', function () {
        Route::get('list', 'system.attachController/list')->option(['route_name' => '文件列表']);
        Route::get('cate', 'system.attachCateController/list')->option(['route_name' => '目录列表']);
        Route::post('move', 'system.attachController/moveCate')->option(['route_name' => '移动分类']);
        Route::post('upload', 'system.attachController/upload')->option(['route_name' => '文件上传']);
        Route::post('remove', 'system.attachController/remove')->option(['route_name' => '文件删除']);
        Route::get('info', 'system.attachCateController/index')->option(['route_name' => '目录信息']);
        Route::post('stream', 'system.attachController/upStream')->option(['route_name' => '二进制上传']);
        Route::post('delete', 'system.attachCateController/delete')->option(['route_name' => '删除目录']);
        Route::post('save', 'system.attachCateController/save')->option(['route_name' => '新增/编辑目录']);
    });
})->option(['https' => true])->pattern(['id' => '\d+', 'name' => '\w+'])->middleware(
    [
        /* 跨域请求 */
        think\middleware\AllowCrossDomain::class,
        /* token签发/验证 */
        app\http\middleware\AuthTokenMiddleware::class,
        /* 用户组路由鉴权 */
        app\http\middleware\AuthCheckMiddleware::class,
        /* 记录用户操作日志 */
        app\http\middleware\ActionLogMiddleware::class,
    ]
);
