<?php

namespace app\console\validate;

use think\validate;

class ArticleValidator extends Validate
{
    protected $regex = [
        'url'   => '[\/\/]{2}\w.*?'
    ];

    protected $rule =   [
        'cid'           => 'require',
        'author'        => 'require',
        'title'         => 'require|min:10',
        'keywords'      => 'require|min:10',
        'description'   => 'require|min:50',
        'content'       => 'require|min:100',
        'litpic'        => 'require|regex:url'
    ];

    protected $message  =   [
        'cid.require'           => '请选择文章发布的栏目',
        'author.require'        => '请为文章署名',
        'title.require'         => '文章标题不得为空',
        'title.min'             => '文章标题不得少于10个字',
        'keywords.require'      => '关键词不得为空',
        'keywords.min'          => '关键词不得少于10个字',
        'description.require'   => '文章描述不得为空',
        'description.min'       => '文章描述不得少于50个字',
        'content.require'       => '文章内容不得为空',
        'content.min'           => '文章内容不得少于100个字',
        'litpic.require'        => '文章封面不得为空',
        'litpic.regex'          => '缩略图网址错误，只需截取[//]后面的网址则可',
    ];

    protected $scene = [
        'save' => ['cid', 'author', 'litpic', 'title', 'keywords', 'description', 'content']
    ];
}
