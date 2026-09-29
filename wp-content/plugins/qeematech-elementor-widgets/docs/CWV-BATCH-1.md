# Core Web Vitals batch 1 — portfolio hero

This batch addresses the confirmed mismatch between the portfolio hero widget and LiteSpeed's optimized HTML. The widget requested eager/high-priority loading, but LiteSpeed rewrote the above-the-fold image to a placeholder plus `data-src`. The patch adds LiteSpeed's supported `data-no-lazy="1"` attribute to both app and website hero variants.

It also removes `qeema-reveal` from the hero copy and hero visual. These elements are above the fold and should be visible at first paint; scroll-reveal remains available to sections below the hero.

No preload was added. The image is an HTML `<img>` discovered during parsing, so preload would only be justified by a measured discovery delay and must match the responsive candidate to avoid duplicate downloads.

## Validation

- Run `php tests/portfolio-case-hero-performance-test.php`.
- Open a local portfolio page and confirm its hero image contains `src`, `loading="eager"`, `fetchpriority="high"`, and `data-no-lazy="1"`.
- Confirm neither `.qeema-cs-hero__copy` nor `.qeema-cs-hero__visual` has `.qeema-reveal`.
- After production merge, purge LiteSpeed once and verify the optimized HTML still has the real image in `src` rather than only `data-src`.
- Profile a representative desktop project three times with a fixed environment and record the median LCP plus its TTFB/resource delay/download/render-delay breakdown.

This patch does not claim the field LCP is already fixed. Search Console uses grouped 28-day field data; the production HTML and repeatable browser traces must be checked first.
