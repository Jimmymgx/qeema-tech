# September 26 SEO recovery

## Changes

- Load `inc/seo-migration-recovery.php` from the existing engine plugin.
- Add 218 exact legacy aliases in `inc/seo-legacy-redirects.php`: 217 recovered from the old database's `_wp_old_slug` history, matched to a published target with the same post type, current slug and title, plus the Green Wing spelling variant. Evidence is in `redirect-evidence.csv`.
- Resolve targets using post type/slug and `get_permalink()`, not installation-specific post IDs or domains. Redirect only exact GET/HEAD 404s. Existing pages, drafts, password-protected targets, per-post noindex targets, search/preview/pagination requests and unknown paths are not redirected by this module. Keep only recognized campaign query parameters.
- Supply missing Rank Math XML sitemap settings for portfolio and live-apps. Explicit administrator exclusions and per-post noindex directives remain effective. Nothing globally enables tag indexing.
- Invalidate Rank Math sitemap cache once when an administrator next visits wp-admin after merging the code.
- Disable the optional homepage preloader and its JavaScript by default. It previously imposed a minimum visible wait and depended on a script that could be delayed by optimization. This does not establish that the overlay was the only cause of poor field LCP. To deliberately restore it, use `add_filter( 'qeema_enable_preloader', '__return_true' );` in site-specific code.
- Remove syntactically invalid JSON-LD script blocks from rendered article content after shortcode/paragraph processing. The affected article contained a sixth, malformed manual FAQ block using `type = "application/ld+json"`, alongside five valid blocks. The content filter recognizes attribute whitespace, preserves valid JSON-LD and visible content, and does not change Rank Math's head output or stored article content.

## Files to merge

Under `wp-content/plugins/qeematech-elementor-widgets/`:

- `qeematech-elementor-widgets.php` (one new require)
- `inc/seo-migration-recovery.php`
- `inc/seo-legacy-redirects.php`
- `inc/structured-data-content.php`
- `tests/seo-migration-recovery-test.php` and `tests/seo-recovery-wordpress-test.php` (verification tools)
- `tests/structured-data-content-test.php`
- `docs/SEO-RECOVERY.md` and `docs/redirect-evidence.csv` (review evidence)

Under `wp-content/plugins/qeematech-custom-css/`:

- `qeematech-custom-css.php` (preloader default)

Merge only these changes into production's current files. Do not replace production wp-config.php, database, debug.log, uploads or the entire WordPress install with the local versions. No database import is required for these fixes.

## Deployment verification

1. Back up the affected live plugin files, merge the code and visit wp-admin as an administrator once to invalidate Rank Math's sitemap cache. Purge LiteSpeed/server/CDN caches so old 404s and sitemap responses are not served. If a new sitemap still returns 404, save Rank Math's sitemap settings and WordPress Permalinks once; do not flush rewrite rules on every request.
2. Confirm `/sitemap_index.xml` lists `/portfolio-sitemap.xml` and `/live-apps-sitemap.xml`, and both contain public canonical project/app URLs. If production explicitly disables either content type, review that setting and enable it in Rank Math; this code intentionally preserves explicit choices.
3. Confirm `/portfolio/greenwing/` returns one 301 to `/portfolio/green-wing/`, whose final response is 200, on the production host. Confirm `/portfolio/test/` points to `/portfolio/activ/` if Activ remains published. Check Arabic article redirects too using the evidence CSV.
4. Confirm `/portfolio/cairo-zoo/` remains 404 while its post is a draft, and random nonexistent URLs remain 404. Confirm important existing pages still return 200 and retain correct canonical tags.
5. Confirm the homepage renders without a loading overlay, including before the first interaction. Re-run mobile Lighthouse/PageSpeed and inspect the LCP element and layout-shift sources. Field results do not update immediately and no improved metric is claimed by this patch.
6. Submit/refresh the sitemap index in Search Console. Use URL Inspection for a few restored canonical targets; request indexing for valuable targets, not old redirected URLs. Google controls indexing and timing.
7. Test `/برمجة-وتصميم-مواقع-الويب-في-مصر/` with Google's Rich Results Test after purging cached HTML. The local response has five JSON-LD blocks, all syntactically valid. Once Google's live test confirms the parsing error is absent, validate the specific Unparsable structured data issue in Search Console. Syntax validation does not guarantee rich-result eligibility.

## Tests

From the plugin directory:

`php tests/seo-migration-recovery-test.php`

`php tests/structured-data-content-test.php`

From the WordPress root:

`php wp-cli.phar eval-file wp-content/plugins/qeematech-elementor-widgets/tests/seo-recovery-wordpress-test.php`

The second test reads the installed WordPress environment. It checks every map target against published content and the effective sitemap options; it makes no content edits. Counts on production can differ if posts were removed, made private or explicitly excluded after the local snapshot.

## Unresolved content and further performance work

Seven of the 44 still-missing portfolio URLs were already drafts in both local databases; they were not republished. Most other missing project URLs are absent from both snapshots. They require owner confirmation and, if restoration is intended, a more recent pre-launch content backup. Do not map them to the homepage or unrelated projects.

The live homepage's large portfolio/carousel payload, LCP image priority and layout shifts still require rendered-browser profiling. This batch removes the confirmed preloader wait but does not redesign or truncate the portfolio filters. The September 19 indexing drop cannot yet be fully attributed to any one cause.
