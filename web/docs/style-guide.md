# 小说后台管理系统 前端风格与工程规范

## 核心信息
- 项目/产品：小说后台管理系统
- 技术栈：Vue 3 + TypeScript + Vite
- 组件库：Element Plus
- 样式方案：CSS Variables + Scoped CSS；允许 SCSS Modules 或 Tailwind（按特性引入，不混用）
- 风格基调：现代科技 + 清新简约 + 品牌定制

---

## 设计视觉规范（Visual Style Guide）

### 色板
- Primary：`#3B82F6`（500），`#2563EB`（600），`#1D4ED8`（700）
- Secondary：`#64748B`（500），`#475569`（600），`#334155`（700）
- Success：`#22C55E`（500），`#16A34A`（600），`#15803D`（700）
- Warning：`#F59E0B`（500），`#D97706`（600），`#B45309`（700）
- Error：`#EF4444`（500），`#DC2626`（600），`#B91C1C`（700）
- 背景与文本：
  - 背景（浅）：`#F5F7FB`
  - 面板：`#FFFFFF`
  - 边框：`#E5E7EB`
  - 文本主色：`#0F172A`
  - 文本次色：`#64748B`
- 暗色模式（建议）：
  - 背景：`#0B1220`
  - 面板：`#0F172A`
  - 边框：`#1E293B`
  - 文本主色：`#E2E8F0`
  - 文本次色：`#94A3B8`
- 变量对齐（CSS Variables）：
  - `--nc-primary`、`--nc-primary-600`、`--nc-success`、`--nc-warning`、`--nc-error`
  - `--nc-bg`、`--nc-panel`、`--nc-border`、`--nc-text`、`--nc-muted`
  - 暗色模式以 `data-theme="dark"` 切换变量集合

### 排版（Typography）
- 字体：`Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Arial, 'Noto Sans', sans-serif`
- 标题层级：
  - H1：`font-weight: 700; font-size: 28px; line-height: 36px`
  - H2：`font-weight: 700; font-size: 24px; line-height: 32px`
  - H3：`font-weight: 600; font-size: 20px; line-height: 28px`
  - H4：`font-weight: 600; font-size: 18px; line-height: 26px`
  - H5：`font-weight: 600; font-size: 16px; line-height: 24px`
  - H6：`font-weight: 600; font-size: 14px; line-height: 22px`
- 正文：
  - Large：`16px / 24px`
  - Standard：`14px / 22px`
  - Small：`12px / 18px`

### 布局与间距（Layout & Spacing）
- 网格单位：`4px / 8px` 原子化间距
- 页面容器：
  - Header 高度：`--nc-header-height: 64px`
  - Sidebar 宽度：`--nc-sidebar-width: 208px`；折叠宽度：`64px`
  - 内容边距：`--nc-content-padding: 24px`
- 间距规则：
  - 组件内边距以 `8px` 为步进（如 8/12/16/24）
  - 组件间距以 `12px` 起步（如 12/16/24）
  - 列表行高与密度可在 `small/medium` 两档切换

### 通用组件视觉与交互
- Button（Primary/Default/Text）：
  - 默认：主色填充、圆角 10px、边框主色
  - Hover：主色 600；阴影 `0 0 0 3px rgba(59,130,246,0.15)`（可选）
  - Active：主色 700；压低阴影（或移除）
  - Disabled：降低不透明度、禁用指针事件
- Input/Select：
  - 默认：圆角 10px、边框 `--nc-border`
  - Focus：边框主色、光晕阴影 `0 0 0 3px rgba(59,130,246,0.15)`
- Card：
  - 圆角 12px、边框 `--nc-border`、阴影 `--nc-shadow`
- Modal/Popover：
  - 圆角 10px、统一阴影、适度内边距（24px）
- Menu（侧边栏）：
  - 展开：图标 + 文字，选中项使用内嵌左侧高亮线与淡底色
  - 折叠：仅图标，文字隐藏；悬停显示提示

---

## 代码与工程规范（Code & Engineering Standards）

### 命名约定
- 组件：PascalCase（如 `UserProfileCard`）
- Hooks/函数：camelCase（如 `useFetchData`、`handleFormSubmit`）
- 文件/目录：kebab-case（如 `user-profile-card.vue`、`menu-service.ts`）
- Type/Interface：PascalCase（如 `MenuNode`、`Role`）

### 目录结构（Feature-based 推荐）
```
web/src/
  api/          # 请求封装（每特性一个文件）
  components/   # 可复用 UI 组件
  layouts/      # 布局组件（AppLayout 等）
  pages/        # 业务页面（按特性分）
  router/       # 路由配置与常量
  store/        # Pinia store
  styles/       # 全局主题与变量
  utils/        # 通用工具方法
```
- 每组件独立样式文件（`<style scoped>` 或 SCSS Modules）
- `index.ts` 导出公共组件，便于按需引入
- 测试文件与组件同级（如 `UserDropdown.test.ts`）

### 组件开发最佳实践
- Vue 3 统一使用函数式心智与 Composition API；避免 Options API
- Props/Emit 必须使用 TypeScript 类型定义
- 单一职责：
  - 展示组件（无业务状态，仅 UI）
  - 容器组件（拉取数据、管理状态，组合展示组件）
- 状态管理：页面级使用 `ref/computed`，跨页面使用 Pinia
- 业务与视图分离：请求与数据整形在 `api/` 或 `service/` 层完成

### 样式规范
- 优先使用 CSS Variables（便于主题切换与全局一致）
- 允许 SCSS Modules（按特性引入，不与现有全局变量冲突）
- 允许 Tailwind（按页面引入，避免与 Element Plus 类名冲突；原子化场景、不要过度嵌套）
- 规则：
  - 最大嵌套层级 ≤ 3
  - 变量命名统一前缀 `--nc-*`
  - Mixin 仅用于复用高频样式（如阴影、圆角、间距）

### ESLint/Prettier（建议配置）
- ESLint：`vue/vue3-recommended`、`@typescript-eslint/recommended`
- Prettier：统一缩进 2、分号、单引号，行宽 100
- Husky + lint-staged：提交前自动 `eslint --fix` 与 `prettier --write`

---

## 质量与性能

### 可访问性（A11y）
- 所有交互元素提供语义标签与 ARIA 属性
- 图标需提供 `aria-label` 或隐藏并由文本替代
- 颜色对比度满足 WCAG AA（建议 ≥ 4.5:1）
- 键盘可访问（Tab 顺序、焦点状态可见）

### 性能优化
- 路由与组件懒加载（`() => import('...')`）
- 代码拆分：按页面与特性分包；公共库使用 `optimizeDeps`
- 避免不必要重渲染：精准依赖 `computed`，减少深层响应式包裹
- 图片与图标：
  - 小图标使用内联 SVG；品牌图标单独组件
  - 大图使用懒加载与合适的尺寸

---

## 组件状态与交互动画
- 动画基线：`150–250ms` 之间，使用 `ease-out` 或 `cubic-bezier`
- 悬停：颜色微调 + 轻微阴影或提亮
- 选中：明确高亮（主色 + 左侧高亮线）
- 焦点：清晰边界与光晕；符合 A11y
- 反馈：成功/失败使用状态色，保持一致的文案与位置

---

## 实施与落地
- 变量落地：统一在 `web/src/styles/theme.css` 定义与维护
- 主题切换：使用本系统的色卡选择器写入 `localStorage` 并动态更新 CSS Variables
- 路由风格：前端路由常量统一在 `web/src/router/routes.ts` 管理
- 折叠菜单：宽度固定 64px、隐藏文字，悬停显示提示，图标始终可见

---

## 版本与维护
- 每次 UI/主题改动需更新本规范并在 PR 内链接变更点
- 新增页面需在“组件开发最佳实践”与“A11y”两项进行自检

> 本规范用于指导高质量 UI 开发，确保一致性与可维护性。如需品牌化升级或暗色模式全面切换，可增补配套的变量集与局部覆盖方案。
