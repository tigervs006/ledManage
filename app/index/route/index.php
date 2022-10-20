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

Route::group(function () {
    /* 网站首页 */
    Route::rule('/', 'index');
    /* 行政区域 */
    Route::rule('region', 'index/region');
    /* 单页模型 */
    Route::rule('single/<dirname?>$', 'single/index');
    /* 网站地图 */
    Route::rule('sitemap', 'index/sitemap')->option(['ext' => 'html']);
    /* 网站搜索 */
    Route::rule('search/$', 'index/search')->pattern(['keyword' => '\S+']);
    /* 搜索分页 */
    Route::rule('search/page/<current>$', 'index/search')->pattern(['keyword' => '\S+']);
    /* 行业模型伪静态 */
    Route::group('area', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'area/detail');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'area/list');
        /* 行业列表 */
        Route::rule('<dirname?>/$', 'area/list')->name('areaList');
        /* 行业详情 */
        Route::rule('<dirname?><id>$', 'area/index')->name('areaDetail');
    });
    /* 案例模型伪静态 */
    Route::group('case', function () {
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'cases/list');
        /* 案例列表 */
        Route::rule('<dirname?>/$', 'cases/list')->name('caseList');
        /* 案例详情 */
        Route::rule('<id>$', 'cases/index')->name('caseDetail');
    });
    /* 标签模型伪静态 */
    Route::group('tags', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'tags/detail');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'tags/list');
        /* 标签列表 */
        Route::rule('<dirname?>/$', 'tags/list')->name('tagsList');
        /* 标签详情 */
        Route::rule('<dirname?><id>$', 'tags/index')->name('tagsDetail');
    });
    /* 视频模型伪静态 */
    Route::group('video', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'video/detail');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'video/list');
        /* 视频列表 */
        Route::rule('<dirname?>/$', 'video/list')->name('videoList');
        /* 视频详情 */
        Route::rule('<dirname?><id>$', 'video/index')->name('videoDetail');
    });
    /* 文档模型伪静态 */
    Route::group('news', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'industry/index');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'industry/list');
        /* 文档列表 */
        Route::rule('<dirname?>/$', 'industry/list')->name('newsList');
        /* 文章详情 */
        Route::rule('<dirname?><id>$', 'industry/index')->name('newsDetail');
    });
    /* 灯具检测伪静态 */
    Route::group('testing', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'testing/index');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'testing/list');
        /* 文档列表 */
        Route::rule('<dirname?>/$', 'testing/list')->name('testingList');
        /* 文章详情 */
        Route::rule('<dirname?><id>$', 'testing/index')->name('testingDetail');
    });
    /* 灯具认证伪静态 */
    Route::group('attestation', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'testing/index');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'testing/list');
        /* 文档列表 */
        Route::rule('<dirname?>/$', 'testing/list')->name('attestationList');
        /* 文章详情 */
        Route::rule('<dirname?><id>$', 'testing/index')->name('attestationDetail');
    });
    /* 光源驱动伪静态 */
    Route::group('illuminant', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'testing/index');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'testing/list');
        /* 文档列表 */
        Route::rule('<dirname?>/$', 'testing/list')->name('illuminantList');
        /* 文章详情 */
        Route::rule('<dirname?><id>$', 'testing/index')->name('illuminantDetail');
    });
    /* 关于我们伪静态 */
    Route::group('about', function () {
        /* 企业简介 */
        Route::rule('<dirname?>/$', 'about/index')->name('index');
    });
    /* 服务支持伪静态 */
    Route::group('support', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'support/detail');
        /* 视频播放 */
        Route::rule('video/<id>$', 'support/video')->name('videoPlay');
        /* 服务列表 */
        Route::rule('<dirname?>/$', 'support/list')->name('supportList');
        /* 服务详情 */
        Route::rule('<dirname?><id>$', 'support/index')->name('supportDetail');
    });
    /* 商品模型伪静态 */
    Route::group('product', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'product/detail');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'product/list');
        /* 商品列表 */
        Route::rule('<dirname?>/$', 'product/list')->name('productList');
        /* 商品详情 */
        Route::rule('<dirname?><id>$', 'product/index')->name('productDetail');
    });
    /* 图集模型伪静态 */
    Route::group('images', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'images/detail');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'images/list');
        /* 图集列表 */
        Route::rule('<dirname?>/$', 'images/list')->name('imagesList');
        /* 图集详情 */
        Route::rule('<dirname?><id>$', 'images/index')->name('imagesDetail');
    });
    /* 下载模型伪静态 */
    Route::group('download', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'download/detail');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'download/list');
        /* 下载列表 */
        Route::rule('<dirname?>/$', 'download/list')->name('downloadList');
        /* 下载详情 */
        Route::rule('<dirname?><id>$', 'download/index')->name('downloadDetail');
    });
    /* 留言模型伪静态 */
    Route::group('guestbook', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'guestbook/detail');
        /* 分页列表 */
        Route::rule('<dirname?>page/<current>$', 'guestbook/list');
        /* 留言列表 */
        Route::rule('<dirname?>/$', 'guestbook/list')->name('guestbookList');
        /* 留言详情 */
        Route::rule('<dirname?><id>$', 'guestbook/index')->name('guestbookDetail');
    });
    /* miss路由 */
    Route::miss(function() { return '404 Not Found!'; });
})->option(['method' => 'get', 'https' => true])->pattern(['id' => '\d+', 'current' => '\d+', 'dirname' => '[(a-zA-Z\_\-\/)|(?!page\/)]*']);
