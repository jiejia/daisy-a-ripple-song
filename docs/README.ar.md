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

<h3 align="center">قالب ووردبريس موجه للبودكاست مع أصول مبنية عبر Vite</h3>

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

> قالب ووردبريس كلاسيكي للمدونات والبودكاست، مع خط بناء Vite وتخطيطات تعتمد على الودجات وتكامل اختياري عميق مع إضافة A Ripple Song Podcast.

## ✨ الوصف

تم تصميم A Ripple Song للمواقع التي تجمع بين المقالات وصفحات المبدعين وحلقات البودكاست. يمكن استخدامه بمفرده كقالب مدونة عادي، وعند تفعيل الإضافة المرافقة يضيف بطاقات الحلقات والمشغل الثابت وواجهة قائمة التشغيل وتكامل أرشيف البودكاست.

### الميزات الرئيسية

- قوالب كلاسيكية للصفحة الرئيسية والأرشيف والمنشور المفرد والصفحات والبحث والمؤلف والوسوم والوسائط
- واجهة أمامية مبنية على Vite مع Tailwind CSS 4 وDaisyUI 5 وAlpine.js وانتقالات Swup وتشغيل صوتي عبر Howler ودعم AudioMotion
- صفحة رئيسية وأشرطة جانبية معتمدة على الودجات:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - Podcast List وSubscribe Links عند تفعيل `a-ripple-song-podcast`
- إعدادات القالب عبر Carbon Fields:
  - رفع شعار الموقع مع قص مستهدف `220x32`
  - اختيار سمات DaisyUI الفاتحة والداكنة
  - تخصيص حقوق النشر في التذييل
  - إدراج سكربتات في الرأس والتذييل
  - صفحة روابط اجتماعية
- تكامل إضافة البودكاست:
  - عرض مخصص لمحتوى `ars_episode`
  - مشغل صوت ثابت ودرج قائمة تشغيل
  - تتبع المشاهدات والتشغيل عبر WordPress AJAX
  - أرشيف الوسوم يمكنه عرض المقالات والحلقات معاً
  - أرشيف الكاتب يمكنه إظهار بيانات المشاركة في البودكاست
- تحسينات للمحرر:
  - نمط بلوك `Panel` لبلوك `core/group`
  - نمط جاهز `Intro Panel`
- القالب جاهز للترجمة مع ملفات موجودة في `resources/lang`

### ملاحظات

- القالب يحتاج إلى تبعيات Composer. إذا كنت تثبته من المصدر فشغل `composer install` داخل مجلد القالب.
- أصول الإنتاج يتم تحميلها من `public/dist`. استخدم `npm install` و`npm run build` فقط إذا كنت تحتاج إلى إعادة بناء الواجهة الأمامية.
- نشر البودكاست وتوليد RSS يتمان عبر الإضافة المرافقة وليس عبر القالب نفسه.

## 🚀 التثبيت

1. ارفع قالب `a-ripple-song` إلى `/wp-content/themes/` أو ثبته كملف ZIP من لوحة التحكم.
2. إذا كنت تستخدم نسخة المصدر فشغل `composer install` داخل `wp-content/themes/a-ripple-song`.
3. فعّل القالب من لوحة تحكم ووردبريس.
4. اربط قائمتك مع `Primary Navigation`.
5. اضبط مناطق الودجات في `Appearance` → `Widgets`:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. افتح `A Ripple Song` → `General` و`Social Links` لإعداد الشعار والألوان والسكربتات والروابط الاجتماعية.
7. اختياري: فعّل `A Ripple Song Podcast` للحصول على ودجات الحلقات وواجهة المشغل وقوالب البودكاست.
8. اختياري للتطوير: شغّل `npm install` ثم `npm run dev` لوضع Vite التطويري أو `npm run build` لبناء الإنتاج.

## ❓ الأسئلة الشائعة

### هل أحتاج إلى إضافة البودكاست؟

لا. يمكن للقالب العمل كقالب مدونة عادي بدونها. تحتاج الإضافة فقط للحلقات والمشغل الثابت وأزرار الاشتراك وتدفق Podcast RSS.

### لماذا أرى تحذيراً عن التبعيات في wp-admin؟

يعرض القالب إشعاراً عندما تكون ملفات Composer autoload مفقودة. شغّل `composer install` داخل `wp-content/themes/a-ripple-song` ثم حدّث لوحة التحكم.

### ما هي مناطق الودجات التي تتحكم في التخطيط؟

`Home Main` يتحكم في وحدات الصفحة الرئيسية، و`Leftbar Primary` و`Rightbar Primary` يتحكمان في الأعمدة الجانبية، و`Footer Links` يعرض شبكة روابط التذييل.

### هل يمكنني استخدام Vite أثناء التطوير؟

نعم. شغّل `npm install` أولاً ثم `npm run dev`. إذا كان خادم Vite على `http://127.0.0.1:5173` متاحاً فسيحمّل القالب الأصول منه تلقائياً، وإلا فسيعود إلى الملفات المبنية في `public/dist`.

## 🖼️ لقطات الشاشة

1. رأس القالب مع تبديل النمط والبحث والتنقل المتجاوب
2. صفحة رئيسية معتمدة على الودجات باستخدام Banner Carousel وBlog List ووحدات المؤلف / البودكاست
3. صفحات إعداد القالب للشعار والألوان والسكربتات والروابط الاجتماعية
4. صفحة حلقة بودكاست مع مشغل ثابت ودرج قائمة تشغيل عند تفعيل الإضافة المرافقة

## 📝 سجل التغييرات

### 1.0.0

- أول إصدار عام لقالب A Ripple Song.

## 🔔 ملاحظة الترقية

### 1.0.0

أول إصدار عام.
