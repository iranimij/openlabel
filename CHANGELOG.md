# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- M1 S1 · Engine data layer: declarative schema for `openlabel_label`, `openlabel_design`, `openlabel_design_store`, `openlabel_placement`, `openlabel_index` (+ replica); `Api/Data` interfaces for Label, Design (store-scoped text, alt text, tooltip) and Placement; `LabelRepositoryInterface` and `DesignRepositoryInterface` (placements and store texts are saved with their parent); validators with actionable messages; ACL resources `Iranimij_OpenLabel::openlabel`, `::labels`, `::designs`, `::custom_css`; uninstall class. Index rows use `customer_group_id = -1` for "all groups".
- M1 S2 · Rule engine: `Model\Rule\Rule` (condition tree evaluated in SQL through the core `Sql\Builder`), `Combine` and `Product` conditions (native product attributes incl. store-scoped ones, via the catalog widget condition), built-in conditions On sale (on_sale, discount %, discount amount from the price index per website and customer group), Is new (news dates, days since created/updated), Stock (salable and salable quantity, MSI with legacy fallback), Price range (final/regular price), Rating (stars) and Review count, plus a DI `ConditionPool` for custom conditions.
- M1 S3 · Indexer `openlabel_product` (mview, depends on price and stock indexers): full rebuild into `openlabel_index_replica` with an atomic table swap and a lock, partial rebuilds for changed products and their configurable/grouped/bundle parents, one row per customer group for price labels, parent rows for `apply_to_parent` labels, label reindex on save with cache cleaning bounded by the diff, plugins on the price, stock and review updates, hourly window-transition cron and daily is-new cron.
- Module skeleton `Iranimij_OpenLabel` (M0): system configuration section `openlabel` under the Iranimij tab with `general/enabled` (default on), ACL resources, translations, CI template (PHPCS, PHPStan level 6, unit and integration matrix Magento 2.4.7/2.4.8/2.4.9 × PHP 8.2/8.3/8.4).
- `docs/hyva-integration.md`: verified Hyvä 1.4.2 hook points, events and container-query rules.
