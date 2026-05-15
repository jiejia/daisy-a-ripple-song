# Daisy A Ripple Song WordPress.org 主题审查合规报告

审查日期：2026-05-15

审查范围：`wp-content/themes/daisy-a-ripple-song/`

参考文档：

- <https://make.wordpress.org/themes/handbook/review/required/>
- <https://make.wordpress.org/themes/handbook/review/how-to-do-a-review-draft/>

说明：本报告按你要求没有再次运行 Theme Check，只基于 WordPress.org 官方主题审查要求和当前源码做人工检查。Theme Check 通过只能说明自动规则没有发现明显问题，不能代表人工审核一定通过。

## 总结

当前主题如果把源码目录直接打包提交，明显不符合 WordPress.org 主题目录要求，主要原因是包含 `.git`、`.github`、`.idea`、`node_modules`、`vendor`、`build`、zip 包、`.DS_Store`、`.phpunit.cache` 等开发文件或隐藏文件。

即使只提交 `bin/build-dist.sh` 生成的发布包，仍有高风险不合规点：主题内包含/依赖 Carbon Fields 这类会引入插件性质能力的框架、存在可保存任意 header/footer script 的设置、主题前端含访问/播放统计调用、设置页放在顶级后台菜单、readme 资源授权清单不足，并且 README/readme 中推荐的配套插件不是 WordPress.org 插件目录来源。

建议先修复“必须修复”部分，再提交给官方人工审核。

## 必须修复

### 1. 提交包不能包含开发文件、隐藏文件、zip 文件

相关要求：官方要求移除 `.git`、隐藏文件/文件夹、zip 文件、shell 脚本等不允许的文件，并且提交包只能包含主题运行必需文件。

当前发现：

- 主题根目录存在：`.git`、`.github`、`.idea`、`.DS_Store`、`.phpunit.cache`
- 主题根目录存在：`node_modules/`、`vendor/`、`build/`
- `build/` 里存在多个 zip：`daisy-a-ripple-song-dev-scoper-test.zip`、`daisy-a-ripple-song-autoload-alias-fix.zip`、`daisy-a-ripple-song-carbon-hooks-fix.zip`
- 根目录还有开发构建文件：`package.json`、`package-lock.json`、`composer.json`、`composer.lock`、`vite.config.js`、`scoper.inc.php`、`bin/`

影响：

- 如果直接上传当前主题目录，会被文件要求拒绝。
- `node_modules/`、`build/`、隐藏文件和历史 zip 不能进入最终主题 zip。

修复建议：

- 永远只提交干净的发布目录/发布 zip，不要提交源码根目录。
- 发布包中排除：`.git`、`.github`、`.idea`、`.DS_Store`、`.phpunit.cache`、`node_modules`、`build`、所有 `.zip`、`.sh`、测试缓存和开发配置。
- 如果发布包仍需要 `vendor/`，只保留运行时必须文件，并确认其中所有文件、文本域、授权和功能都符合主题要求。
- 在发布前增加一个自动检查脚本，例如列出发布包内的隐藏文件、zip、shell、node_modules、测试目录和开发配置，发现即失败。

### 2. Carbon Fields 依赖和打包方式存在人工审核高风险

相关文件：

- `composer.json`
- `functions.php`
- `src/Providers/CarbonFieldsServiceProvider.php`
- `src/Providers/SettingServiceProvider.php`
- `src/Widgets/*`

当前发现：

- 主题通过 Composer 引入 `htmlburger/carbon-fields`。
- `functions.php` 从 `vendor/autoload.php` 或 `vendor/scoper-autoload.php` 加载依赖。
- 后台设置页和 widgets 都依赖 Carbon Fields。
- Carbon Fields 自身包含大量后台 UI、字段、容器等框架能力；如果完整进入主题包，审核员会按“主题内库也必须满足主题要求”审查它。
- 这类框架可能带来额外文本域、后台功能、区块注册能力或插件性质功能，容易触发“插件功能不能放主题里”的人工审核问题。

影响：

- 官方允许主题使用部分框架，但框架也必须符合主题要求。
- 如果框架中出现独立文本域、插件性质功能、区块注册或与主题展示无关的后台功能，主题可能被要求移除或迁移。
- 主题不能要求插件才能工作，也不能把插件复制进主题目录。

修复建议：

- 最稳妥方案：把 Carbon Fields 相关设置、字段、widget 表单迁移到 WordPress 原生 Customizer、Settings API、Widgets API 或 block editor 支持的主题展示设置。
- 如果继续使用 Carbon Fields：发布包只保留真正需要的运行时代码；移除不会被主题使用的模块；确认唯一或允许范围内的 text domain；确认没有自定义 block、CPT、shortcode、非展示相关 meta box 等插件领域功能。
- 对发布包做人工检索：`register_block_type`、`register_post_type`、`add_shortcode`、多 text domain、plugin header、外部下载/安装插件逻辑。
- 如果官方审核员质疑 Carbon Fields，优先改为原生 WordPress API，不建议和审核员争论这个点。

### 3. 主题设置允许任意 header/footer 脚本，不符合安全和插件领域要求

相关文件：

- `src/Settings/General.php`
- `src/Providers/SettingServiceProvider.php`

当前发现：

- `General::fields()` 中有 `header_scripts` 和 `footer_scripts` textarea。
- 帮助文本明确说明可填入完整 `<script>`，例如 Google Analytics。
- `outputHeaderScripts()` 和 `outputFooterScripts()` 直接 `echo` 保存的脚本内容。
- `SettingServiceProvider::register()` 把这两个输出挂到 `wp_head` 和 `wp_footer`。

影响：

- 官方把 analytics/tracking support 列为 plugin territory。
- 任意脚本注入属于安全高风险，人工审核通常会要求从主题移除。
- 即使只有有权限用户能保存，也不适合 WordPress.org 主题目录。

修复建议：

- 删除 `header_scripts`、`footer_scripts` 设置字段。
- 删除 `outputHeaderScripts()`、`outputFooterScripts()` 和对应 hooks。
- 如果用户需要 analytics、广告、站点验证、像素或自定义脚本，放到配套插件或推荐 WordPress.org 上的现有插件。
- 如只需要展示样式类配置，改成受限白名单字段，并在输出时严格 escape。

### 4. 前端访问/播放统计属于隐私和插件领域风险

相关文件：

- `resources/js/main.js`
- `resources/views/partials/entry-meta.php`
- `src/Providers/AssetServiceProvider.php`
- 配套插件 REST API

当前发现：

- `resources/js/main.js` 中定义并调用 `metrics/views`、`metrics/plays`、`metrics`。
- 页面会根据 DOM 上的 post ID 请求统计接口，并更新浏览/播放计数。
- `readme.txt` 的 Features 中包含 `Data tracking`。

影响：

- 官方要求任何用户数据跟踪和收集必须默认关闭，并且只能 opt-in。
- analytics/tracking support 属于 plugin territory。
- 目前主题前端含统计调用逻辑，即使 REST API 实际在插件里，也会让主题承担跟踪功能的一部分。

修复建议：

- 从主题前端 JS 中移除自动统计发送逻辑。
- 如果只展示插件提供的计数，主题可以保留纯展示 markup，但不要主动记录 view/play。
- 把 view/play 记录逻辑完整迁移到 WordPress.org 插件目录中的插件，并默认关闭，提供明确 opt-in。
- 在 `readme.txt` 增加隐私说明：收集什么、何时收集、存在哪里、如何关闭。

### 5. 推荐配套插件不能指向 GitHub 或第三方来源

相关文件：

- `README.md`
- `readme.txt`
- 主题内和文档中关于 `A Ripple Song` 插件的说明

当前发现：

- `README.md` 链接了 GitHub 上的配套播客插件。
- `readme.txt` 中也说明安装配套插件后会启用播客播放器和统计能力。

影响：

- WordPress.org 主题只允许推荐 WordPress.org 插件目录中的插件。
- 主题不能自动安装插件，不能要求插件才能正常工作。

修复建议：

- 如果需要推荐配套插件，先把插件提交到 WordPress.org 插件目录。
- readme 中只使用 WordPress.org 插件链接和插件 slug。
- 文案必须写成 “recommended/optional”，不能写成主题运行必需。
- 主题在未安装插件时必须保持完整可用，只是少一些增强功能。

### 6. 主题选项页不应放在后台顶级菜单

相关文件：

- `src/Menus/ThemeOptions.php`
- `src/Providers/SettingServiceProvider.php`

当前发现：

- `ThemeOptions::topMenu()` 使用 `add_menu_page()` 创建顶级 `Theme Options` 菜单。
- Carbon Fields 子设置页挂在这个顶级菜单下。

影响：

- 官方对主题设置页和 onboarding 有限制，主题设置通常应在 Appearance 下，或使用 Customizer / Site Editor 能力。
- 主题创建顶级后台菜单容易被认为打扰后台信息架构。

修复建议：

- 删除顶级 `add_menu_page()`。
- 把设置页改到 `Appearance > Daisy A Ripple Song`，使用 `add_theme_page()`。
- 或者迁移到 Customizer，并使用 `edit_theme_options` capability。
- 保留当前已使用的 `edit_theme_options` 权限，这是正确方向。

### 7. 主题设置可能使用多个 options 存储

相关文件：

- `src/Abstracts/AbstractSetting.php`
- `src/Settings/General.php`
- `src/Settings/SocialLinks.php`
- `src/Providers/SettingServiceProvider.php`

当前发现：

- Carbon Fields theme options 通常按字段保存为多个 option。
- 当前字段命名如 `ars_general_light_theme`、`ars_general_dark_theme`、`ars_general_footer_copyright`、社交链接字段等，看起来会形成多个独立 option。

影响：

- 官方要求主题设置最多使用一个 option，或使用 WordPress 原生 theme mods / Customizer 存储。
- 多 option 设置会被人工审核要求合并。

修复建议：

- 使用一个 option，例如 `daisy_a_ripple_song_options`，内部保存数组。
- 或迁移到 Customizer 的 `theme_mods`。
- 添加迁移逻辑：读取旧 Carbon Fields/options 值，写入新单一 option，然后保留兼容读取一段版本。
- 删除旧字段后，提供清理逻辑，但不要在主题激活时破坏用户数据。

### 8. readme 的 Resources 授权清单不足

相关文件：

- `readme.txt`
- `README.md`
- `package.json`
- `composer.json`
- `public/dist/*`
- `screenshot.png`

当前发现：

- `readme.txt` 的技术栈只列了 Vite、Tailwind CSS、daisyUI、Alpine.js、Lucide、Simple Icons、Swup、Howler.js、audioMotion、Carbon Fields、PHP-Scoper。
- 没有逐项列出每个第三方资源的版权、许可证和来源 URL。
- 没有看到 screenshot、字体、图标、图片、JS/CSS 库的完整资源授权清单。

影响：

- 官方要求主题 zip 内所有代码、数据、图片、字体等都必须 GPL 兼容。
- 必须在一个文件中列出所有资源的 license、copyright、source。

修复建议：

- 在 `readme.txt` 增加完整 `== Resources ==`。
- 每一项写清：资源名、作者/版权、许可证、来源 URL。
- 覆盖所有 bundled 资源：截图、图标库、JS 库、CSS 框架、PHP 库、字体、图片、音频、构建后仍在包内的任何第三方文件。
- 如果某资源许可证不是 GPL-compatible，替换资源。
- 如果使用 WordPress core 自带库，不要重复打包，应改用 core 注册脚本。

## 应尽快修复

### 9. Vite dev server 探测和 localhost 资源加载不应出现在发布主题行为中

相关文件：

- `src/Providers/AssetServiceProvider.php`
- `src/Providers/SettingServiceProvider.php`

当前发现：

- 主题默认探测 `http://127.0.0.1:5173/@vite/client`。
- 如果本机 dev server 可访问，会从 localhost enqueue 脚本和样式。
- 后台设置页也有同样逻辑。

影响：

- 官方要求未经用户明确同意不得从远程资源获取文件或数据。
- localhost 虽不是第三方 CDN，但发布主题在用户站点主动 HTTP 探测仍可能被审核员视为不必要的网络请求。

修复建议：

- 发布版本完全禁用 dev server 探测。
- 仅在 `WP_DEBUG && SCRIPT_DEBUG && defined('DAISY_A_RIPPLE_SONG_DEV')` 时启用。
- 或在打包脚本中替换/移除 dev server 分支，发布包只加载 `public/dist`。

### 10. Banner Carousel Widget 存储重复内容，接近不推荐的内容创建

相关文件：

- `src/Widgets/BannerCarouselWidget.php`
- `resources/views/widgets/banner-carousel.php`

当前发现：

- Widget 使用 Carbon Fields `complex` 字段保存多个 slide。
- 每个 slide 保存 image、link、target、description。

影响：

- 官方审查草案说明：主题只允许少量内容创建；大型视觉内容如 slideshow 应优先使用已有 posts/pages/media。
- 如果用户切换主题，保存在主题 widget 字段里的轮播内容不可复用。

修复建议：

- 改为展示已有 posts/pages/media/gallery，不在主题里创建一套 slide 内容模型。
- 或把轮播内容管理迁移到插件。
- 如果保留主题 widget，只保留展示配置，不保存重复内容。

### 11. 管理后台错误提示不可 dismiss，并使用了 manage_options

相关文件：

- `functions.php`

当前发现：

- 依赖缺失时输出 `<div class="notice notice-error">`。
- 没有 `is-dismissible` 或持久 dismissal。
- 权限判断使用 `manage_options`。

影响：

- 官方要求主题生成的 admin notices 必须可 dismiss。
- 主题选项权限应使用 `edit_theme_options`。

修复建议：

- 发布包应确保依赖完整，不需要展示“composer install”错误。
- 如果仍保留 notice，添加 `notice is-dismissible`，并实现 user meta 或 transient 记忆关闭状态。
- 权限改为 `current_user_can('edit_theme_options')`。

### 12. 部分输出需要加强 escaping

相关文件：

- `header.php`
- `resources/views/widgets/banner-carousel.php`

当前发现：

- `header.php` 的 `title="<?php bloginfo('description'); ?>"` 应改为 `esc_attr(get_bloginfo('description'))`。
- `header.php` 的站点名文本输出应改为 `esc_html(get_bloginfo('name'))`。
- `banner-carousel.php` 的 `rel` 属性目前直接输出三元表达式结果，建议统一 `esc_attr()`。
- `banner-carousel.php` 的 inline `<script>` 可以迁移到 enqueue 的 JS 文件，模板只输出 data attributes。

影响：

- 官方要求所有不可信输出都 escape。
- 当前几处多数来自 WordPress 或固定白名单，实际风险较低，但人工审核会偏向要求统一修复。

修复建议：

- 用 `esc_attr(get_bloginfo('description'))` 替换属性内 `bloginfo()`。
- 用 `esc_html(get_bloginfo('name'))` 替换文本内 `bloginfo()`。
- 用 `esc_attr($slide['link_target'] === '_blank' ? 'noopener noreferrer' : '')` 输出 rel。
- 把 carousel 初始化脚本移到主题主 JS 或单独 widget JS，并通过 `wp_add_inline_script()` 只传必要配置。

## 当前看起来已满足或风险较低的点

- `style.css` 包含必需头信息：Theme Name、Author、Description、Version、Requires at least、Tested up to、Requires PHP、License、License URI、Text Domain。
- Classic theme 基础 hooks 看起来存在：`wp_head()`、`wp_footer()`、`wp_body_open()`、`body_class()`、`post_class()`、`wp_link_pages()`、`title-tag` 支持。
- 主题有 skip link：`header.php` 中存在 `Skip to content` 链接。
- 主题文本域主域为 `daisy-a-ripple-song`，`style.css` 中声明了 `Domain Path: /resources/lang`。
- 目前没有在主题源码中发现自定义 post type、shortcode 或自定义 role 的直接注册。

## 建议修复顺序

1. 先决定是否继续使用 Carbon Fields。如果目标是通过 WordPress.org 官方主题目录，建议优先迁移到原生 WordPress API。
2. 移除 header/footer scripts 设置和前端 metrics 记录逻辑。
3. 把主题设置页迁移到 Appearance 或 Customizer，并把设置存储合并到一个 option 或 theme mods。
4. 清理 readme：补全 Resources、隐私说明、插件推荐来源和功能限制。
5. 固化发布流程，确保最终 zip 不包含开发文件、隐藏文件、zip、node_modules、build、未审计 vendor。
6. 修复 escaping、dismissible notice、inline script 等较小问题。

## 提交前人工检查清单

- 发布 zip 解压后没有隐藏文件、zip 文件、shell 脚本、node_modules、build、IDE 配置、测试缓存。
- `readme.txt` 有完整 `== Resources ==`，每个第三方资源都有 source/license/copyright。
- 没有 analytics/tracking/custom script injection 这类插件领域功能。
- 没有推荐非 WordPress.org 插件。
- 主题不需要插件也能正常显示内容。
- 主题设置只用一个 option 或 theme mods。
- 所有外部资源都已本地打包，且没有未经用户同意的 HTTP 请求。
- 所有用户可控输出都有 escape，所有入库数据都有 sanitize。
- Admin notice 可 dismiss，并使用 `edit_theme_options`。

