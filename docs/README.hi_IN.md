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

<h3 align="center">Vite assets वाला podcast-first WordPress theme</h3>

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

> ब्लॉग और podcast साइटों के लिए classic WordPress theme, जिसमें Vite frontend pipeline, widget-driven layout, और A Ripple Song Podcast plugin के साथ optional integration है।

## ✨ विवरण

A Ripple Song उन साइटों के लिए बनाया गया है जहां articles, creators, और podcast episodes साथ चलते हैं। यह अपने आप में एक सामान्य blog theme की तरह काम करता है, और companion plugin active होने पर episode cards, sticky player, playlist UI, और podcast archive integration जोड़ता है।

### मुख्य फीचर्स

- Home, archive, single, page, search, author, tag, और media views के लिए classic templates
- Vite आधारित frontend setup जिसमें Tailwind CSS 4, DaisyUI 5, Alpine.js, Swup transitions, Howler audio playback, और AudioMotion visualizer शामिल हैं
- Widget-driven homepage और sidebars:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - `a-ripple-song-podcast` active होने पर Podcast List और Subscribe Links
- Carbon Fields पर आधारित theme settings:
  - `220x32` crop target वाला site logo upload
  - Light / dark DaisyUI theme picker
  - Footer copyright override
  - Header / footer script injection
  - Social links page
- Podcast plugin integration:
  - `ars_episode` के लिए dedicated single / archive presentation
  - Sticky audio player और playlist drawer
  - WordPress AJAX के जरिए view / play metrics
  - Tag archives में posts और episodes दोनों शामिल हो सकते हैं
  - Author archives में podcast participation data शामिल हो सकता है
- Editor enhancements:
  - `core/group` के लिए `Panel` block style
  - `Intro Panel` block pattern
- `resources/lang` में bundled language packs

### नोट्स

- Theme को Composer dependencies की जरूरत है। यदि आप source checkout से install कर रहे हैं, तो theme directory में `composer install` चलाएं।
- Production assets `public/dist` से load होते हैं। `npm install` और `npm run build` तभी चलाएं जब आपको frontend assets rebuild करने हों।
- Podcast publishing और RSS generation theme की नहीं, companion plugin की responsibility है।

## 🚀 इंस्टॉलेशन

1. `a-ripple-song` theme को `/wp-content/themes/` में upload करें या WP Admin से ZIP install करें।
2. यदि आप source version उपयोग कर रहे हैं, तो `wp-content/themes/a-ripple-song` में `composer install` चलाएं।
3. WP Admin में theme activate करें।
4. अपना menu `Primary Navigation` को assign करें।
5. `Appearance` → `Widgets` में इन widget areas को configure करें:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. `A Ripple Song` → `General` और `Social Links` में logo, palettes, scripts, और social profiles सेट करें।
7. Optional: `A Ripple Song Podcast` plugin activate करें ताकि episode widgets, player UI, और podcast-specific templates मिलें।
8. Optional development step: `npm install` चलाकर `npm run dev` से Vite dev mode या `npm run build` से production build करें।

## ❓ अक्सर पूछे जाने वाले सवाल

### क्या मुझे podcast plugin चाहिए?

नहीं। Theme बिना plugin के भी एक सामान्य blog theme की तरह काम करता है। Plugin सिर्फ episodes, sticky player, subscribe buttons, और podcast RSS workflow के लिए जरूरी है।

### wp-admin में dependency warning क्यों दिख रही है?

जब Composer autoload files नहीं मिलतीं, theme admin notice दिखाता है। `wp-content/themes/a-ripple-song` में `composer install` चलाकर wp-admin refresh करें।

### कौन से widget areas layout को control करते हैं?

`Home Main` homepage modules के लिए है, `Leftbar Primary` और `Rightbar Primary` side columns के लिए हैं, और `Footer Links` footer link grid को render करता है।

### क्या development में Vite use कर सकता हूँ?

हाँ। पहले `npm install` चलाएं, फिर `npm run dev`। अगर `http://127.0.0.1:5173` पर Vite server available है, तो theme वही assets load करेगा; नहीं तो `public/dist` के built files पर fallback करेगा।

## 🖼️ Screenshots

1. Theme header जिसमें mode toggle, search, और responsive navigation है
2. Widget-driven homepage जिसमें Banner Carousel, Blog List, और author / podcast modules हैं
3. Logo, palette, scripts, और social links के लिए theme settings pages
4. Companion plugin active होने पर sticky player और playlist drawer वाला podcast episode page

## 📝 बदलाव

### 1.0.0

- A Ripple Song theme का पहला public release.

## 🔔 Upgrade Notice

### 1.0.0

पहला public release.
