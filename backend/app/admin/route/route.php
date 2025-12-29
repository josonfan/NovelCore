<?php
use think\facade\Route;

/**
 * Admins 接口
 * - add: 创建管理员（POST）
 * - detail: 管理员详情（GET|POST）
 * - index: 管理员列表（GET|POST）
 * - assignRoles: 为管理员绑定角色（POST）
 */
Route::group('Admins', function () {
    Route::post('add', 'Admins/add')->middleware('AdminAuth')->middleware('RbacAuth');    
    Route::rule('detail', 'Admins/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('index', 'Admins/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::post('assignRoles', 'Admins/assignRoles')->middleware('AdminAuth')->middleware('RbacAuth');
});
/**
 * Login 接口（认证与个人信息）
 * - login: 登录（POST|GET）
 * - logout: 退出（POST|GET）
 * - info: 个人信息（GET|POST）
 * - update: 更新个人信息（POST）
 * - changePassword: 修改密码（POST）
 * - context: 登录后上下文（GET|POST，用户+角色+权限+菜单聚合）
 */
Route::group('Login', function () {    
    Route::rule('login', 'Login/login', 'POST|GET');    
    Route::rule('logout', 'Login/logout', 'POST|GET')->middleware('AdminAuth');
    Route::rule('info', 'Login/info', 'GET|POST')->middleware('AdminAuth');
    Route::rule('update', 'Login/update', 'POST')->middleware('AdminAuth');
    Route::rule('changePassword', 'Login/changePassword', 'POST')->middleware('AdminAuth');
    Route::rule('context', 'Login/context', 'GET|POST')->middleware('AdminAuth');
});
/**
 * Roles 接口（角色管理）
 * - create: 创建角色（POST）
 * - assignPermissions: 绑定权限（POST）
 * - index/detail/update/delete: 列表/详情/更新/删除
 */
Route::group('Roles', function () {
    Route::post('create', 'Roles/create')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::post('assignPermissions', 'Roles/assignPermissions')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('index', 'Roles/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Roles/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Roles/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Roles/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * Permissions 接口（权限点管理）
 * - create/index/detail/update/delete
 */
Route::group('Permissions', function () {
    Route::post('create', 'Permissions/create')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('index', 'Permissions/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Permissions/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Permissions/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Permissions/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * Menus 接口（菜单管理与权限可见性）
 * - index: 返回当前管理员可见菜单树（GET|POST）
 * - list/detail/create/update/delete/bindPermissions/options
 */
Route::group('Menus', function () {
    Route::rule('index', 'Menus/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('list', 'Menus/list', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Menus/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Menus/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Menus/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Menus/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('bindPermissions', 'Menus/bindPermissions', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('options', 'Menus/options', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * Sites 接口（站点管理）
 */
Route::group('Sites', function () {
    Route::rule('index', 'Sites/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Sites/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Sites/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Sites/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Sites/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'Sites/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('init', 'Sites/init', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});

/**
 * DomainList 接口（域名管理）
 */
Route::group('DomainList', function () {
    Route::rule('index', 'DomainList/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'DomainList/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'DomainList/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'DomainList/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'DomainList/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('StorageConfig', function () {
    Route::rule('index', 'StorageConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'StorageConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'StorageConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'StorageConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});

Route::group('EmailConfig', function () {
    Route::rule('index', 'EmailConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'EmailConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'EmailConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'EmailConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});

Route::group('SearchConfig', function () {
    Route::rule('index', 'SearchConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'SearchConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'SearchConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'SearchConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});

Route::group('AiConfig', function () {
    Route::rule('index', 'AiConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'AiConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'AiConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'AiConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});

Route::group('RecommendationConfig', function () {
    Route::rule('index', 'RecommendationConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'RecommendationConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'RecommendationConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'RecommendationConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});

Route::group('CustomerServiceConfig', function () {
    Route::rule('index', 'CustomerServiceConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'CustomerServiceConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'CustomerServiceConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'CustomerServiceConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});
Route::group('Upload', function () {
    Route::rule('file', 'Upload/file', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('CommentReviewConfig', function () {
    Route::rule('index', 'CommentReviewConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'CommentReviewConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'CommentReviewConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'CommentReviewConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});

Route::group('TelegramAuditConfig', function () {
    Route::rule('index', 'TelegramAuditConfig/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'TelegramAuditConfig/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detailBySite', 'TelegramAuditConfig/detailBySite', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('save', 'TelegramAuditConfig/save', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    
});
/**
 * PaymentChannel 接口（支付通道管理）
 */
Route::group('PaymentChannel', function () {
    Route::rule('index', 'PaymentChannel/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'PaymentChannel/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'PaymentChannel/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'PaymentChannel/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'PaymentChannel/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'PaymentChannel/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * SiteUsers 接口（用户管理）
 */
Route::group('SiteUsers', function () {
    Route::rule('index', 'SiteUsers/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'SiteUsers/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('setStatus', 'SiteUsers/setStatus', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});
/**
 * SiteComments 接口（评论管理）
 */
Route::group('SiteComments', function () {
    Route::rule('index', 'SiteComments/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'SiteComments/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('audit', 'SiteComments/audit', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});
/**
 * SiteOrders 接口（订单管理）
 */
Route::group('SiteOrders', function () {
    Route::rule('index', 'SiteOrders/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'SiteOrders/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'SiteOrders/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});
Route::group('SiteTickets', function () {
    Route::rule('index', 'SiteTickets/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'SiteTickets/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('process', 'SiteTickets/process', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('types', 'SiteTickets/types', 'GET|POST')->middleware('AdminAuth');
    Route::rule('statuses', 'SiteTickets/statuses', 'GET|POST')->middleware('AdminAuth');
});
/**
 * SiteStats 接口（站点统计管理）
 */
Route::group('SiteStats', function () {
    Route::rule('index', 'SiteStats/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'SiteStats/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('summary', 'SiteStats/summary', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('series', 'SiteStats/series', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
});


/**
 * Vip 接口（会员管理）
 */
Route::group('Vip', function () {
    Route::rule('index', 'Vip/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Vip/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Vip/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Vip/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Vip/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'Vip/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/** 
 * Categories 接口（分类管理）
 */
Route::group('Categories', function () {
    Route::rule('index', 'Categories/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Categories/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Categories/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Categories/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Categories/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'Categories/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('options', 'Categories/options', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * Tags 接口（标签管理）
 */
Route::group('Tags', function () {
    Route::rule('index', 'Tags/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Tags/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Tags/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Tags/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Tags/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'Tags/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('options', 'Tags/options', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * Novels 接口（小说管理）
 */
Route::group('Novels', function () {
    Route::rule('index', 'Novels/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Novels/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Novels/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Novels/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Novels/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('bindTags', 'Novels/bindTags', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('audit', 'Novels/audit', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * Chapters 接口（章节管理）
 */
Route::group('Chapters', function () {
    Route::rule('index', 'Chapters/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Chapters/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('content', 'Chapters/content', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Chapters/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Chapters/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Chapters/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

/**
 * 未匹配路由的兜底处理
 */
Route::miss(function(){
    return json(['code'=>404,'msg'=>'接口不存在']);
});
