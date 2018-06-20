# Planet Alert Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

Actually, I will try to follow and respect the above-mentionned conventions, but as I often remind, I am no profesionnal dev so I might do beginner's mistakes ;) But I'll do my best !

Keep in mind that Planet Alert depends on [ProcessWire CMS](https://processwire.com) by Ryan Cramer.

For easier maintaining of this file, here are the Guiding Principles to keep a god changelog :
- Changelogs are for humans, not machines.
- There should be an entry for every single version.
- The same types of changes should be grouped.
- Versions and sections should be linkable.
- The latest version comes first.
- The release date of each version is displayed.
- Mention whether you follow Semantic Versioning.
- Types of changes : 
	- 'Added' for new features.
	- 'Changed' for changes in existing functionality.
	- 'Deprecated' for soon-to-be removed features.
	- 'Removed' for now removed features.
	- 'Fixed' for any bug fixes.
	- 'Security' in case of vulnerabilities.


## Unreleased
### Added
- Allow multiple teachers

## v1.0.0 - [Unreleased]
### Added
- Better repository management to allow other users to quickly start a Planet Alert instance (and to take part in development ;) )

## [v0.1.2] - 2018-06-20
### Added
- Admin's possibility to save actions (in adminTable) even though a player is ticked 'absent'.

### Fixed
- Redirection after donation didn't work
- Filtering on Visualizer's page had an inconsistent behavior. Buttons are more explicit now and should work as expected.
- Quote syntax error Places page (causing a 500 error)
- Forgotten isset() to avoid a PHP warning

### Changed
- Scoring scale for Motivation is more explicit in global reports

## [v0.1.1] - 2018-06-14
### Changed
- Limit to Fights/Buy/Free actions in Team News thumbnails 
- Limit to UT training in Team News footer
- Add Monster's names in Team News
- More details in Monster invasions automatic answers (city/country)


## [v0.1.0] - 2018-06-10
Initial official release.

### Added
- This CHANGELOG file
- New lessons appear in Recent Public News on Newsboard.
- 'In class' Team option is available for admin. This should be useful to keep track of player's motivation only for out-of-class activity in Reports at the end of a period.
- Memory helmet out-of-class activity appear in Team recent news on Main Office.

### Changed
- See player's stats during Monster invasions. When in-class quizzing, the teacher writes the result in the student's copybook. If 3rd wrong answer is given (during current schoolyear), the item is lost. 


### Fixed
- When 'Reload a question' was clicked during Monster Invasions and the player was the last one, the list of players was displayed and no question appeared. This should be fixed.
- Empty /tmp subtree every night. /tmp stores players having lessons validated by the teacher in class for undo's purpose. Conflicts would appear (and the list would uselessly grow with time) if it wasn't cleaned. And the undo action should be necessary only for a few minutes/hours.


[Unreleased]: https://framagit.org/celfred/planetAlert/compare/v0.1.0...master
[v0.1.0]: https://framagit.org/celfred/planetAlert/tags/v0.1.0
[v0.1.1]: https://framagit.org/celfred/planetAlert/tags/v0.1.1
[v0.1.1]: https://framagit.org/celfred/planetAlert/tags/v0.1.2
