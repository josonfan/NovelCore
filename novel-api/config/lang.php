<?php
// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

return [
    // 默认语言
    'default_lang'        => env('LANG.default_lang', 'zh-cn'),
    // 自动侦测浏览器语言
    'auto_detect_browser' => true,
    // 允许的语言列表
    'allow_lang_list'     => ['zh-cn', 'en-us', 'ja-jp', 'zh-tw', 'th-th'],
    // 多语言自动侦测变量名
    'detect_var'          => 'lang',
    // 是否使用Cookie记录
    'use_cookie'          => false,
    // 多语言cookie变量
    'cookie_var'          => 'think_lang',
    // 多语言header变量
    'header_var'          => 'accept-language',
    // 扩展语言包
    'extend_list'         => [],
    // Accept-Language转义为对应语言包名称
    'accept_language'     => [
        'zh'          => 'zh-cn',
        'zh-cn'       => 'zh-cn',
        'zh-hans'     => 'zh-cn',
        'zh-Hans-CN'  => 'zh-cn',
        'zh-CN'       => 'zh-cn',
        'zh-Hans'     => 'zh-cn',
        'en'          => 'en-us',
        'en-us'       => 'en-us',
        'en-US'       => 'en-us',
        'en-GB'       => 'en-us',
        'ja'          => 'ja-jp',
        'ja-jp'       => 'ja-jp',
        'ja-JP'       => 'ja-jp',
        'th'          => 'th-th',
        'th-th'       => 'th-th',
        'th-TH'       => 'th-th',
        'zh-tw'       => 'zh-tw',
        'zh-hant'     => 'zh-tw',
        'zh-TW'       => 'zh-tw',
        'zh-Hant'     => 'zh-tw',
    ],
    // 是否支持语言分组
    'allow_group'         => false,
];
