<?php
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// API 路由分组
Route::group('api', function () {
    // 用户注册与登录
    Route::post('register', 'api.User/register');
    Route::post('login', 'api.User/login');

    // 分类与标签
    Route::get('categories', 'api.Category/index');
    Route::get('tags', 'api.Tag/index');

    // 小说列表与详情
    Route::get('novels', 'api.Novel/index');
    Route::get('novels/:novelId', 'api.Novel/show');

    // 章节列表与内容
    Route::get('novels/:novelId/chapters', 'api.Chapter/index');
    Route::get('novels/:novelId/chapters/:chapterId', 'api.Chapter/show');

    // 阅读进度与搜索（部分需登录）
    Route::get('search', 'api.Search/index');
    Route::get('novels/:novelId/progress', 'api.Reading/getProgress')->middleware(\app\middleware\AuthMiddleware::class);
    Route::post('novels/:novelId/progress', 'api.Reading/saveProgress')->middleware(\app\middleware\AuthMiddleware::class);
    Route::get('domains', 'api.Domain/index');
    Route::get('user/summary', 'api.UserSummary/summary')->middleware(\app\middleware\AuthMiddleware::class);

    // 需要登录的接口
    Route::group(function () {
        // 用户
        Route::post('logout', 'api.User/logout');
        Route::get('user/info', 'api.User/info');
        Route::get('user/favorites', 'api.Favorite/list');
        Route::get('user/reading-history', 'api.Reading/listHistory');
        Route::post('user/reading-history', 'api.Reading/saveHistory');

        // 点赞小说
        Route::post('novels/:novelId/like', 'api.Like/like');
        Route::delete('novels/:novelId/like', 'api.Like/unlike');

        // 收藏小说
        Route::post('novels/:novelId/favorite', 'api.Favorite/favorite');
        Route::delete('novels/:novelId/favorite', 'api.Favorite/unfavorite');

        // 关注作者/小说
        Route::post('authors/:authorId/follow', 'api.Follow/followAuthor');
        Route::delete('authors/:authorId/follow', 'api.Follow/unfollowAuthor');
        Route::post('novels/:novelId/follow', 'api.Follow/followNovel');
        Route::delete('novels/:novelId/follow', 'api.Follow/unfollowNovel');

        // 发表评论与评论点赞
        Route::post('novels/:novelId/comments', 'api.Comment/store');
        Route::post('comments/:commentId/like', 'api.Comment/like');
        Route::delete('comments/:commentId/like', 'api.Comment/unlike');
    })->middleware(\app\middleware\AuthMiddleware::class);

    // 评论列表无需登录
    Route::get('novels/:novelId/comments', 'api.Comment/index');

    // 后台只读接口，受 AdminAuth 保护
    Route::group('admin', function () {
        Route::get('search-logs', 'api.AdminData/searchLogsList');
        Route::get('favorites', 'api.AdminData/favoritesList');
        Route::get('reading-history', 'api.AdminData/readingHistoryList');
        Route::get('read-logs', 'api.AdminData/readLogsList');
        Route::get('device-logs', 'api.AdminData/deviceLogsList');
        Route::get('login-logs', 'api.AdminData/loginLogsList');
        Route::get('novels', 'api.AdminNovel/index');
        Route::put('comments/:id', 'api.AdminComment/updateStatus');
        // 配置下发
        Route::post('config/storage', 'admin.Config/saveStorage');
        Route::post('config/email', 'admin.Config/saveEmail');
        Route::post('config/search', 'admin.Config/saveSearch');
        Route::post('config/comment', 'admin.Config/saveComment');
        Route::post('config/ai', 'admin.Config/saveAi');
        Route::post('config/customer-service', 'admin.Config/saveCustomerService');
        Route::post('config/recommendation', 'admin.Config/saveRecommendation');
        Route::post('config/domains', 'admin.Config/saveDomains');
        Route::get('stats/daily', 'admin.Stats/getDailyStats');
    })->middleware(\app\middleware\AdminAuthMiddleware::class);
});
