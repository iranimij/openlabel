# PROGRESS

Fine-grained checkpoint for the OpenLabel build. Updated with every merged PR. The Notion build log (07 · Build Brief, section 7) holds the per-milestone summary.

## Current milestone

M1 · Engine: **in progress** (started 2026-10-09, target 2026-11-09). M0 · Foundation done 2026-10-09.

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

M1 code is complete and **PR #10 (`feat/m1-s7-closeout`, run 37997573763) is green on every job** including the new coverage gate. It contains all M1 commits (S1–S7) in order. Recommended merge path: Iman approves PR #4 (schema contract); once it merges, retarget #10 to `main` (`gh pr edit 10 --base main`) and close #5–#9 as superseded (their heads predate the CI fixes and were hit by the Docker Hub pull-rate limit). When `main` is green on the full matrix, M1 is done; M2 (admin) starts in a new session with the kickoff prompt.

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
- Local PHPStan runs with `modules/phpstan-local.neon` (level 6, generated factories scanned); CI uses `bitexpert/phpstan-magento`. After `setup:di:compile` on the fixture, delete `generated/metadata` or the integration sandbox install fails on the disabled 2FA module.

## Test inventory

| Repo | Unit | Integration | Other |
|---|---|---|---|
| module-base | 19 tests / 48 assertions | 6 tests / 12 assertions | PHPCS clean, PHPStan 6 clean, LOC guard (408 / 1000 lines) |
| openlabel (after M1 S7) | 117 / 340 | 59 / 238 (+ perf budget on main/nightly) | PHPCS zero errors, PHPStan 6 clean, coverage gate ≥ 85 % (Variable 96.7, Condition 97.7, Resolver 100, Rule 98.3) |
| openlabel-hyva | 4 / 13 | 1 | PHPCS clean, PHPStan 6 clean |
| openlabel-dev-env | — | — | Playwright 20 / 20 (desktop + mobile Chromium) |

## Open questions for Iman

- Docker Hub pull-rate limit on GitHub runners (ExtDN container actions): wait and rerun, or add Docker Hub credentials as secrets so the jobs can run as authenticated job containers?
- Packagist: register `iranimij/module-base` after the `v1.0.0` tag (CI pre-install scripts fall back to a VCS repository until then) and mark the two old packages abandoned (composer.json already carries the field).
- Hyvä portal keys (optional): the fixture and CI use the OSL packages from your copy; with keys the fixture can switch to the portal repository.

## Known failing or skipped tests

- `Test/Integration/Performance/FullReindexTest` is skipped unless `OPENLABEL_PERF=true` (CI sets it on `main` and nightly, not on pull requests; agreed 2026-10-09).
- CI reruns on PRs #5–#9 fail on Docker Hub `toomanyrequests` while pulling the ExtDN action images (infrastructure, not code); PR #4 and PR #10 (all code) are green. (Playwright `configurable-selection-changed` test skips only if the sample-data configurable product `radiant-tee` is missing.)

## Fixture gotchas (see openlabel-dev-env README)

Composer `--no-plugins` in the Warden container; background jobs via `setsid nohup` inside the container; CSP enforce flag set via SQL; OpenSearch image pinned to 2.5.0; admin 2FA and URL secret keys disabled.

## Last CI run

See the links in the Notion build log entry for 2026-10-09 (module-base, openlabel, openlabel-hyva).
