# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- M1 S1 · Engine data layer: declarative schema for `openlabel_label`, `openlabel_design`, `openlabel_design_store`, `openlabel_placement`, `openlabel_index` (+ replica); `Api/Data` interfaces for Label, Design (store-scoped text, alt text, tooltip) and Placement; `LabelRepositoryInterface` and `DesignRepositoryInterface` (placements and store texts are saved with their parent); validators with actionable messages; ACL resources `Iranimij_OpenLabel::openlabel`, `::labels`, `::designs`, `::custom_css`; uninstall class. Index rows use `customer_group_id = -1` for "all groups".
- Module skeleton `Iranimij_OpenLabel` (M0): system configuration section `openlabel` under the Iranimij tab with `general/enabled` (default on), ACL resources, translations, CI template (PHPCS, PHPStan level 6, unit and integration matrix Magento 2.4.7/2.4.8/2.4.9 × PHP 8.2/8.3/8.4).
- `docs/hyva-integration.md`: verified Hyvä 1.4.2 hook points, events and container-query rules.
