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

<h3 align="center">Theme WordPress orienté podcast avec assets gérés par Vite</h3>

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

> Un theme WordPress classique pour les blogs et podcasts, avec pipeline Vite, mises en page pilotées par widgets et intégration optionnelle poussée avec le plugin A Ripple Song Podcast.

## ✨ Description

A Ripple Song est un theme WordPress classique conçu pour les sites qui publient des articles, présentent des auteurs et diffusent des episodes de podcast. Il fonctionne seul comme un theme de blog standard, et lorsqu'on active le plugin compagnon il ajoute des cartes d'episode, un lecteur fixe, une playlist et une integration des archives podcast.

### Fonctionnalites principales

- Templates classiques pour l'accueil, les archives, les articles seuls, les pages, la recherche, les auteurs, les tags et les medias
- Pipeline frontend base sur Vite avec Tailwind CSS 4, DaisyUI 5, Alpine.js, transitions Swup, lecture audio via Howler et visualisation AudioMotion
- Accueil et barres laterales pilotes par widgets :
  - Banner Carousel
  - Blog List
  - Authors List
  - Footer Links
  - Tags Cloud
  - Podcast List et Subscribe Links lorsque `a-ripple-song-podcast` est actif
- Reglages du theme avec Carbon Fields :
  - Upload du logo avec cible de recadrage `220x32`
  - Selecteurs de theme DaisyUI clair / sombre
  - Surcharge du copyright du pied de page
  - Injection de scripts dans l'en-tete et le pied de page
  - Page de liens sociaux pour les plateformes courantes
- Integration avec le plugin podcast :
  - Affichage dedie des contenus `ars_episode`
  - Lecteur audio fixe et tiroir de playlist
  - Suivi des vues et lectures via AJAX WordPress
  - Archives par tag pouvant melanger articles et episodes
  - Archives auteur pouvant inclure la participation aux podcasts
- Ameliorations de l'editeur :
  - Style de bloc `Panel` pour `core/group`
  - Pattern de bloc `Intro Panel`
- Theme pret pour la traduction avec packs de langue dans `resources/lang`

### Notes

- Le theme attend la presence des dependances Composer. Si vous installez depuis les sources, executez `composer install` dans le dossier du theme.
- Les assets de production sont charges depuis `public/dist`. N'utilisez `npm install` et `npm run build` que si vous devez reconstruire le frontend.
- La publication podcast et la generation RSS sont fournies par le plugin compagnon, pas par le theme lui-meme.

## 🚀 Installation

1. Televersez le theme `a-ripple-song` dans `/wp-content/themes/` ou installez-le en ZIP depuis l'administration WordPress.
2. Si vous utilisez les sources, lancez `composer install` dans `wp-content/themes/a-ripple-song`.
3. Activez le theme dans l'administration.
4. Assignez votre menu a `Primary Navigation`.
5. Configurez ces zones de widgets dans `Apparence` → `Widgets` :
   - `Home Main`
   - `Leftbar Primary`
   - `Rightbar Primary`
   - `Footer Links`
6. Ouvrez `A Ripple Song` → `General` et `Social Links` pour configurer le logo, les palettes, les scripts et les profils sociaux.
7. Optionnel : activez `A Ripple Song Podcast` pour obtenir les widgets d'episodes, le lecteur et les templates podcast.
8. Optionnel pour le developpement : lancez `npm install`, puis `npm run dev` pour Vite ou `npm run build` pour une reconstruction de production.

## ❓ Foire Aux Questions

### Ai-je besoin du plugin podcast ?

Non. Le theme fonctionne comme un theme de blog classique sans le plugin. Le plugin n'est necessaire que pour les episodes, le lecteur fixe, les boutons d'abonnement et le flux RSS podcast.

### Pourquoi ai-je un avertissement de dependances dans l'admin ?

Le theme affiche une notice lorsque les fichiers d'autoload Composer sont absents. Executez `composer install` dans `wp-content/themes/a-ripple-song`, puis rechargez l'administration.

### Quelles zones de widgets controlent la mise en page ?

`Home Main` pilote les modules de la page d'accueil, `Leftbar Primary` et `Rightbar Primary` controlent les colonnes laterales, et `Footer Links` genere la grille de liens du pied de page.

### Puis-je utiliser Vite en developpement ?

Oui. Executez d'abord `npm install`, puis `npm run dev`. Si le serveur Vite `http://127.0.0.1:5173` est disponible, le theme charge automatiquement ses assets ; sinon il revient aux fichiers compiles dans `public/dist`.

## 🖼️ Captures d'ecran

1. En-tete du theme avec bascule de mode, recherche et navigation responsive
2. Page d'accueil pilotee par widgets avec Banner Carousel, Blog List et modules auteurs / podcast
3. Pages de reglages du theme pour le logo, la palette, les scripts et les liens sociaux
4. Page d'episode podcast avec lecteur fixe et tiroir de playlist lorsque le plugin compagnon est actif

## 📝 Journal des modifications

### 1.0.0

- Premiere version publique du theme A Ripple Song.

## 🔔 Notice de mise a niveau

### 1.0.0

Premiere version publique.
