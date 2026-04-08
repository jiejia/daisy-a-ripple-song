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

<h3 align="center">Tema WordPress focado em podcast com assets gerenciados por Vite</h3>

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

> Um tema WordPress classico para blogs e podcasts, com pipeline Vite, layout guiado por widgets e integracao opcional com o plugin A Ripple Song Podcast.

## ✨ Descricao

A Ripple Song e um tema WordPress classico pensado para sites que combinam artigos, autores e episodios de podcast. Ele funciona sozinho como um tema de blog tradicional, e quando o plugin complementar esta ativo adiciona cards de episodio, player fixo, interface de playlist e integracao com arquivos de podcast.

### Principais recursos

- Templates classicos para home, arquivo, single, pagina, busca, autor, tag e midia
- Frontend com Vite, Tailwind CSS 4, DaisyUI 5, Alpine.js, transicoes Swup, reproducao com Howler e visualizacao com AudioMotion
- Home e sidebars guiadas por widgets:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - Podcast List e Subscribe Links quando `a-ripple-song-podcast` estiver ativo
- Configuracoes do tema com Carbon Fields:
  - Upload de logo com recorte alvo `220x32`
  - Seletores de tema DaisyUI claro / escuro
  - Sobrescrita do copyright do rodape
  - Injecao de scripts no cabecalho e no rodape
  - Pagina de links sociais
- Integracao com o plugin de podcast:
  - Apresentacao dedicada para `ars_episode`
  - Player de audio fixo e gaveta de playlist
  - Contagem de visualizacoes e reproducoes via AJAX do WordPress
  - Arquivos de tags podem misturar posts e episodios
  - Arquivos de autor podem incluir participacao em podcasts
- Melhorias no editor:
  - Estilo de bloco `Panel` para `core/group`
  - Padrao de bloco `Intro Panel`
- Tema pronto para traducao com arquivos em `resources/lang`

### Observacoes

- O tema espera que as dependencias do Composer estejam presentes. Se voce instalar a partir do codigo-fonte, execute `composer install` dentro do diretorio do tema.
- Os assets de producao sao carregados de `public/dist`. Use `npm install` e `npm run build` apenas se precisar reconstruir o frontend.
- Publicacao de podcast e geracao de RSS pertencem ao plugin complementar, nao ao tema.

## 🚀 Instalacao

1. Envie o tema `a-ripple-song` para `/wp-content/themes/` ou instale-o como ZIP no painel do WordPress.
2. Se estiver usando o checkout do codigo, execute `composer install` em `wp-content/themes/a-ripple-song`.
3. Ative o tema no painel.
4. Atribua seu menu a `Primary Navigation`.
5. Configure estas areas de widgets em `Aparencia` → `Widgets`:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. Abra `A Ripple Song` → `General` e `Social Links` para configurar logo, paletas, scripts e perfis sociais.
7. Opcional: ative `A Ripple Song Podcast` para habilitar widgets de episodio, UI do player e templates especificos de podcast.
8. Opcional para desenvolvimento: execute `npm install` e depois `npm run dev` para o Vite, ou `npm run build` para uma nova compilacao de producao.

## ❓ Perguntas Frequentes

### Eu preciso do plugin de podcast?

Nao. O tema pode funcionar como um tema de blog normal sem o plugin. O plugin so e necessario para episodios, player fixo, botoes de assinatura e fluxo RSS de podcast.

### Por que aparece um aviso de dependencias no wp-admin?

O tema mostra um aviso quando os arquivos de autoload do Composer estao ausentes. Execute `composer install` em `wp-content/themes/a-ripple-song` e recarregue o wp-admin.

### Quais areas de widgets controlam o layout?

`Home Main` controla os modulos da pagina inicial, `Leftbar Primary` e `Rightbar Primary` controlam as colunas laterais, e `Footer Links` renderiza a grade de links do rodape.

### Posso usar Vite durante o desenvolvimento?

Sim. Execute primeiro `npm install` e depois `npm run dev`. Se o servidor Vite em `http://127.0.0.1:5173` estiver disponivel, o tema carregara esses assets automaticamente; caso contrario, usara os arquivos compilados em `public/dist`.

## 🖼️ Capturas de tela

1. Cabecalho do tema com alternancia de modo, busca e navegacao responsiva
2. Home baseada em widgets com Banner Carousel, Blog List e modulos de autores / podcast
3. Paginas de configuracao do tema para logo, paleta, scripts e links sociais
4. Pagina de episodio com player fixo e playlist quando o plugin complementar estiver ativo

## 📝 Changelog

### 1.0.0

- Primeiro lancamento publico do tema A Ripple Song.

## 🔔 Aviso de atualizacao

### 1.0.0

Primeiro lancamento publico.
