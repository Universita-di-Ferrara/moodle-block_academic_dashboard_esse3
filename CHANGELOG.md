# Changelog

## 1.0.6 - 2026-06-03

### Added

- Added an ESSE3 user status cache to avoid repeated career lookups for users without active student careers.

### Changed

- Non-student users now skip ESSE3 transcript calls while the cached status is valid and go directly to the enrolled-courses fallback.
- ESSE3 career lookup failures are no longer treated as valid empty career responses.

## 1.0.5 - 2026-06-03

### Added

- Added a Moodle application cache for resolved course teacher names.

### Changed

- Optimized transcript-to-Moodle course matching by batching Moodle course lookups into a single query per render.
- Reused course teacher values within the same request and across requests through MUC caching.

## 1.0.4 - 2026-06-03

### Changed

- Changed ESSE3 career lookup from matricola-based search to `userId`-based search.
- The ESSE3 `userId` value is resolved from the configurable Moodle user field setting `userfield`.
- Updated the career endpoint to `carriere-service-v1/carriere?userId=...`.
- Updated syllabus career validation to use the same configured ESSE3 `userId` flow.
- Updated privacy metadata and documentation references from matricola to ESSE3 `userId`.

### Fixed

- Removed temporary debug logging from ESSE3 career and transcript fetching.
- Fixed the ESSE3 privacy metadata key for the external user identifier.
