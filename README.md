# Academic Dashboard (Esse3)

`block_academic_dashboard_esse3` is a Moodle block that gives users a focused academic entry point inside Moodle by connecting the platform to CINECA ESSE3 and, when needed, to Moodle's own course enrolment data.

The plugin is designed to reduce navigation noise. Instead of asking users to browse categories, dashboards, and course listings manually, it shows the courses that matter to them in a compact academic view.

## What this plugin is for

In many university Moodle installations, students and teachers work in a large number of courses spread across different categories, academic years, and organizational structures. That can make the platform harder to use than it needs to be.

This block addresses that problem in two complementary ways:

- For students, it surfaces the academic record and study-plan context coming from ESSE3 and links it to Moodle courses.
- For teachers, or for users without a configured ESSE3 user ID, it provides a cleaner view of the Moodle courses they are enrolled in, grouped according to the plugin settings.

The result is an academic dashboard that helps users reach the right learning spaces faster and with less confusion.

## What are CINECA and ESSE3?

### CINECA

CINECA is an Italian inter-university consortium that provides digital infrastructure, computing services, and information systems for universities, research institutions, and public bodies. According to CINECA's official history, it was founded in 1967 as a shared computing center for universities and later expanded its mission to include administrative and management systems for higher education.

### ESSE3

ESSE3 is CINECA's student information system for universities. On its official solution page, CINECA describes ESSE3 as a system used by most Italian universities to manage the student lifecycle, including admissions, enrolment, student careers, exams, graduation, and course-related data. In practical terms, ESSE3 is often the authoritative source for student academic careers and related teaching information in Italian universities.

This plugin does not replace ESSE3. It uses ESSE3 as an academic data source and presents relevant information directly inside Moodle.

## How the plugin works

### Student transcript mode

If the logged-in user has a configured ESSE3 user ID value, the block:

1. reads the ESSE3 user ID from a standard Moodle user field or a custom profile field;
2. calls the ESSE3 REST API;
3. retrieves careers and transcript items;
4. enriches those items with teacher and partition data when available;
5. tries to match each academic activity to a Moodle course;
6. renders the result as an academic dashboard inside Moodle.

When transcript data is available, the block can also merge additional Moodle courses that the user is enrolled in but that are not part of the retrieved transcript, so they remain visible in a separate section of the same view.

### Enrolled-course fallback mode

If the user does not have an ESSE3 user ID configured, the block falls back to Moodle enrolments and builds the dashboard from the courses the user is enrolled in.

This is especially useful for teachers, because it lets them see their teaching spaces in a curated block instead of navigating the full Moodle structure.

### Empty transcript case

If an ESSE3 user ID is present but ESSE3 does not return transcript data, the block falls back to the enrolled-course view.

## Main features

- ESSE3 transcript integration through the ESSE3 REST API.
- Moodle course matching based on course `idnumber`.
- Direct course actions such as `Go to course` and, when applicable, `Enrol`.
- Search and filtering in transcript mode.
- Grid and list layouts for transcript cards.
- Syllabus modal loaded through a Moodle external function and AMD module.
- Enrolled-course fallback for users without an ESSE3 user ID or without ESSE3 careers.
- Configurable grouping and sorting for enrolled Moodle courses.
- Support for custom Moodle user profile fields as ESSE3 user ID source.
- Accent color setting for visual customization.

## Course matching

The plugin tries to connect ESSE3 activities to Moodle courses through the Moodle course `idnumber`.

The matching logic uses the ESSE3 academic identifiers and looks for Moodle courses whose `idnumber` starts with:

```text
<cdsId>-<adId>-<aaFreqId>
```

Example:

```text
12345-67890-2025
```

When a matching course is found, the block can expose:

- a direct link to the Moodle course;
- an enrolment link if the user is not already enrolled;
- teacher information from Moodle when the ESSE3 item does not already provide it.

## Data flow summary

- User logs in to Moodle.
- The block resolves the configured Moodle user field used as ESSE3 `userId`.
- If an ESSE3 user ID is available, the block requests career data from ESSE3.
- Transcript items are cached through Moodle MUC.
- Each transcript item is mapped to a Moodle card model.
- Moodle courses are matched through `idnumber`.
- Extra Moodle enrolments can be merged into the transcript view.
- If no ESSE3 user ID or ESSE3 career is available, the block builds the view from Moodle enrolments only.

## Configuration

After installation, configure the plugin in:

`Site administration > Plugins > Blocks > Academic Dashboard (Esse3)`

Available settings include:

| Setting | Purpose |
| --- | --- |
| `apiurl` | Base URL of the ESSE3 REST API |
| `apitoken` | Basic authentication token used for API calls |
| `userfield` | Moodle user field sent to ESSE3 as `userId`, including supported custom profile fields |
| `rootcategories` | Optional list of root category IDs allowed in fallback mode |
| `groupdepth` | First grouping level for enrolled-course fallback |
| `secondarygroupdepth` | Optional second grouping level for enrolled-course fallback |
| `sortcategorydepth` | Optional category depth used for sorting |
| `sortdirection` | Ascending or descending sorting |
| `groupmode` | Grouping strategy when depth-based grouping is not used |
| `secondarygroupmode` | Optional secondary grouping strategy |
| `accentcolor` | Primary accent color used in the block UI |

## Requirements

- A Moodle version compatible with the `requires` value declared in `version.php`
- Network access from Moodle to the ESSE3 REST API
- A valid ESSE3 API URL and token
- A Moodle user field containing the ESSE3 user ID, usually `username`
- Moodle courses with `idnumber` values aligned with the institution's ESSE3 mapping strategy

## Installation

1. Copy the `academic_dashboard_esse3` directory into `blocks/`.
2. Visit `Site administration > Notifications`.
3. Complete the Moodle upgrade.
4. Add the block to the Dashboard or Site home.

## Supported pages

The block can be added to:

- Dashboard (`my`)
- Site home (`site-index`)

It is not intended for standard course pages, activity pages, or admin pages.

## Technical notes

- Component name: `block_academic_dashboard_esse3`
- Main block class: `block_academic_dashboard_esse3`
- Transcript data is cached through Moodle MUC
- Mustache templates are used for transcript and enrolled-course rendering
- AMD modules are used for transcript filters, enrolled-course search, and syllabus loading
- The plugin supports both standard Moodle user fields and custom profile fields for the ESSE3 user ID lookup

## Why this plugin matters

This plugin is useful because Moodle and a university student information system usually solve different problems:

- Moodle is the learning platform.
- ESSE3 is the academic and administrative system of record.

Users often need both views at the same time. Students need to understand which Moodle courses are relevant to their academic career. Teachers need quick access to the teaching spaces they manage. This block brings those contexts closer together and turns Moodle into a more usable academic entry point.

## External references

The following official CINECA pages were used to write this overview:

- CINECA, "ESSE3 Student management system": <https://academy.cineca.it/en/solutions/esse3-student-management-system>
- CINECA, "Our history": <https://www.cineca.it/en/about-us/our-history>

## License

GNU GPL v3 or later

## Credits

Developed by Università degli Studi di Ferrara - Unife.
