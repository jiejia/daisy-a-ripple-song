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

<h3 align="center">WordPress-тема для подкастов с Vite-сборкой ассетов</h3>

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

> Классическая WordPress-тема для блогов и подкастов с Vite, виджетной раскладкой и опциональной глубокой интеграцией с плагином A Ripple Song Podcast.

## ✨ Описание

A Ripple Song подходит для сайтов, где сочетаются статьи, авторские страницы и подкаст-эпизоды. Без плагина тема работает как обычная блоговая тема, а при включении companion-плагина добавляет карточки эпизодов, фиксированный плеер, интерфейс плейлиста и интеграцию с подкаст-архивами.

### Основные возможности

- Классические шаблоны для главной, архивов, одиночных записей, страниц, поиска, авторов, тегов и медиа
- Frontend на базе Vite с Tailwind CSS 4, DaisyUI 5, Alpine.js, переходами Swup, аудио через Howler и визуализацией AudioMotion
- Главная и сайдбары на основе виджетов:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - Podcast List и Subscribe Links при активном `a-ripple-song-podcast`
- Настройки темы через Carbon Fields:
  - Загрузка логотипа с целевым кадрированием `220x32`
  - Переключатели светлой и тёмной темы DaisyUI
  - Переопределение копирайта в подвале
  - Вставка пользовательских скриптов в `head` и перед `</body>`
  - Страница ссылок на соцсети
- Интеграция с подкаст-плагином:
  - Отдельное отображение `ars_episode`
  - Фиксированный аудиоплеер и drawer плейлиста
  - Счётчики просмотров и воспроизведений через WordPress AJAX
  - Архивы тегов могут включать и записи, и эпизоды
  - Архивы авторов могут учитывать участие в подкастах
- Улучшения редактора:
  - Стиль блока `Panel` для `core/group`
  - Паттерн блока `Intro Panel`
- Тема готова к локализации, языковые файлы лежат в `resources/lang`

### Примечания

- Тема ожидает наличие Composer-зависимостей. Если вы ставите её из исходников, выполните `composer install` в каталоге темы.
- Продакшн-ассеты загружаются из `public/dist`. Команды `npm install` и `npm run build` нужны только для пересборки фронтенда.
- Публикация подкаста и генерация RSS выполняются companion-плагином, а не самой темой.

## 🚀 Установка

1. Загрузите тему `a-ripple-song` в `/wp-content/themes/` или установите ZIP через админку WordPress.
2. Если вы используете исходники, выполните `composer install` в `wp-content/themes/a-ripple-song`.
3. Активируйте тему в админке.
4. Назначьте меню в область `Primary Navigation`.
5. Настройте зоны виджетов в `Внешний вид` → `Виджеты`:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. Откройте `A Ripple Song` → `General` и `Social Links`, чтобы настроить логотип, палитры, скрипты и соцсети.
7. Необязательно: активируйте `A Ripple Song Podcast`, чтобы получить виджеты эпизодов, интерфейс плеера и шаблоны для подкастов.
8. Необязательно для разработки: выполните `npm install`, затем `npm run dev` для Vite или `npm run build` для продакшн-сборки.

## ❓ Часто задаваемые вопросы

### Нужен ли мне подкаст-плагин?

Нет. Тема работает и как обычная блоговая тема. Плагин нужен только для эпизодов, фиксированного плеера, кнопок подписки и Podcast RSS.

### Почему в wp-admin появляется предупреждение о зависимостях?

Тема показывает уведомление, если отсутствуют Composer autoload-файлы. Выполните `composer install` в `wp-content/themes/a-ripple-song` и обновите wp-admin.

### Какие зоны виджетов управляют макетом?

`Home Main` отвечает за модули главной страницы, `Leftbar Primary` и `Rightbar Primary` управляют боковыми колонками, а `Footer Links` выводит сетку ссылок в подвале.

### Можно ли использовать Vite в разработке?

Да. Сначала выполните `npm install`, затем `npm run dev`. Если доступен Vite-сервер по адресу `http://127.0.0.1:5173`, тема автоматически подключит его ассеты; иначе будут использованы собранные файлы из `public/dist`.

## 🖼️ Скриншоты

1. Шапка темы с переключателем режима, поиском и адаптивной навигацией
2. Главная страница на основе виджетов с Banner Carousel, Blog List и блоками авторов / подкастов
3. Страницы настроек темы для логотипа, палитры, скриптов и соцсетей
4. Страница эпизода с фиксированным плеером и плейлистом при активном companion-плагине

## 📝 Журнал изменений

### 1.0.0

- Первый публичный релиз темы A Ripple Song.

## 🔔 Уведомление об обновлении

### 1.0.0

Первый публичный релиз.
