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

<h3 align="center">使用 Vite 資產流程的播客優先 WordPress 佈景主題</h3>

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

> 一個面向部落格與播客網站的經典 WordPress 佈景主題，具備 Vite 前端資產、可組合的小工具版面，以及對 A Ripple Song Podcast 外掛的可選深度整合。

## ✨ 說明

A Ripple Song 是一個適合文章、作者內容與播客節目並存的 WordPress 經典主題。單獨使用時可作為一般部落格主題；啟用配套外掛後，則可加入單集卡片、固定播放器、播放清單抽屜與播客歸檔整合等能力。

### 主要功能

- 經典主題範本：首頁、封存、單篇、頁面、搜尋、作者、標籤、媒體等檢視
- 以 Vite 為核心的前端架構，整合 Tailwind CSS 4、DaisyUI 5、Alpine.js、Swup 頁面切換、Howler 音訊播放與 AudioMotion 視覺化
- 由小工具驅動的首頁與側邊欄版面：
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - 啟用 `a-ripple-song-podcast` 後可使用 Podcast List 與 Subscribe Links
- 由 Carbon Fields 提供的主題設定：
  - 站點 Logo 上傳，裁切目標為 `220x32`
  - 明亮 / 深色 DaisyUI 主題選擇器
  - 頁尾版權文字覆寫
  - 頁首 / 頁尾自訂腳本注入
  - 常用社群平台連結設定
- 與播客配套外掛整合：
  - `ars_episode` 單篇與封存樣式
  - 固定音訊播放器與播放清單抽屜
  - 透過 WordPress AJAX 的瀏覽 / 播放統計
  - 標籤頁可同時包含文章與單集
  - 作者頁可包含播客參與內容
- 編輯器增強：
  - `core/group` 的自訂 `Panel` 區塊樣式
  - 內建 `Intro Panel` 區塊樣板
- 主題文字可翻譯，語言檔位於 `resources/lang`

### 注意事項

- 主題需要 Composer 依賴。如果你是從原始碼安裝，請在主題目錄執行 `composer install`。
- 正式環境資產會從 `public/dist` 載入。只有在需要重建前端資產時才需要執行 `npm install` 與 `npm run build`。
- 播客發布與 RSS Feed 產生功能由配套外掛提供，不屬於主題本身。

## 🚀 安裝

1. 將 `a-ripple-song` 主題上傳到 `/wp-content/themes/`，或在後台以 ZIP 安裝。
2. 若使用原始碼版本，請在 `wp-content/themes/a-ripple-song` 執行 `composer install`。
3. 在後台啟用主題。
4. 將選單指派到 `Primary Navigation`。
5. 在 `外觀` → `小工具` 中設定以下區域：
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. 開啟 `A Ripple Song` → `General` 與 `Social Links`，設定 Logo、配色、腳本與社群連結。
7. 可選：安裝並啟用 `A Ripple Song Podcast` 外掛，以啟用單集小工具、播放器 UI 與播客範本。
8. 可選開發步驟：執行 `npm install`，再使用 `npm run dev` 啟用 Vite 開發模式，或用 `npm run build` 產生正式資產。

## ❓ 常見問題

### 一定要安裝播客外掛嗎？

不一定。沒有外掛時，主題也能作為一般部落格主題使用。只有在需要播客單集、底部播放器、訂閱按鈕與播客 RSS 流程時，才需要安裝外掛。

### 為什麼後台會看到依賴缺失警告？

當 Composer 自動載入檔不存在時，主題會顯示管理後台通知。請在 `wp-content/themes/a-ripple-song` 執行 `composer install` 後重新整理後台。

### 哪些小工具區域會影響版面？

`Home Main` 控制首頁模組，`Leftbar Primary` 與 `Rightbar Primary` 控制左右側欄，`Footer Links` 負責頁尾連結網格。

### 開發時可以使用 Vite 嗎？

可以。先執行 `npm install`，再執行 `npm run dev`。當 `http://127.0.0.1:5173` 的 Vite 伺服器可用時，主題會自動載入開發資產；否則會回退到 `public/dist` 的建置檔。

## 🖼️ 截圖

1. 含主題模式切換、搜尋與響應式導覽的頁首
2. 使用 Banner Carousel、Blog List 與作者 / 播客模組組成的首頁
3. 用於 Logo、配色、腳本與社群連結的主題設定頁
4. 啟用配套外掛後，具備固定播放器與播放清單抽屜的播客單集頁

## 📝 更新日誌

### 1.0.0

- A Ripple Song 主題首次公開發佈。

## 🔔 升級提示

### 1.0.0

首次公開發佈。
