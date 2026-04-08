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

<h3 align="center">Vite 자산 파이프라인을 사용하는 팟캐스트 중심 WordPress 테마</h3>

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

> 블로그와 팟캐스트 사이트를 위한 클래식 WordPress 테마로, Vite 기반 프런트엔드, 위젯 중심 레이아웃, 그리고 A Ripple Song Podcast 플러그인과의 선택적 심화 연동을 제공합니다.

## ✨ 설명

A Ripple Song은 글, 크리에이터 정보, 팟캐스트 에피소드를 함께 운영하는 사이트를 위한 클래식 WordPress 테마입니다. 단독으로는 일반 블로그 테마로 사용할 수 있고, 컴패니언 플러그인을 활성화하면 에피소드 카드, 하단 고정 플레이어, 재생목록 UI, 아카이브 연동 같은 팟캐스트 전용 표시가 추가됩니다.

### 주요 기능

- 홈, 아카이브, 싱글, 페이지, 검색, 작성자, 태그, 미디어용 클래식 템플릿
- Tailwind CSS 4, DaisyUI 5, Alpine.js, Swup, Howler, AudioMotion을 포함한 Vite 기반 프런트엔드 구성
- 위젯 중심 홈 및 사이드바 레이아웃
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - `a-ripple-song-podcast` 활성화 시 Podcast List 및 Subscribe Links
- Carbon Fields 기반 테마 설정
  - `220x32` 기준 사이트 로고 업로드
  - 라이트 / 다크 DaisyUI 테마 선택기
  - 푸터 저작권 문구 덮어쓰기
  - 헤더 / 푸터 스크립트 삽입
  - 주요 소셜 플랫폼 링크 설정
- 팟캐스트 플러그인 연동
  - `ars_episode` 싱글 / 아카이브 표시
  - 고정 오디오 플레이어와 재생목록 드로어
  - WordPress AJAX 기반 조회수 / 재생수 추적
  - 태그 아카이브에서 글과 에피소드를 함께 표시
  - 작성자 아카이브에 팟캐스트 참여 정보 포함
- 에디터 확장
  - `core/group`용 `Panel` 블록 스타일
  - `Intro Panel` 블록 패턴
- `resources/lang`에 번역 파일 포함

### 참고

- 이 테마는 Composer 의존성을 필요로 합니다. 소스 코드로 설치하는 경우 테마 디렉터리에서 `composer install`을 실행하세요.
- 운영 자산은 `public/dist`에서 로드됩니다. 프런트엔드 자산을 다시 빌드해야 할 때만 `npm install`과 `npm run build`가 필요합니다.
- 팟캐스트 발행과 RSS 피드 생성은 테마 자체 기능이 아니라 컴패니언 플러그인 기능입니다.

## 🚀 설치

1. `a-ripple-song` 테마를 `/wp-content/themes/`에 업로드하거나 WP 관리자에서 ZIP으로 설치합니다.
2. 소스 체크아웃을 사용하는 경우 `wp-content/themes/a-ripple-song`에서 `composer install`을 실행합니다.
3. WP 관리자에서 테마를 활성화합니다.
4. 메뉴를 `Primary Navigation`에 할당합니다.
5. `외모` → `위젯`에서 다음 영역을 구성합니다.
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. `A Ripple Song` → `General` 및 `Social Links`에서 로고, 색상, 스크립트, 소셜 프로필을 설정합니다.
7. 선택 사항: `A Ripple Song Podcast` 플러그인을 활성화하면 에피소드 위젯, 플레이어 UI, 팟캐스트 전용 템플릿을 사용할 수 있습니다.
8. 선택 사항 개발 단계: `npm install` 후 `npm run dev`로 Vite 개발 서버를 실행하거나 `npm run build`로 운영 빌드를 만듭니다.

## ❓ 자주 묻는 질문

### 팟캐스트 플러그인이 꼭 필요한가요?

아니요. 플러그인 없이도 일반 블로그 테마로 사용할 수 있습니다. 에피소드 표시, 고정 플레이어, 구독 버튼, Podcast RSS 워크플로가 필요할 때만 플러그인이 필요합니다.

### wp-admin에 의존성 경고가 보이는 이유는 무엇인가요?

Composer 오토로드 파일이 없으면 관리자 알림이 표시됩니다. `wp-content/themes/a-ripple-song`에서 `composer install`을 실행한 뒤 wp-admin을 새로고침하세요.

### 어떤 위젯 영역이 레이아웃을 제어하나요?

`Home Main`은 홈페이지 모듈, `Leftbar Primary`와 `Rightbar Primary`는 좌우 사이드 컬럼, `Footer Links`는 푸터 링크 그리드를 담당합니다.

### 개발 중에 Vite를 사용할 수 있나요?

네. 먼저 `npm install`을 실행한 뒤 `npm run dev`를 실행하세요. `http://127.0.0.1:5173`의 Vite 서버가 열려 있으면 테마가 자동으로 해당 자산을 사용하고, 그렇지 않으면 `public/dist`의 빌드 파일로 대체됩니다.

## 🖼️ 스크린샷

1. 테마 모드 전환, 검색, 반응형 내비게이션이 포함된 헤더
2. Banner Carousel, Blog List, 작성자 / 팟캐스트 모듈로 구성한 홈페이지
3. 로고, 팔레트, 스크립트, 소셜 링크를 설정하는 테마 설정 페이지
4. 컴패니언 플러그인 활성화 시 하단 플레이어와 재생목록 드로어가 있는 팟캐스트 에피소드 화면

## 📝 변경 로그

### 1.0.0

- A Ripple Song 테마의 첫 공개 릴리스.

## 🔔 업그레이드 안내

### 1.0.0

첫 공개 릴리스.
