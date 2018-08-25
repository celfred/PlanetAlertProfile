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
	- 'Backend' for changes in backend fields/templates


## Unreleased
### Added
- Allow multiple teachers

## v1.0.0 - [Unreleased]

### Added
- Better repository management to allow other users to quickly start a Planet Alert instance (and to take part in development ;) )

## [v0.2.1] - Unreleased

### Added
- Planet Alert News in 'recent activity' on Newsboard : list of less-than-30-days-old place/people/lesson/equipment/exercise for information. The list is contextual : a player sees his head teacher's new items. A teacher sees his own news and admin news, admin sees everything. The list is not displayed if empty and is limited to 10 elements.


## [v0.2.0] - 2018-08-19

Major update.

### Added
- Multi-teacher access : profiles now exist. Teacher, player, and admin roles are now separate. This triggered many changes : new adminActions through Teacher Zone or Admin Zone, new restrictions because a teacher can choose his or her items / actions / periods / tasks...
- Multi-language site : default is English, but French is available as well. The main teacher decides for his or her players. More translations could be added quite easily through PW tools.
- Versioning is now based on 'second number reflects back-end changes', i.e. in v0.2.0, the 2 illustrates the fact that back-end has heavily changed from v.0.1.5. 
- Version number in footer

### Changed
- No team players now have a paginated team list and personal free world statistics to face the 350 users loading issue. It's a first draft.
- Official periods is now integrated in 'team' template and is set by team main teacher
- Ranks are now index based to deal with all schools levels (primary through high-school)
- Planet Alert internal tasks are now indicated by the adminOnly checkbox
- New back-end modules : see below
- Memory potions texts are managed differently : no more need of index field

### Backend (Many changes)
- New fields : owner, periodOwner, textOwner (to operate with teachers variations), teacher, teacherTitle, singleTeacher, memoryPotionTexts, instructions, version...
- New templates : tasks, categories, topics, groups,... (to operate with new teachers permissions), teacherProfile
- New roles : teacher, player
- Adapted permissions
- Multi-language fieldtypes for title, summary, body, answer, question, instructions
- New module : FrontEndEditLightbox : used to simplify teacher (and admin) front-end edit
- New module : LimitRepeater to restrict teacher edit capabilities in repeater fields
- Install Languages Support modules

### Removed
- frenchSummary field : now included through multi-language fieldtypes
- index field from memory-text template

### Security
- More restrictive file and folder access rights (in config.php)


## [v0.1.5] - 2018-07-15

### Added
- tmp Page for players : Major update to manage trainings and fights activity. Each player has a child tmp page which is updated on every monster activity. This page is then used to display UT scores, fights scores, last dates... Loading time should be improved since no need to recalculate everything from player's complete history.
- New tmp page is accessible for Admin on player's profile page
- Admin action to recalculate all tmp pages in a team
- mapIndex now is written on Places PDFs
- Limit archiving to 'History' pages (since new tmp page is a sibling)

### Changed
- Order of elements on player's profile page

### Backend
- New Template : tmp
	- title
	- tmpMonstersActivity
		- monster (new Page)
		- inUt (new Integer)
		- outUt (new Integer)
		- trainNb (new Integer)
		- fightNb (new Integer)
		- lastFightDate (new Datetime)
		- lastTrainDate (new Datetime)
		- quality (new Float)
		- date (not new)
	- index (not new)
- New Places added
- Updated worldmap with Places numbers in download area


## [v0.1.4] - 2018-07-05

### Added
- Memory potion : Players can buy a short text to memorize
- Possibility to set no official period
- Usabled items now appear in player's profile
- Book of Knowledge : PDF download link for admin
- Book of Knowledge : Add 'Back to Book' button in lessons
- Possibility to generate an empty PDF from player's profile (to put in copybook)

### Fixed
- Set highscores (Fights were taken into account along with UT, in-class training were ignored)
- utGain() included fight results
- Most active player scoreboard
- Forgotten nl2br() in summaries
- 'Archive' team option now takes care of people, streak, usabledItems, skills, yearlyKarma
- Newsboard players having an empty rank
- Visualizer not taken into account in Fighting Zone

### Changed
- pendingLessons field becomes pending
- PDF design (for copybook)
- PDF : Load each page separately to avoid server time-out
- Delete option in adminActions now restore a usabled item or directly remove a deleted item
- no-team players are ignored in setCaptains() (This was useless)
- Framagit link instead of Github on Home page 

### Removed
- Work statistics from Newsboard for no-team players
- Work statistics from Newsboard when no official period is set


## [v0.1.3] - 2018-06-24

### Added
- Monster Attacks report (battles) is now displayed in player's profile : this allows to separate Monster Fights (thanks to the Memory Helmet) and regular in-class test results (named 'battles')
- Logged player's mini-Profile is displayed on Newsboard (in place of PMA, see below) 

### Changed
- Scoreboards order on Newsboard
- Improve loading time
- Decision menu for picked player offers more options (The idea is to increase interactivity at the beginning of lessons, in class) :
	- Organize team defense
	- Play for a random discount (cheaper place, equipment...)
	- Go to the Marketplace.
	- Make a donation (help another player).
	- See team's Freeworld.
	- See team's scoring table.
	- Pick a random player in the team.
	- Read about a random element.
	- Visit the Hall of Fame.

### Removed
- Personal Mission Analyzer (PMA) is removed for the moment to improve loading time.

### Fixed
- Greatest # of Places scoreboard was is now correctly sorted
- Wrong stats in UT and Fight reports when 'Force Visualizer' is used
- Hide login form if user is already logged in and loginform page is displayed


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
[v0.1.2]: https://framagit.org/celfred/planetAlert/tags/v0.1.2
[v0.1.3]: https://framagit.org/celfred/planetAlert/tags/v0.1.3
[v0.1.4]: https://framagit.org/celfred/planetAlert/tags/v0.1.4
[v0.1.5]: https://framagit.org/celfred/planetAlert/tags/v0.1.5
[v0.2.0]: https://framagit.org/celfred/planetAlert/tags/v0.2.0
