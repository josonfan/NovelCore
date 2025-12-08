import { createI18n } from 'vue-i18n'

const messages = {
  'zh-CN': {
    common: {
      refresh: '刷新',
      save: '保存',
      logout: '退出',
      search_placeholder: '按名称/编码搜索',
      system_name: '管理后台',
    },
    dashboard: {
      welcome: '欢迎使用 NovelCore 后台',
      hint: '请从左侧菜单开始使用。',
    },
    menu: {
      manage: '菜单管理',
      system: '系统管理',
      home: '首页',
    },
    login: {
      username: '用户名',
      password: '密码',
      submit: '登录',
    },
  },
}

export const i18n = createI18n({
  legacy: false,
  locale: 'zh-CN',
  fallbackLocale: 'zh-CN',
  messages,
})
