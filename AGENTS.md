# Repository Guidelines

## Project Structure & Module Organization

This is a server-rendered PHP website. Root-level `*.php` files are public pages (for example, `index.php`, `servicios.php`, and `novedades.php`). Shared fragments such as `menu.php`, `footer.php`, and `slider-home.php` are included with `require_once`.

Static client assets are organized by type: `css/` contains Bootstrap, Font Awesome, and site styles (`main.css`); `js/` contains jQuery, Bootstrap, and site behavior (`main.js`); `images/`, `fonts/`, and `pdf/` hold published assets. Individual news items and their supporting images are grouped under `novedades/<article-slug>/`. The `galeria/` directory is a self-contained gallery component.

## Development & Verification Commands

No dependency manager, build step, linter, or automated test suite is configured. Serve it through Apache/PHP and review pages in a browser. For a quick local alternative, run:

```sh
php -S localhost:8000
```

Then open `http://localhost:8000/index.php`. Validate edited PHP files before committing:

```sh
php -l index.php
php -l novedades/my-article/index.php
```

## Coding Style & Naming Conventions

Follow the surrounding file's formatting: PHP templates currently use four-space indentation in most page markup, while some legacy fragments use tabs. Preserve the local convention rather than reformatting unrelated lines. Keep HTML attributes lowercase, use single quotes for simple `require_once` paths, and place shared layout in fragments instead of duplicating it across pages.

Use lowercase, hyphen-separated names for public content and asset paths, such as `novedades/centenario-porvenir/` and `images/novedades/escala-salarial-2014.jpg`. Keep CSS additions in `css/main.css` and JavaScript additions in `js/main.js` unless modifying the gallery's isolated files.

## Testing Guidelines

Manually check every affected page at desktop and narrow viewport widths. Confirm navigation links, included header/footer fragments, images, PDFs, and gallery/lightbox behavior work from the intended deployment path. Run `php -l` on each changed PHP file; there is no coverage target or test naming convention currently defined.

## Commit & Pull Request Guidelines

The repository has no commit history yet, so no established message convention can be inferred. Use concise imperative messages with a focused scope, for example `Add 2026 wage-scale notice`. Keep commits limited to one change set. Pull requests should explain the visible change, list affected URLs, link any relevant issue, and include screenshots for layout or styling changes. Note any new downloadable document or image asset explicitly.

## Security & Configuration

Do not commit credentials or environment-specific configuration. Check that relative links and PHP includes still resolve under the site's deployed `/web/` path before publishing.

## Mobile Application & System Rules

1. **Design & Navigation Consistency**:
   - Always maintain visual, structural, and behavioral consistency across the entire app.
   - Reuse existing design tokens (`AppColors`), typography (`Manrope`), card styles, border radiuses, and navigation conventions. Do not create isolated or mismatched UI patterns.
   - The Administrator view navigation must follow the same pattern as the main app: a bottom `NavigationBar` with consistent icons and active indicators. Do not place primary navigation tabs/icons in the top header.
   - Header is reserved for the institutional brand (`S.O.E.P.La` + `Sindicato de Obreros y Empleados Panaderos de Lanús`) and action controls (such as "Vista Afiliado"). Do not duplicate action buttons at the bottom.

2. **Date Format Standard**:
   - All dates in the mobile application (displays, cards, forms, dialogs, inputs) MUST strictly use the Argentine format: `dd/mm/aaaa` (`DD/MM/YYYY`).
   - Any input accepting or displaying dates must display `dd/mm/aaaa` and parse/convert to ISO `YYYY-MM-DD` for backend persistence. Provide date pickers formatting to `dd/mm/aaaa`.

3. **Event Management & Pickup Branches**:
   - The sole physical branch for benefits and event pickup is Lanús (`Sede Central Lanús · Llavallol 902`).
   - Administrators must have full visibility into the detailed list of affiliates and child beneficiaries enrolled in every event, including school levels, garment sizes, and pickup fulfillment status.
   - Voucher validation must support both manual code entry and camera QR code scanning.

