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

<h3 align="center">Tema WordPress orientado a podcasts con assets gestionados por Vite</h3>

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

> Un tema WordPress clasico para blogs y podcasts, con pipeline Vite, maquetacion basada en widgets e integracion opcional con el plugin A Ripple Song Podcast.

## ✨ Descripcion

A Ripple Song es un tema clasico de WordPress pensado para sitios que mezclan articulos, autores y episodios de podcast. Puede usarse solo como tema de blog normal, y cuando el plugin complementario esta activo agrega tarjetas de episodios, reproductor fijo, interfaz de playlist e integracion con archivos de podcast.

### Funciones principales

- Plantillas clasicas para inicio, archivos, entradas individuales, paginas, busqueda, autores, etiquetas y medios
- Frontend con Vite, Tailwind CSS 4, DaisyUI 5, Alpine.js, transiciones Swup, reproduccion con Howler y visualizacion con AudioMotion
- Inicio y barras laterales guiadas por widgets:
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - Podcast List y Subscribe Links cuando `a-ripple-song-podcast` esta activo
- Ajustes del tema con Carbon Fields:
  - Subida de logotipo con recorte objetivo `220x32`
  - Selectores de tema DaisyUI claro / oscuro
  - Reemplazo del copyright del pie
  - Inyeccion de scripts en cabecera y pie
  - Pagina de enlaces sociales
- Integracion con el plugin de podcast:
  - Presentacion dedicada para `ars_episode`
  - Reproductor de audio fijo y cajon de playlist
  - Seguimiento de vistas y reproducciones via AJAX de WordPress
  - Los archivos por etiqueta pueden incluir posts y episodios
  - Los archivos de autor pueden incluir participacion en podcasts
- Mejoras del editor:
  - Estilo de bloque `Panel` para `core/group`
  - Patron de bloque `Intro Panel`
- Tema preparado para traduccion con paquetes incluidos en `resources/lang`

### Notas

- El tema necesita dependencias de Composer. Si lo instalas desde el codigo fuente, ejecuta `composer install` dentro del directorio del tema.
- Los assets de produccion se cargan desde `public/dist`. Solo necesitas `npm install` y `npm run build` si vas a reconstruir el frontend.
- La publicacion de podcasts y la generacion del RSS pertenecen al plugin complementario, no al tema.

## 🚀 Instalacion

1. Sube el tema `a-ripple-song` a `/wp-content/themes/` o instalalo como ZIP desde el admin.
2. Si usas el codigo fuente, ejecuta `composer install` en `wp-content/themes/a-ripple-song`.
3. Activa el tema en WordPress.
4. Asigna tu menu a `Primary Navigation`.
5. Configura estas areas de widgets en `Apariencia` → `Widgets`:
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. Abre `A Ripple Song` → `General` y `Social Links` para configurar logo, paletas, scripts y perfiles sociales.
7. Opcional: activa `A Ripple Song Podcast` para habilitar widgets de episodios, la interfaz del reproductor y plantillas especificas de podcast.
8. Opcional para desarrollo: ejecuta `npm install` y luego `npm run dev` para Vite o `npm run build` para una reconstruccion de produccion.

## ❓ Preguntas Frecuentes

### Necesito el plugin de podcast?

No. El tema puede funcionar como un tema de blog normal sin el plugin. Solo necesitas el plugin para episodios, reproductor fijo, botones de suscripcion y flujo RSS de podcast.

### Por que aparece una advertencia de dependencias en wp-admin?

El tema muestra un aviso si faltan los archivos de autoload de Composer. Ejecuta `composer install` en `wp-content/themes/a-ripple-song` y recarga wp-admin.

### Que areas de widgets controlan la maquetacion?

`Home Main` controla los modulos de la portada, `Leftbar Primary` y `Rightbar Primary` controlan las columnas laterales, y `Footer Links` renderiza la cuadricula de enlaces del pie.

### Puedo usar Vite durante el desarrollo?

Si. Ejecuta primero `npm install` y luego `npm run dev`. Si el servidor Vite en `http://127.0.0.1:5173` esta disponible, el tema cargara automaticamente esos assets; de lo contrario usara los archivos compilados de `public/dist`.

## 🖼️ Capturas

1. Cabecera del tema con cambio de modo, busqueda y navegacion responsive
2. Portada basada en widgets con Banner Carousel, Blog List y modulos de autores / podcast
3. Paginas de ajustes para logo, paleta, scripts y enlaces sociales
4. Pagina de episodio de podcast con reproductor fijo y playlist cuando el plugin complementario esta activo

## 📝 Historial de cambios

### 1.0.0

- Primera version publica del tema A Ripple Song.

## 🔔 Aviso de actualizacion

### 1.0.0

Primera version publica.
