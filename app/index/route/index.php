<?php
use think\facade\Route;

Route::group(function () {
    /* 网站首页 */
    Route::rule('/', 'index');
    /* 行政区域 */
    Route::rule('region', 'index/region');
    /* 单页模型伪静态 */
    Route::rule('single/<dirname?>$', 'single/index');
    /* 行业模型伪静态 */
    Route::group('area', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'area/detail');
        /* 行业列表 */
        Route::rule('<dirname?>/$', 'area/index')->name('areaList');
        /* 行业详情 */
        Route::rule('<dirname?><id>$', 'area/detail')->name('areaDetail');
    });
    /* 案例模型伪静态 */
    Route::group('case', function () {
        /* 案例列表 */
        Route::rule('<dirname?>/$', 'cases/index')->name('caseList');
        /* 案例详情 */
        Route::rule('<id>$', 'cases/detail')->name('caseDetail');
    });
    /* 标签模型伪静态 */
    Route::group('tags', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'tags/detail');
        /* 标签列表 */
        Route::rule('<dirname?>/$', 'tags/index')->name('tagsList');
        /* 标签详情 */
        Route::rule('<dirname?><id>$', 'tags/detail')->name('tagsDetail');
    });
    /* 视频模型伪静态 */
    Route::group('video', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'video/detail');
        /* 视频列表 */
        Route::rule('<dirname?>/$', 'video/index')->name('videoList');
        /* 视频详情 */
        Route::rule('<dirname?><id>$', 'video/detail')->name('videoDetail');
    });
    /* 文档模型伪静态 */
    Route::group('news', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'industry/detail');
        /* 文档列表 */
        Route::rule('<dirname?>/$', 'industry/index')->name('newsList');
        /* 文章详情 */
        Route::rule('<dirname?><id>$', 'industry/detail')->name('newsDetail');
    });
    /* 关于我们伪静态 */
    Route::group('about', function () {
        /* 企业简介 */
        Route::rule('index/$', 'about/index')->name('index');
        /* 服务体系 */
        Route::rule('system/$', 'about/system')->name('system');
        /* 生产制造 */
        Route::rule('produce/$', 'about/produce')->name('produce');
    });
    /* 服务支持伪静态 */
    Route::group('support', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'support/detail');
        /* 视频播放 */
        Route::rule('video/<id>$', 'support/video')->name('videoPlay');
        /* 服务列表 */
        Route::rule('<dirname?>/$', 'support/index')->name('supportList');
        /* 服务详情 */
        Route::rule('<dirname?><id>$', 'support/detail')->name('supportDetail');
    });
    /* 商品模型伪静态 */
    Route::group('product', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'product/detail');
        /* 商品列表 */
        Route::rule('<dirname?>/$', 'product/list')->name('productList');
        /* 商品详情 */
        Route::rule('<dirname?><id>$', 'product/detail')->name('productDetail');
    });
    /* 图集模型伪静态 */
    Route::group('images', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'images/detail');
        /* 图集列表 */
        Route::rule('<dirname?>/$', 'images/list')->name('imagesList');
        /* 图集详情 */
        Route::rule('<dirname?><id>$', 'images/detail')->name('imagesDetail');
    });
    /* 下载模型伪静态 */
    Route::group('download', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'download/detail');
        /* 下载列表 */
        Route::rule('<dirname?>/$', 'download/list')->name('downloadList');
        /* 下载详情 */
        Route::rule('<dirname?><id>$', 'download/detail')->name('downloadDetail');
    });
    /* 留言模型伪静态 */
    Route::group('guestbook', function () {
        /* 顶级栏目 */
        Route::rule('<id>$', 'guestbook/detail');
        /* 留言列表 */
        Route::rule('<dirname?>/$', 'guestbook/list')->name('guestbookList');
        /* 留言详情 */
        Route::rule('<dirname?><id>$', 'guestbook/detail')->name('guestbookDetail');
    });
    /* miss路由 */
    Route::miss(function() { return '404 Not Found!'; });
})->option(['method' => 'get', 'https' => true])->pattern(['id' => '\d+', 'dirname' => '[\w\/]+']);
