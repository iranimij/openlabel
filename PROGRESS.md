# PROGRESS

Fine-grained checkpoint for the OpenLabel build. Updated with every merged PR. The Notion build log (07 · Build Brief, section 7) holds the per-milestone summary.

## Current milestone

M3 · Hyvä render: **in progress** (started 2026-10-11, stacked on the M2 S10 branch until Iman merges M2). M2 · Admin: **done, pending Iman** (2026-10-10): code, tests, docs and CI complete; waiting for the S1 review and the recorded real-person ≤ 3 min test. M1 · Engine done 2026-10-10 (b6efd54, run 38031715575). M0 · Foundation done 2026-10-09.

## M3 slices

Plan approved 2026-10-11 (plan file in the session; summary here). Stacked PRs: S1 is based on `feat/m2-s10-closeout`, every next slice on the previous one. Hyvä slices live in `openlabel-hyva`, storefront specs in `openlabel-dev-env`.

| # | Slice | State |
|---|---|---|
| S1 | Stylesheet generator (`Model/Css/{DesignRules,StylesheetGenerator,Storage,Regenerator}`, CLI `openlabel:css:regenerate`, settings button, regenerate on save) | PR #22 |
| S2 | Label markup partial + `Block\Css` `<link>` + `ViewModel\LabelRenderer` + cache identities | in progress |
| S3 | Hyvä listing slot (Product\Image plugin, collection preload, list-item cache tags) | todo |
| S4 | Hyvä gallery slot (child block + Gallery plugin) | todo |
| S5 | Customer groups × Varnish storefront specs | todo |
| S6 | Tailwind source, CSP, no-rebuild, pointer events, RTL specs | todo |
| S7 | axe-core + Lighthouse gates | todo |
| S8 | CI Playwright job (Varnish, Hyvä 1.4 + 1.3) | todo, needs Iman's answer on Hyvä packages in CI |
| S9 | Custom-theme fallback, docs, close-out | todo |

## M2 slices

Plan: S1 waits for Iman's review (first PR of the milestone); S2–S10 are stacked, each PR based on the previous slice branch.

| # | Slice | State |
|---|---|---|
| S1 | 15 system designs (data patch) + WCAG contrast | PR open, awaiting Iman |
| S2 | Admin menu, routes, label and design grids (inline edit, mass actions, matched count) | PR open (stacked on S1) |
| S3 | Design form (type cards, colours + contrast badge, per-store text, image upload + SVG sanitizer, custom CSS behind ACL) | PR open (stacked on S2) |
| S4 | Label form: basics, who-and-when, save bar, duplicate | PR open (stacked on S3) |
| S5 | Show when: quick toggles + rule tree | PR open (stacked on S4) |
| S6 | Show where: placements + 3×3 position picker | PR open (stacked on S5) |
| S7 | Live preview (sticky, desktop/mobile, contrast warning) | PR open (stacked on S6) |
| S8 | Matched products tab + reindex button | PR open (stacked on S7) |
| S9 | Empty-state starters | PR open (stacked on S8) |
| S10 | System config additions + close-out (settings, docs, CHANGELOG, final review fixes) | PR open (stacked on S9) |

## M1 slices

| # | Slice | State |
|---|---|---|
| S1 | Schema (5 tables + replica), `Api/Data` + repositories with validators, ACL resources, uninstall | done (PR #4, awaiting Iman's review) |
| S2 | Rule model + built-in conditions (OnSale, IsNew, Stock, PriceRange, Rating, ReviewCount) | done (PR #5, stacked on S1, auto-merge) |
| S3 | Indexer: mview, full/list/row, `reindexLabel` diff, parent rows, group rows, replica swap, price/stock/review plugins, crons, cache tags | done (PR #6, stacked on S2, auto-merge) |
| S4 | Variables (15 processors, pool, locale renderer) + HTML allow-list | done (PR #7, stacked on S3, auto-merge) |
| S5 | Resolver (one SELECT) + ViewModel + query-count tests | done (PR #8, stacked on S4, auto-merge) |
| S6 | CLI `openlabel:reindex`, `openlabel:preview` | done (PR #9, stacked on S5, auto-merge) |
| S7 | Close-out: CHANGELOG, docs/engine.md, coverage gate, perf test, build log | done (PR #10, stacked on S6, auto-merge) |

## M0 slices

| # | Slice | State |
|---|---|---|
| S0 | Fixture store `openlabel-dev-env`: Warden env `openlabel.test`, Magento 2.4.9 + sample data, Hyvä 1.4.2 (OSL packages, path repo), Varnish on, storefront CSP enforced, Playwright baseline (20 tests: smoke + Hyvä hook verification, desktop + mobile) | done |
| S1 | `module-base` skeleton + CI template (PHPCS, PHPStan 6 with bitexpert/phpstan-magento, unit + integration matrix 2.4.7-p10/2.4.8-p5/2.4.9 × PHP 8.2/8.3/8.4, `ci-ok` aggregate, Dependabot, issue templates) | done |
| S2 | `module-base` config tab `iranimij` + ACL | done |
| S3 | `module-base` typed helpers `TypedReader`, `SafeJson` | done |
| S4 | `module-base` System › Iranimij › Installed Modules page | done |
| S5 | `module-base` 1.0.0 release (CHANGELOG, LOC guard test, tag) | done when `v1.0.0` tag exists (see Last CI run) |
| S6 | `openlabel` skeleton with config section `openlabel/general/enabled` under tab `iranimij` | done |
| S7 | `openlabel-hyva` skeleton with `hyva_*` layout handles and Tailwind stub | done |
| S8 | `docs/hyva-integration.md`, Notion 02 §4/§5 + 06 F16 updated, entity spec pages (Label, Design, Placement) under 02 · Architecture | done |
| S9 | `iranimij/badger` and `iranimij/magento2-product-labels` marked abandoned (PRs merged; Iman clicks Packagist) | done |
| S10 | Milestone report, build log | done |

## Exact next step

M3 S2: open the PR, then S3 in `openlabel-hyva` (listing slot: Product\\Image plugin, collection preload, list-item cache tags). Earlier note kept for M2: M3 · Hyvä render, in a new session. Before that: Iman reviews PR #12 (S1); then retarget the S10 PR to `main` and close #13–#20 as in M1, and Iman runs the recorded ≤ 3 min custom-label test. Ledger: `openlabel-dev-env/modules/M2-ledger.md`.

## M1 decisions (also in the Notion build log at close-out)

- `openlabel_index.customer_group_id` is `INT NOT NULL`; `-1` = all groups (Iman, 2026-10-09). No DB default because the declarative-schema XSD only accepts `\d+|null`; the indexer always writes the value.
- `openlabel_label.design_id` FK uses `NO ACTION` (MariaDB reports RESTRICT as NO ACTION, which made `setup:db:status` report a perpetual diff).
- `hide_on_zero_variable` stays in 1.1; no column in M1.
- Unit tests carry both `@dataProvider` and `#[DataProvider]`: 2.4.7 ships PHPUnit 9 (annotations only), 2.4.9 ships PHPUnit 12 (attributes only).
- Built-in conditions contribute SQL as `Zend_Db_Expr` through the core `Magento\Rule\Model\Condition\Sql\Builder`; the native attribute condition extends `Magento\CatalogWidget\Model\Rule\Condition\Product` (new core dependency `magento/module-catalog-widget`), which already handles store-scoped attributes. Rule context (store, website, customer group) is set on `Model\Rule\Rule` and read by the conditions.
- Price-based conditions read the price index, which omits out-of-stock products unless "Display Out of Stock Products" is on.
- Is-new date conditions use the default-scope (store 0) `news_from_date`/`news_to_date`; website overrides of those dates are reachable through the native attribute condition.
- MSI is optional: `Model/Condition/Stock/MsiStockData` resolves the MSI interfaces through the object manager and is only used when `Magento_InventorySalesApi` + `Magento_InventoryIndexer` are enabled (`StockDataResolver`).
- Index rows: `parent_product_id` is NULL for direct matches; a parent row (configurable, grouped, bundle, written only with `apply_to_parent`) carries the id of the lowest matching child. Parents come from `catalog_product_relation` through the entity link field.
- Price changes reach the index through plugins on the price indexer actions (`Rows`, `Full`); catalog price rules already go through the price index, so no separate catalogrule plugin. Stock changes through the stock indexer actions, reviews through `Review::aggregate`. MSI multi-source changes that bypass the legacy stock reach the index on the next product save or full reindex (1.0 limitation, documented).
- Cache cleaning inside indexer actions is deferred and executed by the core `CacheCleaner` plugin after the action; `LabelReindexer` (label save, CLI, cron) cleans immediately.
- The integration test framework replaces the lock manager with a dummy, so lock refusal is unit-tested; the integration database carries the sample catalog, so tests filter their own SKUs.
- Variables: processors return typed `Value`s (text, html, number, currency, date); the `Renderer` formats for the locale, escapes values, keeps `{BR}`, applies the allow-list and leaves unknown variables literal. A zero number renders as "0" and is reported as empty (hiding is the 1.1 flag). `{SPECIAL_ENDS_IN}` treats `special_to_date` as valid through that day (UTC). `{STOCK_QTY}` is the salable quantity (orders reduce it).
- Resolver: a stack (area + position) is limited by the `max_labels` of its highest-priority label; `hide_lower_priority` suppresses higher priority numbers across all areas of that product; the design inside a `ResolvedLabel` carries the store-resolved text in its default slot. The `Labels` view model memoizes per request and returns nothing (no query) when the module is disabled for the store.
- Matrix lessons (2.4.7/2.4.8): from 2.4.8 the core Sql Builder wraps numeric fields in IFNULL(field, 0) (composites need a never-matching sentinel); 2.4.8 keeps out-of-stock products in the price index; CI's Elasticsearch 8 rejects 2.4.7 search writes (tests schedule the search indexer); the 2.4.7 test framework nulls test-case properties (declare them nullable). Run the full matrix on the branch (`gh workflow run CI --ref <branch>`) before merging anything that touches SQL.
- Local PHPStan runs with `modules/phpstan-local.neon` (level 6, generated factories scanned); CI uses `bitexpert/phpstan-magento`. After `setup:di:compile` on the fixture, delete `generated/metadata` or the integration sandbox install fails on the disabled 2FA module.

## Test inventory

| Repo | Unit | Integration | Other |
|---|---|---|---|
| module-base | 19 tests / 48 assertions | 6 tests / 12 assertions | PHPCS clean, PHPStan 6 clean, LOC guard (408 / 1000 lines) |
| openlabel (after M1 S7) | 117 / 340 | 59 / 238 (+ perf budget on main/nightly) | PHPCS zero errors, PHPStan 6 clean, coverage gate ≥ 85 % (Variable 96.7, Condition 97.7, Resolver 100, Rule 98.3) |
| openlabel (after M2) | 203 | 153 (1 skipped: perf) | PHPCS zero errors, PHPStan 6 clean, coverage gate ≥ 85 % now also over `Model/Css` and `Model/Image` |
| openlabel-hyva | 4 / 13 | 1 | PHPCS clean, PHPStan 6 clean |
| openlabel-dev-env | — | — | Playwright 43 / 43 (20 storefront desktop + mobile, 23 admin incl. login setup) |

## Open questions for Iman

- Packagist: register `iranimij/module-base` after the `v1.0.0` tag (CI pre-install scripts fall back to a VCS repository until then) and mark the two old packages abandoned (composer.json already carries the field).
- Hyvä portal keys (optional): the fixture and CI use the OSL packages from your copy; with keys the fixture can switch to the portal repository.

## Known failing or skipped tests

- `Test/Integration/Performance/FullReindexTest` is skipped unless `OPENLABEL_PERF=true` (CI sets it on `main` and nightly, not on pull requests; agreed 2026-10-09). (Playwright `configurable-selection-changed` test skips only if the sample-data configurable product `radiant-tee` is missing.)

## Fixture gotchas (see openlabel-dev-env README)

Composer `--no-plugins` in the Warden container; background jobs via `setsid nohup` inside the container; CSP enforce flag set via SQL; OpenSearch image pinned to 2.5.0; admin 2FA and URL secret keys disabled.

## Last CI run

See the links in the Notion build log entry for 2026-10-09 (module-base, openlabel, openlabel-hyva).
