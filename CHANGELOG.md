# Changelog

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
