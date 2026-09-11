# NovelCore — 用户端 API

面向 C 端的用户 API 服务，基于 PHP 8.0+ 和 ThinkPHP 8。提供小说阅读、章节浏览、评论、收藏、点赞、工单、反馈、VIP 订阅、订单、搜索、多语言等完整用户链路。

## 技术栈

- **框架**: ThinkPHP 8
- **ORM**: think-orm 3.x/4.x
- **鉴权**: firebase/php-jwt（JWT）
- **缓存**: predis/predis（Redis）
- **队列**: baiy/think-async
- **搜索**: elasticsearch 8.7 / MySQL 双后端（SearchServiceFactory 工厂切换）
- **邮件**: daniel-zahariev/php-aws-ses
- **UUID**: ramsey/uuid
- **其他**: guzzlehttp/guzzle（HTTP）、geoip2（IP 地理）、think-throttle（限流）

## 目录结构

```
app/
├── controller/               # C 端控制器
│   ├── Novel.php / Chapter.php / Category.php / Tag.php
│   ├── Comment.php / Like.php / Favorite.php / Follow.php
│   ├── User.php / Vip.php / Order.php
│   ├── Ticket.php / Feedback.php / Message.php
│   ├── Search.php / Reading.php
│   └── Common.php / Index.php / Health.php / Sync.php
├── admin/controller/         # 后台只读接口（Config / Stats）
├── model/                    # ORM 模型（BaseModel / CacheModel 封装异步 CRUD）
├── service/
│   ├── search/               # 搜索服务（ES / MySQL / Factory / Interface）
│   ├── JwtService.php        # JWT 生成与解析
│   ├── UserAuthService.php   # 登录注册、邮箱验证码
│   ├── ContentService.php    # 跨模型聚合（小说详情组装）
│   ├── SyncService.php       # 内容同步接收
│   ├── StatsService.php      # 统计数据
│   └── ...
├── middleware/
│   ├── Auth.php              # JWT 鉴权（支持 Authorization: Bearer）
│   ├── AdminAuth.php         # 后台接口鉴权
│   ├── Cors.php              # 跨域
│   ├── ApiThrottle.php       # 频率限制
│   ├── BotBlock.php          # 爬虫拦截
│   ├── ApiAccessLog.php      # 请求日志（生成 X-Request-Id）
│   └── SiteInit.php          # 站点初始化检查
├── lang/                     # 多语言包（zh-cn / en-us / ja-jp / zh-tw / th-th）
└── command/                  # 控制台命令（DailyStats / DataSync / SyncPush ...）

config/                       # jwt / redis / database / sync / site / throttle ...
route/app.php                 # 路由定义（/api + /api/admin 分组）
```

## 关键设计

### 异步 CRUD（CacheModel）

统一通过 `CacheModel` 封装的接口操作数据库，不直接写 SQL：

| 操作 | 方法 | 说明 |
|------|------|------|
| 新增 | `writeById(null, $data)` | 同步写缓存 → 入队异步插库 |
| 编辑 | `writeById($id, $data)` | 同步更新缓存 → 入队异步更新 |
| 删除 | `deleteById($id)` | 清理缓存 → 入队异步删除 |
| 读取 | `infoById($id, $fields)` | 优先读缓存，MISS 查库回写 |

### 搜索服务（工厂模式）

```php
$service = SearchServiceFactory::create();  // 根据配置返回 ES 或 MySQL 实现
$service->search($keyword, $options);
```

### 多语言

- Query 变量 `?lang=<locale>` 或 Header `think-lang: <locale>` 切换
- 错误响应自动翻译（`lang('未登录或令牌无效')`）

### 请求头约定

| Header | 用途 |
|--------|------|
| `Authorization` | JWT 令牌（支持 `Bearer` 前缀自动剥离） |
| `X-Device-Id` | 设备指纹（登录日志 / 设备画像） |
| `Device-Brand` / `Device-Model` / `OS` | 设备信息 |
| `Client-Version` | 客户端版本号 |
| `think-lang` | 多语言选择 |

## 快速运行

```bash
# 安装依赖
composer install

# 配置环境
cp .example.env .env   # 替换密码、密钥、域名等占位符

# 开发启动（8080 端口）
php -S 127.0.0.1:8080 -t public public/router.php

# 日统计重建
php think UserWeekStatsRebuild
```

## 开发规范

- 控制器继承 `app\controller\Common`，统一 `$this->request->param()` 取参
- 业务逻辑落 Service 层，数据访问落 Model 层
- 列表查询：`Model::getList($where, $field, $orderby, $limit, $page)`
- 详情查询：先查主键，再 `infoById($id, $fields)` 取指定字段
- 所有带参数接口统一用 `POST` + `application/json` Body
- 提交遵循 Conventional Commits
