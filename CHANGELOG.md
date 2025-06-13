# Changelog

## 4.1.1 - 2025-06-13
- Removes Site Tags from getting merged into IdentifyStaleMedia job, which caused media from outside of the selected show to get updated.

## 4.1.0 - 2025-06-11
- Merges the KB, GA, and Food Media Manager plugins back into one repository. 

## 4.0.6 - 2025-04-08
- fix type error when creating a scheduled sync that had empty Media Fields or Show Fields.
- add ability to delete scheduled syncs

## 4.0.5 - 2025-04-08
- fix issue where media would fail to sync if a "site" was not found when syncing Site tags

## 4.0.4 - 2025-03-11
- add support for entrified tags to prep for Craft 5

## 4.0.3 - 2024-10-08
- change show entries sync job to always sync long and short descriptions

## 4.0.2 - 2024-10-01
- add ability to select what show entries to sync
- fix URL issue on control panel Shows template
- fix issue that was incorrectly tagging shows as being available to public (FVOD)

## 4.0.1 - 2024-07-19
- Fix control panel Synchronize action requests, which were not properly sending form data to controller

## 4.0.0 - 2024-03-22

- Stable Craft 4 release

### Fixed

- Entries not getting correct status when no availabilities are present

## 4.0.0-beta.1 - 2024-02-05

- adds support for Craft 4
- update control panel template button listener JS functionality
- add support for alternative rich text plugins, namely CK Editor

## 3.3.1 - 2024-02-01

### Added

- `mediamanager/schedule/run` console command to run the scheduled sync jobs.

## 3.3.0 - 2024-01-12

### Added

- You can now select what fields should be updated during a sync.
- Added more API attributes to the list of fields that can be synchronized to Show entries.
- Show Entry sync jobs can now sync the Passport availability and public availability flags.
- The plugin now emits its own log files for better debugging.

### Changed

- **New** Media entries and Show entries added during a sync will always synchronize all fields, regardless of what fields were selected for sync.
- The way a show's "Episode Count" property is determined now looks at a show's asset count rather than the static "Episode Count" property on the Show record.

## 3.2.0 - 2023-12-20

### Changed
- The plugin settings now say "Content ID" instead of "API key" for show IDs when creating new shows to sync.

### Added
- The plugin will now check whether a media entry should be 'marked for deletion' based on its presence in the API calls.
- Show synchronization jobs can now be scheduled to run at a specific time.

## 3.1.1 - 2021-06-03

- Fix episode not being populated.
- Introduce show synchronize.

## 3.0.0 - 2020-06-10

- Initial release.

