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

<h3 align="center">Vite asset pipeline সহ podcast-first WordPress theme</h3>

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

> ব্লগ ও podcast সাইটের জন্য classic WordPress theme, যেখানে Vite frontend pipeline, widget-driven layout, এবং A Ripple Song Podcast plugin-এর সাথে optional integration আছে।

## ✨ বর্ণনা

A Ripple Song এমন সাইটের জন্য তৈরি যেখানে article, creator profile, এবং podcast episode একসাথে প্রকাশ করা হয়। এটি একাই সাধারণ blog theme হিসেবে কাজ করতে পারে, আর companion plugin সক্রিয় থাকলে episode card, sticky player, playlist UI, এবং podcast archive integration যোগ হয়।

### প্রধান ফিচার

- Home, archive, single, page, search, author, tag, এবং media view-এর জন্য classic template
- Vite ভিত্তিক frontend stack, যেখানে Tailwind CSS 4, DaisyUI 5, Alpine.js, Swup transitions, Howler audio playback, এবং AudioMotion visualizer আছে
- Widget-driven homepage এবং sidebar:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - `a-ripple-song-podcast` সক্রিয় থাকলে Podcast List এবং Subscribe Links
- Carbon Fields ভিত্তিক theme settings:
  - `220x32` crop target সহ site logo upload
  - Light / dark DaisyUI theme picker
  - Footer copyright override
  - Header / footer script injection
  - Social links page
- Podcast plugin integration:
  - `ars_episode` এর জন্য dedicated single / archive presentation
  - Sticky audio player এবং playlist drawer
  - WordPress AJAX এর মাধ্যমে view / play metrics
  - Tag archive-এ post এবং episode একসাথে দেখানো
  - Author archive-এ podcast participation data যোগ করা
- Editor enhancements:
  - `core/group` এর জন্য `Panel` block style
  - `Intro Panel` block pattern
- `resources/lang`-এ bundled language packs

### নোট

- Theme চালাতে Composer dependency দরকার। source checkout থেকে install করলে theme directory-তে `composer install` চালান।
- Production assets `public/dist` থেকে load হয়। frontend rebuild করার দরকার হলে তবেই `npm install` এবং `npm run build` চালান।
- Podcast publishing এবং RSS generation theme-এর নয়, companion plugin-এর কাজ।

## 🚀 ইনস্টলেশন

1. `a-ripple-song` theme `/wp-content/themes/`-এ upload করুন অথবা WP Admin থেকে ZIP install করুন।
2. source version ব্যবহার করলে `wp-content/themes/a-ripple-song`-এ `composer install` চালান।
3. WP Admin-এ theme activate করুন।
4. আপনার menu `Primary Navigation`-এ assign করুন।
5. `Appearance` → `Widgets`-এ এই widget area-গুলো configure করুন:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. `A Ripple Song` → `General` এবং `Social Links`-এ logo, palette, scripts, এবং social profile সেট করুন।
7. Optional: `A Ripple Song Podcast` plugin activate করলে episode widget, player UI, এবং podcast-specific template পাওয়া যাবে।
8. Optional development step: `npm install` চালিয়ে `npm run dev` দিয়ে Vite dev mode বা `npm run build` দিয়ে production build করুন।

## ❓ সাধারণ প্রশ্ন

### Podcast plugin কি লাগবেই?

না। Theme plugin ছাড়া সাধারণ blog theme হিসেবেও কাজ করবে। episode, sticky player, subscribe button, এবং podcast RSS workflow দরকার হলে তবেই plugin লাগবে।

### wp-admin-এ dependency warning কেন দেখাচ্ছে?

Composer autoload file না থাকলে theme admin notice দেখায়। `wp-content/themes/a-ripple-song`-এ `composer install` চালিয়ে wp-admin refresh করুন।

### কোন widget area layout নিয়ন্ত্রণ করে?

`Home Main` homepage module নিয়ন্ত্রণ করে, `Leftbar Primary` এবং `Rightbar Primary` দুই পাশের column নিয়ন্ত্রণ করে, আর `Footer Links` footer link grid render করে।

### Development-এ Vite ব্যবহার করা যাবে?

হ্যাঁ। আগে `npm install` চালান, তারপর `npm run dev`। `http://127.0.0.1:5173`-এ Vite server পাওয়া গেলে theme সেখান থেকে asset load করবে, না হলে `public/dist`-এর built file ব্যবহার করবে।

## 🖼️ Screenshots

1. Theme header যেখানে mode toggle, search, এবং responsive navigation আছে
2. Widget-driven homepage যেখানে Banner Carousel, Blog List, এবং author / podcast module আছে
3. Logo, palette, scripts, এবং social links-এর জন্য theme settings page
4. Companion plugin active থাকলে sticky player এবং playlist drawer সহ podcast episode page

## 📝 পরিবর্তন

### 1.0.0

- A Ripple Song theme-এর প্রথম public release।

## 🔔 Upgrade Notice

### 1.0.0

প্রথম public release।
