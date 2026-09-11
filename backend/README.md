# NovelCore — 后台管理服务

NovelCore 平台的后台管理 API，基于 PHP 8.0+ 和 ThinkPHP 8 多应用模式。提供站点管理、内容管理（小说/章节/分类/标签）、RBAC 权限、菜单、工单、反馈、评论审核、VIP 订阅、统计等完整管理能力。

## 技术栈

- **框架**: ThinkPHP 8（think-multi-app 多应用）
- **ORM**: think-orm 3.x/4.x
- **鉴权**: lcobucci/jwt（JWT）
- **缓存**: Redis（自定义 CacheModel，先写缓存 + 异步持久化）
- **队列**: baiy/think-async
- **搜索**: elasticsearch 8.7
- **其他**: think-throttle（限流）、geoip2（IP 地理）、pinyin（拼音）、AWS SDK（存储）

## 目录结构

```
app/
├── admin/                    # 后台管理应用
│   ├── controller/           # 控制器（继承 Backend，统一 ajaxReturn）
│   ├── service/              # 业务服务（静态方法，编排校验与落库）
│   ├── validate/             # 场景化校验器
│   ├── middleware/           # JwtAuth / RbacAuth 鉴权中间件
│   ├── model/                # 模块内语义化模型（继承 common 公共模型）
│   ├── route/route.php       # 路由（分组 + 中间件绑定）
│   └── config/middleware.php # 中间件别名注册
├── api/                      # 公共接口应用（站点注册 / 同步接收）
├── common/
│   ├── model/                # 公共模型（BaseModel / CacheModel / 所有实体）
│   ├── service/              # 公共服务（JwtService / SyncService / SyncExecutor）
│   └── middleware/           # ServiceWhitelist / ResponseTime
└── command/                  # 控制台命令（SyncInit / SyncPush / PushToElastic）

config/                       # 配置文件（database / cache / queue / sync / middleware）
extend/                       # 扩展类（ElasticService / GeoIP2 / StorageClient）
public/                       # Web 入口
```

## 关键设计

### 读写模型（CacheModel）

- **写入**：先写 Redis（TTL 来自 `CACHE.TTL`），再通过自定义队列异步落库
- **读取**：优先 `infoById(id)` 读缓存，MISS 则查库回写
- **删除**：`deleteById(id)` 同时清理缓存并触发同步队列

### RBAC 权限

- 数据表：`roles` / `permissions` / `role_permission` / `admin_role`
- 模型表名：`permissions`（`resource + action + field` 为业务唯一键）
- 路由绑定：业务接口链式 `->middleware('RbacAuth')`

### 内容同步

- `sync_queue` 表记录待同步任务（`site_id` + `content_type` + `content_id`）
- `CacheModel::persistById` 根据表名自动映射并入队
- 消费命令：`php think SyncPush`

## 快速运行

```bash
# 安装依赖
composer install

# 配置环境
cp .example.env .env   # 替换密码、密钥等占位符

# 开发启动
php think run          # http://127.0.0.1:8000

# 同步推送（消费队列）
php think SyncPush
```

## 开发规范

- 控制器继承 `Backend`，仅做入参白名单提取与服务调用
- 服务层使用静态方法，首行 `validate(...)->scene(...)->check($data)`
- 新增/编辑统一调用 `Model::writeById(id, data)`；删除用 `deleteById(id)`
- 返回结构：`ajaxReturn(code, msg, data?, token?)`
- 错误码：200/400/401/403/404/409/422/500
