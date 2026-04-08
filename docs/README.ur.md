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

<h3 align="center">Vite assets کے ساتھ podcast-first WordPress تھیم</h3>

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

> بلاگز اور podcast سائٹس کے لئے classic WordPress تھیم، جس میں Vite frontend pipeline، widget-driven layout، اور A Ripple Song Podcast plugin کے ساتھ optional integration شامل ہے۔

## ✨ تعارف

A Ripple Song ایسی سائٹس کے لئے بنایا گیا ہے جہاں articles، creators، اور podcast episodes ایک ساتھ ہوتے ہیں۔ یہ اکیلا بھی عام blog theme کے طور پر کام کرتا ہے، اور companion plugin فعال ہونے پر episode cards، sticky player، playlist UI، اور podcast archive integration فراہم کرتا ہے۔

### اہم خصوصیات

- Home، archive، single، page، search، author، tag، اور media views کے لئے classic templates
- Vite پر مبنی frontend setup جس میں Tailwind CSS 4، DaisyUI 5، Alpine.js، Swup transitions، Howler audio playback، اور AudioMotion visualizer شامل ہیں
- Widget-driven homepage اور sidebars:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - `a-ripple-song-podcast` فعال ہونے پر Podcast List اور Subscribe Links
- Carbon Fields پر مبنی theme settings:
  - `220x32` crop target کے ساتھ site logo upload
  - Light / dark DaisyUI theme picker
  - Footer copyright override
  - Header / footer script injection
  - Social links page
- Podcast plugin integration:
  - `ars_episode` کے لئے dedicated single / archive presentation
  - Sticky audio player اور playlist drawer
  - WordPress AJAX کے ذریعے view / play metrics
  - Tag archives میں posts اور episodes دونوں شامل ہو سکتے ہیں
  - Author archives میں podcast participation data شامل ہو سکتا ہے
- Editor enhancements:
  - `core/group` کے لئے `Panel` block style
  - `Intro Panel` block pattern
- `resources/lang` میں bundled language packs

### نوٹس

- Theme کو Composer dependencies درکار ہیں۔ اگر آپ source checkout سے install کر رہے ہیں تو theme directory میں `composer install` چلائیں۔
- Production assets `public/dist` سے load ہوتے ہیں۔ `npm install` اور `npm run build` صرف اس وقت چلائیں جب frontend assets دوبارہ build کرنے ہوں۔
- Podcast publishing اور RSS generation theme کی نہیں بلکہ companion plugin کی ذمہ داری ہے۔

## 🚀 تنصیب

1. `a-ripple-song` theme کو `/wp-content/themes/` میں upload کریں یا WP Admin سے ZIP install کریں۔
2. اگر آپ source version استعمال کر رہے ہیں تو `wp-content/themes/a-ripple-song` میں `composer install` چلائیں۔
3. WP Admin میں theme activate کریں۔
4. اپنا menu `Primary Navigation` کے ساتھ assign کریں۔
5. `Appearance` → `Widgets` میں یہ widget areas configure کریں:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. `A Ripple Song` → `General` اور `Social Links` میں logo، palettes، scripts، اور social profiles configure کریں۔
7. Optional: `A Ripple Song Podcast` plugin activate کریں تاکہ episode widgets، player UI، اور podcast-specific templates مل سکیں۔
8. Optional development step: `npm install` کے بعد `npm run dev` سے Vite dev mode یا `npm run build` سے production build کریں۔

## ❓ اکثر پوچھے گئے سوالات

### کیا مجھے podcast plugin درکار ہے؟

نہیں۔ Theme plugin کے بغیر بھی عام blog theme کے طور پر کام کرتا ہے۔ Plugin صرف episodes، sticky player، subscribe buttons، اور podcast RSS workflow کے لئے ضروری ہے۔

### wp-admin میں dependency warning کیوں نظر آ رہی ہے؟

جب Composer autoload files موجود نہ ہوں تو theme admin notice دکھاتا ہے۔ `wp-content/themes/a-ripple-song` میں `composer install` چلائیں اور wp-admin refresh کریں۔

### کون سے widget areas layout کو control کرتے ہیں؟

`Home Main` homepage modules کو control کرتا ہے، `Leftbar Primary` اور `Rightbar Primary` side columns کو control کرتے ہیں، اور `Footer Links` footer link grid render کرتا ہے۔

### کیا development میں Vite استعمال کیا جا سکتا ہے؟

جی ہاں۔ پہلے `npm install` چلائیں، پھر `npm run dev`۔ اگر `http://127.0.0.1:5173` پر Vite server available ہو تو theme وہیں سے assets load کرے گا، ورنہ `public/dist` کے built files استعمال کرے گا۔

## 🖼️ Screenshots

1. Theme header جس میں mode toggle، search، اور responsive navigation شامل ہے
2. Widget-driven homepage جس میں Banner Carousel، Blog List، اور author / podcast modules شامل ہیں
3. Logo، palette، scripts، اور social links کے لئے theme settings pages
4. Companion plugin فعال ہونے پر sticky player اور playlist drawer والا podcast episode page

## 📝 تبدیلیاں

### 1.0.0

- A Ripple Song theme کا پہلا public release۔

## 🔔 Upgrade Notice

### 1.0.0

پہلا public release۔
