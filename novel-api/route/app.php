<?php
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// API 路由分组
Route::group('api', function () {
    // 用户注册与登录
    Route::rule('User/register', 'User/register', 'POST');
    Route::rule('User/login', 'User/login', 'POST');
    // 忘记密码
    Route::rule('User/sendForgotPasswordCode', 'User/sendForgotPasswordCode', 'POST');
        // ->middleware(\app\middleware\ApiThrottle::class, ['visit_rate' => '1/m', 'key_field' => 'username']);
    Route::rule('User/resetPassword', 'User/resetPassword', 'POST')->middleware(\app\middleware\ValidateEmailCode::class);
    // 账户查询邮箱 注意邮箱部分隐藏 忘记密码配套使用
    Route::rule('User/getEmail', 'User/getEmail', 'POST');



    // 分类与标签（统一 Controller/Action，参数走 Body）
    Route::rule('Category/index', 'Category/index', 'POST');
    Route::rule('Category/info', 'Category/info', 'POST');
    Route::rule('Tag/index', 'Tag/index', 'POST');
    Route::rule('Tag/info', 'Tag/info', 'POST');
    

    
    // VIP 套餐列表与详情
    Route::rule('Vip/index', 'Vip/index', 'POST');
    Route::rule('Vip/info', 'Vip/info', 'POST');
    // 支付渠道列表与详情
    Route::rule('PaymentChannel/index', 'PaymentChannel/index', 'POST');
    Route::rule('PaymentChannel/info', 'PaymentChannel/info', 'POST');
    // 订单
    Route::rule('Order/notify/:icon_iden/:channel_id', 'Order/notify', 'POST');


    // 小说列表与详情（统一 Controller/Action，参数走 Body）
    Route::rule('Novel/index', 'Novel/index', 'POST')->middleware(\app\middleware\NoAuth::class);
    Route::rule('Novel/show', 'Novel/show', 'POST')->middleware(\app\middleware\NoAuth::class);
    // 章节列表与内容（统一 Controller/Action，参数走 Body）
    Route::rule('Chapter/index', 'Chapter/index', 'POST')->middleware(\app\middleware\NoAuth::class);
    

    // 阅读进度与搜索（统一 Controller/Action）
    Route::rule('Search/index', 'Search/index', 'POST')->middleware(\app\middleware\NoAuth::class);
    Route::rule('Search/hotKeywords', 'Search/hot_keywords', 'GET')->middleware(\app\middleware\NoAuth::class);
    Route::rule('Reading/getProgress', 'Reading/getProgress', 'POST')->middleware(\app\middleware\Auth::class);
    Route::rule('Reading/saveProgress', 'Reading/saveProgress', 'POST')->middleware(\app\middleware\Auth::class);
    Route::rule('Domain/index', 'Domain/index', 'POST');
    Route::rule('UserSummary/summary', 'UserSummary/summary', 'GET')->middleware(\app\middleware\Auth::class);
    Route::rule('Sync/receive', 'Sync/receive', 'POST')->middleware(\app\middleware\AdminPushAuth::class);
    Route::rule('Health/index', 'Health/index', 'GET')->middleware(\app\middleware\AdminPushAuth::class);
    Route::rule('Ticket/types', 'Ticket/types', 'GET');
    Route::rule('Ticket/statuses', 'Ticket/statuses', 'GET');
    // 投诉建议提交（无需登录）
    Route::rule('Feedback/add', 'Feedback/add', 'POST')
        ->middleware(\app\middleware\NoAuth::class)
        ->middleware(\app\middleware\ApiThrottle::class, ['visit_rate' => '1/m']);
    // 投诉建议类型与状态
    Route::rule('Feedback/types', 'Feedback/types', 'GET');
    Route::rule('Feedback/statuses', 'Feedback/statuses', 'GET');
    
    
    // 需要登录的接口
    Route::group(function () {
        // 文件上传
        Route::rule('Upload/file', 'Upload/file', 'POST');

        // 用户
        Route::rule('User/logout', 'User/logout', 'POST');
        Route::rule('User/info', 'User/info', 'GET');
        Route::rule('User/update', 'User/update', 'POST');
        Route::rule('User/sendEmailCode', 'User/sendEmailCode', 'POST')
            ->middleware(\app\middleware\ApiThrottle::class, ['visit_rate' => '1/m', 'key_field' => 'email']);// 发送邮箱验证码
        Route::rule('User/bindEmail', 'User/bindEmail', 'POST')->middleware(\app\middleware\ValidateEmailCode::class);// 绑定邮箱
        // 用户登录日志
        Route::rule('User/loginLogs', 'User/getLoginLogs', 'POST');
        // 用户设备日志
        Route::rule('User/deviceLogs', 'User/getDeviceLogs', 'POST');
        Route::rule('User/updatePassword', 'User/updatePassword', 'POST');// 重置密码



        // 小说用户状态
        Route::rule('Novel/userStatus', 'Novel/userStatus', 'POST');
        // 订单
        Route::rule('Order/add', 'Order/add', 'POST');
        Route::rule('Order/affirmBuy', 'Order/affirmBuy', 'POST');
        Route::rule('Order/info', 'Order/info', 'POST');
        Route::rule('Favorite/list', 'Favorite/list', 'POST');
        Route::rule('Reading/listHistory', 'Reading/listHistory', 'POST');
        Route::rule('Reading/saveHistory', 'Reading/saveHistory', 'POST');
        Route::rule('Novel/userStatus', 'Novel/userStatus', 'POST');
        // 章节内容
        Route::rule('Chapter/content', 'Chapter/content', 'POST');
        Route::rule('Chapter/show', 'Chapter/show', 'POST');


        // 点赞小说
        Route::rule('Like/like', 'Like/like', 'POST');
        Route::rule('Like/unlike', 'Like/unlike', 'POST');
        Route::rule('Like/getList', 'Like/getList', 'POST');



        // 收藏小说
        Route::rule('Favorite/favorite', 'Favorite/favorite', 'POST');
        Route::rule('Favorite/unfavorite', 'Favorite/unfavorite', 'POST');

        // 工单
        Route::rule('Ticket/add', 'Ticket/add', 'POST')
            ->middleware(\app\middleware\ApiThrottle::class, ['visit_rate' => '1/m']);
        Route::rule('Ticket/list', 'Ticket/list', 'POST');
        Route::rule('Ticket/info', 'Ticket/info', 'POST');
        Route::rule('Ticket/reply', 'Ticket/reply', 'POST');
        Route::rule('Ticket/replies', 'Ticket/replies', 'POST');
        Route::rule('Ticket/evaluate', 'Ticket/evaluate', 'POST');
        // 投诉建议（我的）
        Route::rule('Feedback/list', 'Feedback/list', 'POST');
        Route::rule('Feedback/info', 'Feedback/info', 'POST');

        // 关注作者/小说
        Route::rule('Follow/followAuthor', 'Follow/followAuthor', 'POST');
        Route::rule('Follow/unfollowAuthor', 'Follow/unfollowAuthor', 'POST');
        Route::rule('Follow/followNovel', 'Follow/followNovel', 'POST');
        Route::rule('Follow/unfollowNovel', 'Follow/unfollowNovel', 'POST');

        // 发表评论与评论点赞
        Route::rule('Comment/store', 'Comment/store', 'POST')
            ->middleware(\app\middleware\ApiThrottle::class, ['visit_rate' => '3/m']);
        Route::rule('Comment/getMyList', 'Comment/getMyList', 'POST');
        Route::rule('Comment/like', 'Comment/like', 'POST');
        Route::rule('Comment/unlike', 'Comment/unlike', 'POST');

        
        // 消息中心
        Route::rule('Message/index', 'Message/index', 'POST');
        Route::rule('Message/info', 'Message/info', 'POST');
        Route::rule('Message/unreadCount', 'Message/unreadCount', 'GET');
    })->middleware(\app\middleware\Auth::class);

    // 评论列表无需登录（统一 Controller/Action）
    Route::rule('Comment/index', 'Comment/index', 'POST');
    // 支付回调无需登录
    Route::rule('Order/notify', 'Order/notify', 'POST');
    // App 初始化
    Route::rule('App/init', 'App/init', 'POST')->middleware(\app\middleware\NoAuth::class);
    // 获取协议内容
    Route::rule('App/agreement', 'App/agreement', 'POST')->middleware(\app\middleware\NoAuth::class);
    // 工单类型与状态（无参数，可 GET）
    Route::rule('Ticket/types', 'Ticket/types', 'GET')->middleware(\app\middleware\NoAuth::class);
    Route::rule('Ticket/statuses', 'Ticket/statuses', 'GET')->middleware(\app\middleware\NoAuth::class);

    // 后台只读接口，受 AdminAuth 保护
    // Route::group('admin', function () {
    //     Route::get('search-logs', 'AdminData/searchLogsList');
    //     Route::get('favorites', 'AdminData/favoritesList');
    //     Route::get('reading-history', 'AdminData/readingHistoryList');
    //     Route::get('read-logs', 'AdminData/readLogsList');
    //     Route::get('device-logs', 'AdminData/deviceLogsList');
    //     Route::get('login-logs', 'AdminData/loginLogsList');
    //     Route::get('novels', 'AdminNovel/index');
    //     Route::put('comments/:id', 'AdminComment/updateStatus');
    //     // 配置下发
    //     Route::post('config/storage', 'admin.Config/saveStorage');
    //     Route::post('config/email', 'admin.Config/saveEmail');
    //     Route::post('config/search', 'admin.Config/saveSearch');
    //     Route::post('config/comment', 'admin.Config/saveComment');
    //     Route::post('config/ai', 'admin.Config/saveAi');
    //     Route::post('config/customer-service', 'admin.Config/saveCustomerService');
    //     Route::post('config/recommendation', 'admin.Config/saveRecommendation');
    //     Route::post('config/domains', 'admin.Config/saveDomains');
    //     Route::get('stats/daily', 'admin.Stats/getDailyStats');
        
    // })->middleware(\app\middleware\AdminAuth::class);
})->middleware(\think\middleware\Throttle::class);

/**
 * 未匹配路由的兜底处理
 */
Route::miss(function(){
    return json(['code'=>404,'msg'=>'接口不存在']);
});
