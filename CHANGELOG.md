# Changelog

## [Unreleased]

## [1.0.14] - 2026-11-28

### Fixed

- Use native PHP types for Oracle APIs and handle missing database and DOM results.
- Restore the `/data/work` text stream on PHP 8.2 by running Oracle jobs sequentially through Farah's chunk writer.
- Preserve Oracle and table serialization, including existing serialized payloads.
- Load remote Oracle documents with HTTP caching and preserve the XHTML namespace used by Gatherer queries.
- Exclude `symfony/polyfill-php83` 1.28.x and 1.29.x because their LDAP signatures emit deprecation warnings on PHP 8.4 and 8.5.

### Removed

- Remove unused legacy PHP helpers for decks, players, Magiccardmarket, and card, image, and review handling, along with the incomplete RSA implementation.

[Unreleased]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.14...HEAD
[1.0.14]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.13...1.0.14
