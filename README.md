# Ficus

Parent theme WordPress (block theme, WP 7+). Boilerplate PHP/template senza CSS: tutto il frontend vive nel child theme.

## Architettura

```
ficus-theme/          ← questo repo (parent)
lomais-wp/            ← child theme Lomais
pigmentalo-wp/        ← child theme Pigmentalo
```

In locale i tre repo devono stare nella stessa directory padre. Docker monta `../ficus-theme` come tema parent nel container.

In produzione il parent entra come **git submodule** in `wp-content/themes/ficus/`.

---

## Cosa fa il parent, cosa fa il child

| Responsabilità                     | Parent (ficus) | Child |
|------------------------------------|:--------------:|:-----:|
| Templates HTML (FSE)               | X              |       |
| Template parts (header, footer)    | X              | override opzionale |
| PHP: setup, image sizes            | X              |       |
| Block styles (registrazione nomi)  | X              |       |
| GitHub updater                     | X              |       |
| theme.json — struttura + defaults  | X              |       |
| theme.json — brand (colori, font)  |                | X     |
| Pipeline Vite + SCSS + TS          |                | X     |
| Font self-hosted                   |                | X     |
| CSS completo                       |                | X     |

---

## Contratto block styles

Il parent registra i nomi, il child CSS deve stilare tutte le classi corrispondenti.

| Block           | Stile registrato | Classe CSS              |
|-----------------|------------------|-------------------------|
| core/button     | outline          | `.is-style-outline`     |
| core/button     | text-link        | `.is-style-text-link`   |
| core/image      | rounded          | `.is-style-rounded`     |
| core/separator  | leaf             | `.is-style-leaf`        |
| core/quote      | highlight        | `.is-style-highlight`   |
| core/group      | card             | `.is-style-card`        |
| core/group      | section          | `.is-style-section`     |

Rimossi dal core: `separator/wide`, `separator/dots`, `quote/plain`.

---

## Creare un nuovo child theme

```bash
cd ficus-theme
./scaffold/create-child.sh nome-progetto
```

Lo script crea `../nome-progetto-wp/` con struttura completa e fa `npm install`.

**Prerequisiti:** Node 22+ (`nvm use` nella root del progetto).

---

## GitHub Updater

`Ficus_GitHub_Updater` è una classe PHP nel parent, riutilizzabile dal child.

**Requisiti:**
- Repo GitHub pubblico, oppure token in `wp-config.php`:
  ```php
  define( 'FICUS_GITHUB_TOKEN', 'ghp_...' );
  ```
- Release con tag semver: `v1.0.0`, `v1.2.3`

Il parent si aggiorna automaticamente. Il child lo attiva in `functions.php`:
```php
// IMPORTANTE: wrappare in after_setup_theme — il child carica prima del parent.
add_action( 'after_setup_theme', function () {
    new Ficus_GitHub_Updater( 'lomais', 'finoz/wpt-lomais', wp_get_theme()->get('Version') );
} );
```

### Procedura di rilascio aggiornamento

L'updater controlla l'endpoint `releases/latest` di GitHub. Un semplice push **non** innesca nulla: serve una **Release** con tag semver.

```bash
# 1. Bump della versione in style.css
#    Modifica la riga: Version: 1.0.0 → 1.1.0

# 2. Commit e push
git add .
git commit -m "release: v1.1.0 - descrizione modifiche"
git push

# 3. Crea la Release su GitHub (con tag)
gh release create v1.1.0 --title "v1.1.0" --notes "Descrizione modifiche"
#    oppure da browser: repo → Releases → Draft a new release
```

Da quel momento WP vede l'aggiornamento disponibile nel pannello Aspetto → Temi.

> Il tag deve corrispondere esattamente al valore `Version:` in `style.css`, preceduto da `v`.
> Esempio: `Version: 1.1.0` → tag `v1.1.0`.

**Cache:** l'updater mantiene un transient da 12h. Per forzare il controllo subito in sviluppo, aggiungi temporaneamente in `wp-config.php`:
```php
delete_transient( 'ficus_gh_release_' . md5( 'finoz/wpt-ficus' ) );
```
oppure disattiva e riattiva il tema.

La stessa procedura vale identica per i child theme (wpt-lomais, wpt-pigmentalo).

---

## Workflow di sviluppo locale

```bash
# 1. Avvia WordPress
cd lomais-wp
cp .env.example .env
docker-compose up -d

# 2. Avvia Vite (in altra finestra)
cd wp-content/themes/lomais
nvm use
npm run dev

# 3. Visita http://localhost:8080
```

Per la build di produzione:
```bash
cd wp-content/themes/lomais
npm run build
```

---

## Prompt AI per generare un nuovo child theme

Copia questo prompt quando usi un assistente AI per creare o lavorare su un child theme:

```
Sto lavorando su un child theme WordPress chiamato [NOME] che estende il parent theme "ficus".

Architettura:
- ficus (parent): PHP + templates HTML block theme, nessun CSS. Definisce la struttura FSE (templates/, parts/), registra block styles e mette a disposizione ficus_enqueue_assets().
- [NOME] (child): tutto il frontend. Ha la sua pipeline Vite (SCSS + TypeScript), i font self-hosted in assets/fonts/, theme.json con i valori di brand.

Stack:
- WordPress 7, block theme puro (no Twig, no Timber)
- Vite 6, TypeScript 5, Sass (SCSS)
- CSS custom properties da theme.json (non variabili SCSS)
- Docker per ambiente locale (WP su :8080, Vite dev su :5173)
- Node 22 (nvm)

Convenzioni SCSS:
- Nessun valore hard-coded: usa sempre var(--wp--preset--color--*), var(--wp--preset--font-size--*), var(--wp--preset--spacing--*), var(--wp--custom--*)
- Un file per contesto: _header.scss, _footer.scss, _layout.scss, _typography.scss, _reset.scss
- blocks/: un file per ogni core block customizzato

Block styles (nomi registrati dal parent, il child deve stilarli):
- core/button:    .is-style-outline, .is-style-text-link
- core/image:     .is-style-rounded
- core/separator: .is-style-leaf
- core/quote:     .is-style-highlight
- core/group:     .is-style-card, .is-style-section

theme.json del child:
- Definisce SOLO settings.color.palette, settings.typography.fontFamilies (con fontFace per self-hosting), e overrides di styles se necessario.
- WP fa merge con il parent: non ridefinire spacing, fontSizes o layout se non vuoi cambiarli.

Templates: usa quelli del parent (index, front-page, single, page, archive, blank). Override solo se il brand richiede struttura HTML diversa.
```

---

## Struttura file del parent

```
ficus-theme/
├── style.css              solo header WP (nessun CSS)
├── functions.php
├── theme.json             struttura + defaults neutri
├── index.php              fallback WP
├── templates/
│   ├── index.html
│   ├── front-page.html
│   ├── single.html
│   ├── page.html
│   ├── archive.html
│   └── blank.html
├── parts/
│   ├── header.html
│   └── footer.html
├── patterns/
├── inc/
│   ├── setup.php          theme support, image sizes
│   ├── assets.php         ficus_enqueue_assets() — nessun hook diretto
│   ├── block-styles.php   register/unregister block styles
│   └── updater.php        Ficus_GitHub_Updater class
└── scaffold/
    ├── create-child.sh
    ├── child-template/    template del child theme
    └── project-template/  template della root del progetto
```
