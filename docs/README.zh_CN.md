<p align="center">
  <a href="../README.md">English</a> •
  <a href="README.zh_CN.md">简体中文</a> •
  <a href="README.zh-Hant.md">繁體中文</a> •
  <a href="README.ja.md">日本語</a> •
  <a href="README.ko_KR.md">한국어</a> •
  <a href="README.fr_FR.md">Français</a> •
  <a href="README.es_ES.md">Español</a> •
  <a href="README.pt_BR.md">Português (Brasil)</a> •
  <a href="README.ru_RU.md">Русский</a> •
  <a href="README.hi_IN.md">हिन्दी</a> •
  <a href="README.bn_BD.md">বাংলা</a> •
  <a href="README.ar.md">العربية</a> •
  <a href="README.ur.md">اردو</a>
</p>

<p align="center">
  <img alt="A Ripple Song Theme" src="https://img.shields.io/badge/A%20Ripple%20Song%20Theme-1.0.0-2563eb?style=for-the-badge&logo=wordpress&logoColor=white" height="40">
</p>

<h3 align="center">使用 Vite 资产管线的播客优先 WordPress 主题</h3>

<p align="center">
  <a href="https://github.com/jiejia/a-ripple-song">⭐ GitHub</a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white">
  <img alt="WordPress" src="https://img.shields.io/badge/WordPress-6.0+-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="Tested" src="https://img.shields.io/badge/Tested%20up%20to-6.9-21759B?style=flat-square&logo=wordpress&logoColor=white">
  <img alt="License" src="https://img.shields.io/badge/License-GPL--3.0-blue?style=flat-square">
</p>

---

# A Ripple Song

> 一个面向博客与播客内容站点的经典 WordPress 主题，带有 Vite 前端资源管线、可组合的小工具布局，以及对 A Ripple Song Podcast 插件的可选深度联动。

## ✨ 说明

A Ripple Song 是一个经典 WordPress 主题，适合同时发布文章、作者内容与播客节目的站点。它单独启用时可以作为常规博客主题使用；启用配套插件后，则会增加单集卡片、底部播放器、播放列表抽屉和播客归档联动等能力。

### 主要功能

- 经典主题模板：首页、归档、单篇、页面、搜索、作者、标签、媒体等视图
- 基于 Vite 的前端方案，集成 Tailwind CSS 4、DaisyUI 5、Alpine.js、Swup 页面切换、Howler 音频播放和 AudioMotion 可视化
- 以小工具驱动的首页和侧边栏布局：
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - 启用 `a-ripple-song-podcast` 后可用的 Podcast List 与 Subscribe Links
- 基于 Carbon Fields 的主题设置：
  - 站点 Logo 上传，裁剪目标为 `220x32`
  - 明亮 / 深色 DaisyUI 主题选择器
  - 页脚版权自定义
  - 页头 / 页尾脚本注入
  - 常用社交平台链接配置
- 与播客配套插件联动：
  - `ars_episode` 的单篇与归档展示
  - 固定音频播放器与播放列表抽屉
  - 基于 WordPress AJAX 的浏览 / 播放统计
  - 标签页可同时包含文章与单集
  - 作者页可包含播客参与内容
- 编辑器增强：
  - `core/group` 的自定义 `Panel` 区块样式
  - 内置 `Intro Panel` 区块样板
- 主题文本域支持多语言，语言包位于 `resources/lang`

### 注意事项

- 主题依赖 Composer 依赖文件。如果你使用源码安装，请在主题目录中执行 `composer install`。
- 生产环境资源从 `public/dist` 加载。只有在需要重建前端资源时才需要执行 `npm install` 和 `npm run build`。
- 播客发布与 RSS Feed 生成功能由配套插件提供，不属于主题本身。

## 🚀 安装

1. 将 `a-ripple-song` 主题上传到 `/wp-content/themes/`，或在后台通过 ZIP 安装。
2. 如果使用源码版本，请在 `wp-content/themes/a-ripple-song` 中执行 `composer install`。
3. 在后台启用主题。
4. 将菜单分配到 `Primary Navigation`。
5. 在 `外观` → `小工具` 中配置这些区域：
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. 打开 `A Ripple Song` → `General` 与 `Social Links`，配置 Logo、配色、脚本和社交链接。
7. 可选：安装并启用 `A Ripple Song Podcast` 插件，以启用单集小工具、播放器 UI 和播客模板。
8. 可选开发步骤：执行 `npm install`，然后使用 `npm run dev` 开启 Vite 开发模式，或使用 `npm run build` 构建生产资源。

## ❓ 常见问题

### 必须安装播客插件吗？

不是。主题在没有插件时也可以作为普通博客主题运行。只有当你需要播客单集、底部播放器、订阅按钮和播客 RSS 工作流时，才需要该插件。

### 为什么后台会提示依赖缺失？

当 Composer 自动加载文件不存在时，主题会显示后台提示。请在 `wp-content/themes/a-ripple-song` 中执行 `composer install` 后刷新后台。

### 哪些小工具区域决定页面布局？

`Home Main` 用于首页模块，`Leftbar Primary` 与 `Rightbar Primary` 用于左右栏，`Footer Links` 用于页脚链接网格。

### 开发时可以使用 Vite 吗？

可以。先执行 `npm install`，然后运行 `npm run dev`。当 `http://127.0.0.1:5173` 的 Vite 服务可用时，主题会自动加载开发资源；否则回退到 `public/dist` 中的构建文件。

## 🖼️ 截图

1. 带主题模式切换、搜索和响应式导航的页头
2. 使用 Banner Carousel、Blog List、作者 / 播客模块构建的首页
3. 用于 Logo、配色、脚本和社交链接的主题设置页
4. 启用配套插件后带底部播放器和播放列表抽屉的播客单集页

## 📝 更新日志

### 1.0.0

- A Ripple Song 主题首次公开发布。

## 🔔 升级提示

### 1.0.0

首次公开发布。
