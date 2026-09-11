# NovelCore — 多站点小说内容管理平台

一个面向文学阅读场景的多站点 SaaS 平台，包含后台管理系统、C 端用户 API、以及管理控制台前端。支持小说/章节/评论/工单/VIP/订阅等完整业务链路，内置 RBAC 权限体系、JWT 鉴权、Redis 缓存、ES 全文搜索、异步队列跨站点内容同步。

## 技术栈

| 模块 | 技术 | 说明 |
|------|------|------|
| backend | PHP 8.0+ / ThinkPHP 8 / think-multi-app | 后台管理 API（RBAC / JWT / 站点管理 / 内容管理） |
| novel-api | PHP 8.0+ / ThinkPHP 8 | C 端用户 API（阅读 / 评论 / 收藏 / 订单 / VIP） |
| web | Vue 3 + TypeScript + Element Plus + Pinia | 管理控制台前端 |
| 基础设施 | MySQL 8 / Redis 7 / Elasticsearch 8 | 存储、缓存、全文搜索 |
| 部署 | Docker Compose | 一键拉起 MySQL + Redis |

## 目录结构

```
NovelCore/
├── backend/                 # 后台管理服务
│   ├── app/
│   │   ├── admin/           # 管理模块（controller / service / validate / middleware / route）
│   │   ├── api/             # 公共接口（站点注册 / 同步接收）
│   │   ├── common/          # 公共模型、服务、中间件
│   │   └── command/         # 控制台命令（同步推送、ES 索引）
│   ├── config/              # 应用配置（database / cache / queue / sync / middleware）
│   ├── extend/              # 扩展类（ElasticService / GeoIP2 / StorageClient）
│   ├── public/              # Web 入口
│   └── .example.env         # 环境变量模板
│
├── novel-api/               # 用户端 API 服务
│   ├── app/
│   │   ├── controller/      # C 端控制器（小说 / 章节 / 评论 / 用户 / 订单 / VIP ...）
│   │   ├── admin/controller/# 后台只读接口
│   │   ├── service/         # 业务服务（JWT / 搜索 / 存储 / 统计 / 同步）
│   │   ├── model/           # ORM 模型（BaseModel / CacheModel）
│   │   ├── middleware/      # 中间件（CORS / Auth / Throttle / 日志）
│   │   └── lang/            # 多语言包（zh-cn / en-us / ja-jp / zh-tw / th-th）
│   ├── config/              # 应用配置（jwt / redis / database / sync / site ...）
│   ├── route/               # 路由定义
│   └── .example.env
│
├── web/                     # 管理控制台前端
│   ├── src/
│   │   ├── api/             # 接口封装（基于 axios）
│   │   ├── components/      # 业务组件（小说 / 章节 / 分类 / 标签 / 工单 / 反馈 ...）
│   │   ├── composables/     # 组合式函数（列表 / 表单 / 权限 / 主题）
│   │   ├── layouts/         # 页面布局
│   │   ├── pages/           # 页面视图（Dashboard / 系统管理 / 内容管理 / 财务 / 工单）
│   │   ├── router/          # 路由
│   │   ├── store/           # Pinia 状态
│   │   └── i18n/            # 多语言
│   └── .env.development / .env.production
│
└── docker/
    └── docker-compose.yml   # MySQL 8 + Redis 7 一键部署
```

## 核心特性

- **多站点架构**：平台管理多个独立小说站点，每个站点可独立配置域名、存储、AI、搜索等
- **RBAC 权限体系**：角色 → 权限（resource/action/field），菜单动态下发，接口级鉴权
- **JWT 双端鉴权**：后台管理使用 `lcobucci/jwt`，C 端 API 使用 `firebase/php-jwt`
- **缓存优先 + 异步持久化**：`CacheModel` 先写 Redis 再通过自定义队列异步落库，读写性能大幅提升
- **跨站点内容同步**：novel-api 写入后自动入队，由 backend 消费并同步到在线站点
- **Elasticsearch 全文搜索**：支持 MySQL / ES 双后端，工厂模式切换
- **工单 / 反馈 / 评论审核**：完整的用户互动管理闭环
- **VIP / 订阅 / 订单**：付费内容与收益结算链路
- **多语言**：C 端 API 支持 5 种语言（中/英/日/繁/泰）
- **安全防护**：CORS、频率限制、Bot 拦截、服务端白名单

## 快速开始

### 1. 启动基础设施

```bash
cd docker
docker compose up -d
```

### 2. 启动后台管理服务

```bash
cd backend
cp .example.env .env   # 填入数据库、Redis、JWT 等配置
composer install
php think run          # 默认 http://127.0.0.1:8000
```

### 3. 启动用户端 API

```bash
cd novel-api
cp .example.env .env
composer install
php -S 127.0.0.1:8080 -t public public/router.php
```

### 4. 启动管理控制台前端

```bash
cd web
cp .env.development .env
npm install
npm run dev            # 默认 http://localhost:5173
```

## 环境配置

所有敏感配置均通过 `.env` 文件注入，示例模板见各子目录的 `.example.env`。配置采用分组键格式：

```ini
[DATABASE]
HOSTNAME = localhost
DATABASE = novelcore
USERNAME = novelcore
PASSWORD = your_db_password_here

[JWT]
SECRET = changeme
TTL = 3600
```

**请务必替换 `.example.env` 中的占位符为实际值，并确保 `.env` 文件已加入 `.gitignore`。**

## 开发规范

- **后端**：PSR-12，ThinkPHP 8 约定，控制器轻 / 服务重 / 模型薄；服务层统一静态方法；校验器场景化
- **前端**：Vue 3 `<script setup>`，TypeScript 严格模式；组件 / 页面 / composables 分层；API 调用统一经 `src/api` 封装
- **提交**：Conventional Commits（`feat:` / `fix:` / `refactor:` / `chore:` / `docs:`）

## 许可证

本项目仅供学习与开发用途。
