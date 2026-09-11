# Changes on `abdo` not on `master`

3 commits ahead of `master`, 59 files changed (+3391 / -1626):

- `ac0e96e9` — new
- `899c7cdf` — Enhance Elementor widgets with improved styling and functionality; update CSS for consistent border-radius across components. Implement AJAX pagination for portfolio archive with infinite scroll support. Adjust debug logging for automatic updates and fix various warnings in the codebase.
- `33c969e5` — Update .htaccess and wp-config.php for site restructuring; change database password and update site URLs. Modify custom CSS for portfolio teaser and update Elementor widgets for improved layout and functionality. Enhance debug logging and fix various warnings in the codebase.

## Site restructuring / config

- [wp-config.php](wp-config.php) — DB password changed from empty to `root`; `WP_HOME`/`WP_SITEURL` changed from `/qeematech-new` to `/qeema-tech`
- [.htaccess](.htaccess) — rewrite base updated to match the new `/qeema-tech/` path
- New mu-plugin [wp-content/mu-plugins/qeema-subdirectory-url-fix.php](wp-content/mu-plugins/qeema-subdirectory-url-fix.php) (94 lines) — patches URLs for the subdirectory move
- Added `wp-cli.phar` binary (~7MB) to the repo, plus `.gitignore` / `.vscode/settings.json` tweaks

## Elementor widgets — styling & functionality overhaul

- Heavy rewrites to `portfolio-archive-widget.php`, `portfolio-case-hero-widget.php`, `portfolio-case-story-widget.php`, `portfolio-teaser-widget.php`, `site-header-widget.php` (hundreds of lines changed each)
- `selected-work-widget.php` deleted entirely (276 lines removed)
- Consistent border-radius/styling updates across component CSS (`feature-grid.css`, `hero-section.css`, `site-footer.css`, `site-header.css`, `stats-counter.css`, `testimonials-carousel.css`)
- Main theme stylesheet [qeematech-custom-css/assets/css/style.css](wp-content/plugins/qeematech-custom-css/assets/css/style.css) rewritten substantially (1366 lines changed)

## AJAX pagination / infinite scroll for portfolio archive

- [ajax-archive.js](wp-content/plugins/qeematech-elementor-widgets/assets/js/ajax-archive.js) expanded significantly (+239 lines) to support infinite scroll
- [ajax-archive-endpoints.php](wp-content/plugins/qeematech-elementor-widgets/inc/ajax-archive-endpoints.php) updated to back the new pagination
- Related JS cleanup in `portfolio-grid.js`, `live-apps-carousel.js`, `site-header.js`, `blog-carousel.js`, `stats-counter.js`, `testimonials-carousel.js`, `works-hero-slider.js` — mostly small trims (e.g. removing stray debug/log lines)
- `cpt-acf-registration.php` — 42 lines removed (registration cleanup)

## Debug logging & misc

- `wp-content/debug.log` grew substantially (+980 lines) — from dev/testing activity, not meaningful content
- LiteSpeed cache marker files (`qc.last.news`, `qc.last.vercheck`) bumped
- Generated Elementor CSS files for various posts (post-835, 843–866, 9) — normal Elementor cache regeneration
- A Rank Math sitemap XML file removed (39 lines)

## Net effect

This branch moves the site from the `/qeematech-new` subdirectory to `/qeema-tech`, reworks several Elementor widgets' styling and markup, adds AJAX infinite-scroll pagination to the portfolio archive, and removes the unused `selected-work-widget`.
