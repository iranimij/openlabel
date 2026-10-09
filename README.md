# OpenLabel: product labels for Magento 2 and Hyvä

**Free. MIT. Hyvä-native. One query per page, zero layout shift, correct under Varnish for every customer group.**

> Status: under construction (milestone M0, foundation). The first release, 1.0.0, is planned for December 2026. Nothing here is usable in a shop yet.

OpenLabel adds rule-based product labels ("Sale -25%", "New", "Only 3 left") to listings and product pages. Labels are matched by a dedicated indexer, rendered server-side inside the cached page, styled by one generated stylesheet per store, and need no JavaScript and no Tailwind rebuild on a stock Hyvä theme.

## Packages

| Package | Purpose |
|---|---|
| `iranimij/openlabel` (this repo) | Core: entities, rule conditions, indexer, resolver, admin, GraphQL, CLI |
| [`iranimij/openlabel-hyva`](https://github.com/iranimij/openlabel-hyva) | Hyvä render package |
| [`iranimij/module-base`](https://github.com/iranimij/module-base) | Shared config tab and helpers. No license checks, no phone-home. |
| `iranimij/openlabel-hyva-bundle` | Metapackage installing all three (1.0.0) |

## Compatibility

| Magento Open Source / Mage-OS / Adobe Commerce | PHP | Hyvä |
|---|---|---|
| 2.4.7, 2.4.8, 2.4.9 | 8.2, 8.3, 8.4 | 1.3.x, 1.4.x |

## Installation (from 1.0.0)

```bash
composer require iranimij/openlabel-hyva-bundle
bin/magento setup:upgrade
bin/magento openlabel:reindex
```

## Development

See [docs/](docs/) for the architecture notes, including [docs/hyva-integration.md](docs/hyva-integration.md), and [PROGRESS.md](PROGRESS.md) for the current state of the build. Contributions follow [CONTRIBUTING.md](CONTRIBUTING.md); issues are triaged within 7 days, there is no SLA.

## Notice

OpenLabel is an independent open-source project. It is not affiliated with, endorsed by, or sponsored by Hyvä Themes B.V. or Adobe Inc. "Hyvä" and "Magento" are trademarks of their respective owners.

## License

MIT. See [LICENSE](LICENSE).
