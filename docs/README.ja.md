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

<h3 align="center">Vite アセットを採用したポッドキャスト向け WordPress テーマ</h3>

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

> ブログとポッドキャスト向けのクラシック WordPress テーマ。Vite ベースのフロントエンド、ウィジェット駆動レイアウト、そして A Ripple Song Podcast プラグインとの任意の深い連携を備えています。

## ✨ 説明

A Ripple Song は、記事、クリエイター情報、ポッドキャスト配信を同じサイトで扱うためのクラシックテーマです。単体でも通常のブログテーマとして使えますが、配布中のコンパニオンプラグインを有効化すると、エピソードカード、固定プレイヤー、プレイリスト UI、アーカイブ連携などのポッドキャスト向け表示を利用できます。

### 主な機能

- ホーム、アーカイブ、シングル、固定ページ、検索、著者、タグ、メディア向けのクラシックテンプレート
- Vite ベースのフロントエンド構成。Tailwind CSS 4、DaisyUI 5、Alpine.js、Swup、Howler、AudioMotion に対応
- ウィジェット駆動のホームページとサイドバー
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - `a-ripple-song-podcast` 有効時は Podcast List と Subscribe Links
- Carbon Fields によるテーマ設定
  - `220x32` を想定したサイトロゴアップロード
  - ライト / ダーク DaisyUI テーマピッカー
  - フッター著作権テキストの上書き
  - ヘッダー / フッター用のカスタムスクリプト挿入
  - 主要 SNS のリンク設定
- ポッドキャストプラグイン連携
  - `ars_episode` のシングル / アーカイブ表示
  - 固定オーディオプレイヤーとプレイリストドロワー
  - WordPress AJAX による閲覧数 / 再生数トラッキング
  - タグアーカイブで記事とエピソードを同時表示
  - 著者アーカイブでポッドキャスト参加情報を反映
- エディター拡張
  - `core/group` 用の `Panel` ブロックスタイル
  - `Intro Panel` ブロックパターン
- `resources/lang` に翻訳ファイルを同梱

### 注意

- テーマは Composer 依存ファイルを前提としています。ソースコードから導入する場合は、テーマディレクトリで `composer install` を実行してください。
- 本番アセットは `public/dist` から読み込まれます。フロントエンドを再ビルドしたい場合のみ `npm install` と `npm run build` が必要です。
- ポッドキャスト公開や RSS フィード生成はテーマ本体ではなく、コンパニオンプラグイン側の機能です。

## 🚀 インストール

1. `a-ripple-song` テーマを `/wp-content/themes/` にアップロードするか、WP 管理画面から ZIP をインストールします。
2. ソースコード版を使う場合は `wp-content/themes/a-ripple-song` で `composer install` を実行します。
3. WP 管理画面でテーマを有効化します。
4. メニューを `Primary Navigation` に割り当てます。
5. `外観` → `ウィジェット` で以下の領域を設定します。
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. `A Ripple Song` → `General` と `Social Links` でロゴ、配色、スクリプト、SNS プロフィールを設定します。
7. 任意: `A Ripple Song Podcast` を有効化すると、エピソード用ウィジェット、プレイヤー UI、ポッドキャスト専用テンプレートが使えます。
8. 任意の開発手順: `npm install` を実行し、`npm run dev` で Vite 開発サーバー、または `npm run build` で本番ビルドを行います。

## ❓ よくある質問

### ポッドキャストプラグインは必須ですか？

必須ではありません。プラグインなしでも通常のブログテーマとして利用できます。エピソード表示、固定プレイヤー、購読ボタン、Podcast RSS ワークフローが必要な場合のみプラグインが必要です。

### wp-admin に依存関係の警告が出るのはなぜですか？

Composer のオートロードファイルが見つからないと管理画面通知が表示されます。`wp-content/themes/a-ripple-song` で `composer install` を実行し、管理画面を更新してください。

### レイアウトを制御するウィジェット領域はどれですか？

`Home Main` がホームページのモジュール、`Leftbar Primary` と `Rightbar Primary` が左右のサイド列、`Footer Links` がフッターのリンクグリッドを担当します。

### 開発時に Vite を使えますか？

はい。まず `npm install` を実行し、その後 `npm run dev` を実行します。`http://127.0.0.1:5173` の Vite サーバーが利用可能ならテーマは自動でそのアセットを使い、利用できない場合は `public/dist` のビルド済みファイルにフォールバックします。

## 🖼️ スクリーンショット

1. テーマ切替、検索、レスポンシブナビゲーションを備えたヘッダー
2. Banner Carousel、Blog List、著者 / ポッドキャストモジュールで構成されたホームページ
3. ロゴ、配色、スクリプト、SNS を設定するテーマ設定画面
4. コンパニオンプラグイン有効時の固定プレイヤー付きポッドキャストエピソード画面

## 📝 変更履歴

### 1.0.0

- A Ripple Song テーマの初回公開リリース。

## 🔔 アップグレード通知

### 1.0.0

初回公開リリース。
