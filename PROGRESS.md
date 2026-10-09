# PROGRESS

Fine-grained checkpoint for the OpenLabel build. Updated with every merged PR. The Notion build log (07 · Build Brief, section 7) holds the per-milestone summary.

## Current milestone

M0 · Foundation: **done** (2026-10-09). Next: M1 · Engine (entities, conditions, indexer, resolver, CLI), target 2026-11-09.

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

M1 S1: `openlabel` db_schema.xml (+ whitelist) for `openlabel_label`, `openlabel_design`, `openlabel_design_store`, `openlabel_placement`, `openlabel_index` per the entity spec pages; `Api/Data` interfaces; repositories with integration tests. Work on a feature branch, PR to `main` (branch protection requires `ci-ok`).

## Test inventory (M0)

| Repo | Unit | Integration | Other |
|---|---|---|---|
| module-base | 19 tests / 48 assertions | 6 tests / 12 assertions | PHPCS clean, PHPStan 6 clean, LOC guard (408 / 1000 lines) |
| openlabel | 3 / 21 | 3 (with hyva: 4 / 10) | PHPCS clean, PHPStan 6 clean |
| openlabel-hyva | 4 / 13 | 1 | PHPCS clean, PHPStan 6 clean |
| openlabel-dev-env | — | — | Playwright 20 / 20 (desktop + mobile Chromium) |

## Open questions for Iman

- Packagist: register `iranimij/module-base` after the `v1.0.0` tag (CI pre-install scripts fall back to a VCS repository until then) and mark the two old packages abandoned (composer.json already carries the field).
- Hyvä portal keys (optional): the fixture and CI use the OSL packages from your copy; with keys the fixture can switch to the portal repository.

## Known failing or skipped tests

None. (Playwright `configurable-selection-changed` test skips only if the sample-data configurable product `radiant-tee` is missing.)

## Fixture gotchas (see openlabel-dev-env README)

Composer `--no-plugins` in the Warden container; background jobs via `setsid nohup` inside the container; CSP enforce flag set via SQL; OpenSearch image pinned to 2.5.0; admin 2FA and URL secret keys disabled.

## Last CI run

See the links in the Notion build log entry for 2026-10-09 (module-base, openlabel, openlabel-hyva).
