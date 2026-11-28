# Changelog

## [Unreleased]

## [1.0.14] - 2026-11-28

### Changed

- Raise the minimum PHP version to 8.2 and declare the DOM, libxml, and XSL extensions in Composer.
- Raise the minimum versions of Slothsoft Core, Farah, and Slothsoft W3C to 1.15.0, 1.29.7, and 1.3.0 respectively.
- Add native parameter, return, and property types across the Oracle PHP APIs.
- Add DDEV for local development and include PHPStan in Jenkins validation. Test PHP 8.2 through 8.5 with lowest and stable Composer dependencies.

### Fixed

- Handle missing database and DOM results when building Oracle search, card, and metadata XML.
- Restore the `/data/work` text stream by running Oracle jobs sequentially through Farah's chunk writer.
- Use modern PHP serialization for Oracle and its tables while retaining support for existing serialized payloads.
- Load remote Oracle documents through an HTTP client with caching and preserve the XHTML namespace used by Gatherer queries.
- Exclude `symfony/polyfill-php83` 1.28.x and 1.29.x because their LDAP signatures emit deprecation warnings on PHP 8.4 and 8.5.

### Removed

- Remove the legacy `Deck`, `Player`, `MKM`, `MKMAuthority`, `MKMBooster`, and `MKMShop` classes.
- Remove `Oracle\Set`, `OracleCardTable`, `OracleDeck`, `OraclePlayer`, `OracleReview`, and the `OracleCardImage`, `OracleColorImage`, `OracleRarityImage`, and `OracleSetImage` classes, along with their obsolete factory and URL helpers.
- Remove the incomplete `BigInt` and `RSAKeyPair` implementation and the obsolete Oracle work implementation.

## [1.0.13] - 2026-03-13

### Added

- Add `/api/search` and `/api/search-query` assets for composing the card search page with the static Oracle data and a separate query document.

### Changed

- Rewrite the image cache as a JavaScript module. Group image lookups by URL and use a shared worker to service them.
- Raise the minimum versions of Slothsoft Core and Farah to 1.14.19 and 1.27.1 and use Farah Testing for module and manifest tests.

### Fixed

- Make nullable parameters explicit to avoid deprecation warnings on PHP 8.4 and 8.5.
- Submit the HTML card search form to the current page.

### Removed

- Retire `/api/shoutbox` and remove the `slothsoft/chat` dependency.
- Replace the `/api/ImageCache` wrapper with a direct module link from `/api/default`.

## [1.0.12] - 2025-09-23

### Changed

- Raise the minimum Farah version to 1.21.3.

### Fixed

- Import the graph stylesheet from Farah in the DCI and DCI timeline templates.

## [1.0.11] - 2025-09-20

### Added

- Add GitHub Actions tests for lowest and stable Composer dependencies and Jenkins integration tests on Linux and Windows.

### Changed

- Raise the minimum Farah version to 1.20, move the development suite to PHPUnit 9.6, and stop tracking `composer.lock`.
- Pause tests that require the old pthreads work classes.

### Fixed

- Update the module's DOM and XPath imports and the image cache worker's database script paths to use Farah's assets.

## [1.0.10] - 2025-07-04

### Changed

- Generate and publish the API documentation through GitHub Actions and GitHub Pages. Remove the generated documentation from the repository.
- Refresh the Composer lock file, including Farah 1.14.2 and Slothsoft Core 1.13.0.
- Drop `krakjoe/pthreads-polyfill` from the locked dependency set.

## [1.0.9] - 2024-10-06

### Fixed

- Omit empty text inputs from submitted card search queries.

## [1.0.8] - 2024-10-05

### Fixed

- Lowercase mana color icon URLs in the card search filters so they match the asset names.

## [1.0.7] - 2024-10-05

### Changed

- Update the locked Slothsoft SSE and W3C versions to 1.0.5 and 1.2.5 respectively.

### Fixed

- Lowercase card image filenames, set directories, rarity URLs, and color image paths for case-sensitive lookups.

## [1.0.6] - 2024-10-05

### Added

- Add `/image-card` and `/image-rarity` Farah assets and bundle mana color icons under `/images`.

### Changed

- Raise the minimum PHP version to 7.4 and move the module from PSR-0 to PSR-4 autoloading.
- Add parameter filtering for `search-query` on `/data/oracle`, look up search results from the full XML document, and use Farah asset URLs for the image cache worker.

### Removed

- Unregister the older HTML fragments for decks, players, events, DCI, and other sections alongside the sitemap and player-site manifest entries.

## [1.0.5] - 2024-10-05

### Fixed

- Convert Oracle search query values and stock counts to strings before writing them to XML attributes.
- Clean up PHP warnings in the Oracle, Gatherer, and related helpers.

## [1.0.4] - 2024-09-29

### Changed

- Adopt the MIT license and update Composer's license metadata.
- Refresh locked Slothsoft dependencies, including Core 1.11.6 and Chat 1.0.5.

## [1.0.3] - 2024-09-23

### Changed

- Add a Composer lock file to record the development dependency versions and normalize repository file formatting.

## [1.0.2] - 2024-04-01

### Changed

- Regenerate the API documentation and its deprecation, error, and marker reports.

## [1.0.1] - 2024-04-01

### Changed

- Let Farah assign names to manifest imports, stylesheets, scripts, and templates where no explicit alias is needed.
- Add the module homepage to Composer metadata and derive the package version from Git tags.

## [1.0.0] - 2018-08-10

### Added

- First tagged release of the Magic: The Gathering module for Farah, with card search and Oracle imports, deck and collection tools, event history, and card images.
- Package the module as `slothsoft/mtg` for PHP 7.2 with its Farah asset manifest and PHPUnit tests.

[Unreleased]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.14...HEAD
[1.0.14]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.13...1.0.14
[1.0.13]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.12...1.0.13
[1.0.12]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.11...1.0.12
[1.0.11]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.10...1.0.11
[1.0.10]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.9...1.0.10
[1.0.9]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.8...1.0.9
[1.0.8]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.7...1.0.8
[1.0.7]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.6...1.0.7
[1.0.6]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/Faulo/slothsoft-mtg/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/Faulo/slothsoft-mtg/tree/1.0.0
