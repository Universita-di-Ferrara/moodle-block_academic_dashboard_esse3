# Changelog

## 1.0.8 - 2026-06-09

### Security

- Added an explicit `block/academic_dashboard_esse3:view` capability for plugin web service access.
- Added a capability check to the syllabus external service after context validation.
- Declared the required capability in `db/services.php` so external service configuration reflects the access requirement.

## 1.0.7 - 2026-06-09

### Added

- Added a configurable student email domain setting to identify student users before contacting ESSE3.
- Added a configurable student matricola field setting used to resolve the Moodle user value sent to ESSE3.
- Added a fallback to the enrolled-courses view for students without a matricola or without active ESSE3 careers.

### Changed

- Switched ESSE3 career retrieval from the slow `carriere-service-v1/carriere?userId=...` endpoint to `libretto-service-v2/libretti?matricola=...`.
- Non-student users are now routed directly to the enrolled-courses view, avoiding unnecessary ESSE3 calls.
- Extended user-status and course-teacher cache TTLs to 24 hours to reduce repeated Moodle and ESSE3 lookups.
- Updated syllabus career validation to use the same matricola-based ESSE3 career lookup as the dashboard.

### Fixed

- Avoided treating students without active careers as errors by showing their enrolled Moodle courses instead.

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
