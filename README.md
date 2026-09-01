# Qeema Tech — qeematech.net

*A ground-up WordPress + Elementor rebuild of the qeematech.net marketing site —
real portfolio work, real client apps, real testimonials, nothing staged.*

This repository is the full local build: WordPress core, the plugins that run
the site, and two custom, hand-written plugins that turn Elementor into
qeematech.net's own design system rather than a generic page-builder template.

---

## The stack

| Layer | What's running |
|---|---|
| CMS | WordPress, theme: **Hello Elementor** (a blank canvas — the design lives in the widgets below, not the theme) |
| Page building | **Elementor** + **Elementor Pro** |
| Content fields | **Advanced Custom Fields Pro** |
| SEO | **Rank Math**, **Fast Indexing API** |
| Performance | **LiteSpeed Cache** |
| Analytics / tracking | **Google Tag Manager**, **Google Site Kit** |
| Security & ops | **Loginizer**, **User Role Editor**, **User Activity Log**, **Redirection** |
| Mail | **WP Mail SMTP** |
| Custom engine | **qeematech-elementor-widgets** — see below |
| Custom styling | **qeematech-custom-css** — see below |

---

## The custom engine: `qeematech-elementor-widgets`

`wp-content/plugins/qeematech-elementor-widgets/` is where the actual site
lives. It's not a handful of one-off tweaks — it's a self-contained system:

- **32 Elementor widgets** (`widgets/`) — heroes, portfolio grids, app
  showcases, testimonial carousels, CTAs, FAQs, site header/footer — each
  its own class with a `register_controls()` and a `render()`.
- **3 custom post types** (`inc/cpt-acf-registration.php`) — `portfolio`,
  `live-apps`, and `testimonial` — backing the "proof it's real" widgets
  above with actual client work, actual published apps, actual client
  videos.
- **Page-builder scripts** (`inc/create-*.php`) — most static pages aren't
  hand-assembled in the Elementor editor; a script queries the real content
  a page needs and assembles it into that page's `_elementor_data`, then
  reads it back to verify the write actually took before committing.
- **Migration tooling** (`inc/import-*-endpoint.php`) — pulls real content
  across from the live qeematech.net into this site's own post types. Off
  by default; see [Local setup](#local-setup) to enable it.

**The rule that shapes all of it:** if a widget has nothing genuine to show —
an empty field, a missing image, a CPT with no entries — it fails quietly and
renders nothing, rather than showing a placeholder standing in for real
content.

## The shared stylesheet: `qeematech-custom-css`

`wp-content/plugins/qeematech-custom-css/` loads a single stylesheet in right
after Elementor's own CSS, so it always wins the cascade. It's the shared
visual language every widget above draws from — glass-panel cards, glow
blobs, chip pills, the site's keyframe animations — kept in one place instead
of scattered across dozens of Elementor "custom CSS" boxes.

---

## Repository layout

```
qeematech-new/
├── wp-admin/, wp-includes/, wp-*.php     WordPress core
├── wp-config.php                          local environment config
└── wp-content/
    ├── themes/hello-elementor/            blank canvas theme
    ├── plugins/
    │   ├── qeematech-elementor-widgets/   the custom engine (see above)
    │   ├── qeematech-custom-css/          the shared stylesheet
    │   └── ...                            Elementor, ACF, Rank Math, etc.
    └── uploads/                           real media: logos, screenshots, portfolio images
```

---

## Local setup

1. Serve this repo through a local PHP/MySQL stack (built and tested against
   XAMPP).
2. Copy `wp-config-sample.php` to `wp-config.php` if it isn't already present,
   and point `DB_*` at your local database.
3. Import the database, activate **qeematech-elementor-widgets** and
   **qeematech-custom-css** alongside Elementor and ACF Pro.
4. Static pages that don't already exist (Live App, Testimonials, Clients,
   portfolio categories, etc.) are created automatically on the next request
   by the page-builder scripts in `inc/` — no manual page-building required.

To re-run a content import from the live site, enable the migration tools
first:

```php
// wp-config.php
define( 'QEEMA_ENABLE_LIVE_IMPORT_TOOLS', true );
```

then use **Tools → Import Live Content** in wp-admin.

---

## Content model

| Post type | Real content it holds |
|---|---|
| `portfolio` | Client projects, grouped by category (websites, mobile apps, contracting, ...) |
| `live-apps` | Published apps with genuine Google Play / App Store links |
| `testimonial` | Client video testimonials |

No synthetic seed data ships in this repo's post types — every entry that
renders on the live site came from a real import or was entered by hand.
