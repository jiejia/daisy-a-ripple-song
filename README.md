<p align="center">
  <a href="./README.md">English</a> •
  <a href="./docs/README.zh_CN.md">简体中文</a> •
  <a href="./docs/README.zh-Hant.md">繁體中文</a> •
  <a href="./docs/README.ja.md">日本語</a> •
  <a href="./docs/README.ko_KR.md">한국어</a> •
  <a href="./docs/README.fr_FR.md">Français</a> •
  <a href="./docs/README.es_ES.md">Español</a> •
  <a href="./docs/README.pt_BR.md">Português (Brasil)</a> •
  <a href="./docs/README.ru_RU.md">Русский</a> •
  <a href="./docs/README.hi_IN.md">हिन्दी</a> •
  <a href="./docs/README.bn_BD.md">বাংলা</a> •
  <a href="./docs/README.ar.md">العربية</a> •
  <a href="./docs/README.ur.md">اردو</a>
</p>

<p align="center">
  <img alt="A Ripple Song Theme" src="https://img.shields.io/badge/A%20Ripple%20Song%20Theme-1.0.0-2563eb?style=for-the-badge&logo=wordpress&logoColor=white" height="40">
</p>

<h3 align="center">Podcast-first WordPress theme with Vite-powered assets</h3>

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

> A classic WordPress theme for blogs and podcasts, with Vite assets, widget-driven layouts, and optional deep integration with the A Ripple Song Podcast plugin.

## ✨ Description

A Ripple Song is a classic WordPress theme built for content sites that mix articles, creators, and podcast episodes. It works as a regular blog theme on its own, and when the companion plugin is active it adds podcast-focused presentation such as episode cards, a sticky player, playlist UI, and archive integration.

### Key features

- Classic theme templates for home, archive, single, page, search, author, tag, and media views
- Vite-powered frontend pipeline with Tailwind CSS 4, DaisyUI 5, Alpine.js, Swup page transitions, Howler audio playback, and AudioMotion visualizer support
- Widget-driven homepage and sidebars:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - Podcast List and Subscribe Links when `a-ripple-song-podcast` is active
- Theme settings powered by Carbon Fields:
  - Site logo upload with `220x32` crop target
  - Light and dark DaisyUI theme pickers
  - Footer copyright override
  - Custom header and footer script injection
  - Social links page for common platforms
- Podcast companion integration:
  - Dedicated `ars_episode` single/archive presentation
  - Sticky audio player and playlist drawer
  - View/play metric tracking via WordPress AJAX
  - Tag archives can include both posts and episodes
  - Author archives can include podcast participation data
- WordPress editor enhancements:
  - Custom `Panel` block style for `core/group`
  - Built-in `Intro Panel` block pattern
- Translation-ready theme text domain with bundled language packs in `resources/lang`

### Notes

- The theme expects Composer dependencies to be present. If you install from source, run `composer install` inside the theme directory.
- Production assets are loaded from `public/dist`. Use `npm install` and `npm run build` only when you need to rebuild frontend assets.
- Podcast publishing and RSS feed generation are provided by the companion plugin, not by the theme itself.

## 🚀 Installation

1. Upload the `a-ripple-song` theme to `/wp-content/themes/` or install it as a ZIP from WP Admin.
2. If you are using the source checkout, run `composer install` in `wp-content/themes/a-ripple-song`.
3. Activate the theme in WP Admin.
4. Assign your menu to `Primary Navigation`.
5. Configure widget areas in `Appearance` → `Widgets`:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. Open `A Ripple Song` → `General` and `Social Links` to configure logo, palettes, scripts, and social profiles.
7. Optional: install and activate `A Ripple Song Podcast` to enable episode widgets, the player UI, and podcast-specific templates.
8. Optional for development: run `npm install` and `npm run dev` for Vite hot reload, or `npm run build` for a production rebuild.

## ❓ Frequently Asked Questions

### Do I need the podcast plugin?

No. The theme can run as a regular blog/theme without it. The plugin is only required for podcast episode content, the sticky player, subscribe widgets, and podcast RSS/feed workflows.

### Why do I see a dependency warning in wp-admin?

The theme shows an admin notice when Composer autoload files are missing. Run `composer install` in `wp-content/themes/a-ripple-song` and refresh wp-admin.

### Which widget areas control the layout?

`Home Main` powers the homepage modules, `Leftbar Primary` and `Rightbar Primary` control the side columns, and `Footer Links` renders the footer link grid.

### Can I use Vite during development?

Yes. Run `npm install` once, then `npm run dev`. When the Vite dev server at `http://127.0.0.1:5173` is available, the theme loads assets from it automatically; otherwise it falls back to built files in `public/dist`.

## 🖼️ Screenshots

1. Theme header with theme-mode toggle, search, and responsive navigation
2. Widget-driven homepage using Banner Carousel, Blog List, and author/podcast modules
3. Theme settings pages for logo, palette, scripts, and social links
4. Podcast episode page with sticky player and playlist drawer when the companion plugin is active

## 📝 Changelog

### 1.0.0

- Initial public release of the A Ripple Song theme.

## 🔔 Upgrade Notice

### 1.0.0

Initial public release.
