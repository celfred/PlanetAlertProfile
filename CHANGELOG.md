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


## v1.0.0 - [Unreleased]
- Better repository management to allow other users to quickly start a Planet Alert instance (and to take part in development ;) )


## [v0.5.0] - [Unreleased]
Teacher's announcements are now possible

### Backend
- New announcement template (teachers can edit and delete their own page)
- New fields : selectPlayers (checkbox), playersList (Page)
- Changes : teachers can add children to team template (with announcement template)

### Added
- Teacher's announcements : the Teacher zone allows a teacher to see his or her announcements, add new announcements to either a team or individual players

### Changed
- Alert messages now have a thin border to be more visible
- CHANGELOG offers only 'major' versions links to avoid clutter : second parameter in version number corresponds to versions causing backend changes


## [v0.4.4] - [11/09/2018]
### Changed
- Donations influence karma only once a day : this is to prevent players from making many 1GC donations in a row only to get ahead of other players


## [v0.4.3] - [11/05/2018]
### Added
- Basic anti-spam for guest contact form

### Changed
- Contact form validation is now managed with basic HTML5 tools

### Fixed
- Guests had a double 'Contact' entry in menu


## [v0.4.2] - [11/05/2018]
UT report was fixed and slightly modified.

### Fixed
- Team UT and monster UT were wrong in UT report

### Added
- Total UT over the selected period is now indicated for each player


## [v0.4.1] - [11/03/2018]
### Fixed
- Wrong Planet Alert tag in cleanTag function
- Forgotten release date in CHANGELOG for v0.4.0
- Forgotten v0.4.0 link in CHANGELOG


## [v0.4.0] - [11/02/2018]
Google maps is replaced with Open Street Map :) And a new 'categorize' exercise type appears along with Planet Alert basic tags support.

### Backend
- FieldtypeLeafletMapMarker is now required
- mapMarker field is replaced with map field (Leaflet Inputfield)
- Add categorize exercise type

### Added
- Failed logins stats over the last 7 days
- Last 30 days training stats
- New exercise type : Categorize
- Planet Alert basic tags support in exercises : _string_ is underlined, *string* is framed, \string\ is italicized

### Changed
- Statistics display only unique visitors (more readable)
- Global Statistics are reserved for admin

### Removed
- Google API is no longer needed for maps


## [v0.3.0] - [2018-10-21]

Reports are now available for teachers. Statsistics as well. And as usual, a few bugfixes.

### Added
- 'Reports' menu for teachers now gives access to all reports
- New report type : category report
- Statistics access for teachers
- Contextual statistics according to logged-in teacher

### Changed
- Admin links (reports, stats, users/history) open in new tab
- Reputation calculation now avoids HP points
- 'Delete' button is back when checking history : faster removing of linked actions (ie, death)
- Team and individual reports are available for all types
- HTML5 datepicker for custom dates

### Fixed
- Remove teachers from statistics
- Show logged player's work statistics
- Recalculate hkCount after updating official period (limited to myself because it is a personal functionality)
- Death bugs : reputation calculation, when no group were set, everyone suffered a 'group member died'
- Detecting low HP players on Newsboard

### Removed
- Admin options that are now included in reports
- Clean no longer needed files


## [v0.2.2] - 2018-10-08

### Changed
- Refactor exercises scoring : 1 file included to share progress bars

### Fixed
- Fights when player had no weapons or no protections : progress bars emptied at once and fight could not be ended
- ImageMap not displayed in training
- Messed-up word list because of quotes (fixed in v0.2.1 but forgotten on Memory helmet page)
- Marketplace bug : calculate left GC after purchase and update possible items
- Monsters list depending on head teacher's monsters
- Recent news display : sorting issue solved so a teacher can see his or her players
- Teacher's mail notification after training or fight
- Typo in inactivity number of days limit


## [v0.2.1] - 2018-09-29

A lot of minor bug fixes, but also new CM1 report type, and new small adjustements for a better user experience : more teacher's management possibilities, easier contact form for players... Details below. Maybe the lists are a little long. I should commit minor versions more often for better readability :) 

### Added
- CM1 report type
- Planet Alert News in 'recent activity' on Newsboard : list of less-than-30-days-old place/people/lesson/equipment/exercise for information. The list is contextual : a player sees his head teacher's new items. A teacher sees his own news and admin news, admin sees everything. The list is not displayed if empty and is limited to 10 elements, date is added...
- IE users : warning message, no training, no fights, no marketplace, random seed for Ajax GET request : Not the best practice for IE users, but Planet Alert is only tested with Firefox for the moment (Help needed !).
- Team name and scores are now visible in tabList
- Pending serious injuries are now displayed at the top of team list
- 'My Actions' menu : 'Contact my teacher' for logged-in player
- Hours are added to periods management
- Teacher's possibility to test fighting zone, underground training zone and visualizer page in mosters' list and in monster's management
- Teacher's access to his or her users list (usersTable) with more possibilities : last visit date, quic edit link, access to profile page, Javascript interactivity...
- No-team players switch button for users list for my own teacher account
- Add 'rank' to quickly edit team/rank for admin in users list
- Quick edit link for teachers in player's history
- Javascript interactivity for player's marketplace
- Automatically delete no-team players/users after 1 year of inactivity out of middle-school (rank > 9)
- Teachers can manage his or her own monsters (exercises)
- Teachers can manage his or her topics 
- French PDF files
- Add teacher's access to 'test' player's profile page
- Add JS french translation

### Changed
- Mail notifications (for teachers and admin) : more readable, use the WireMail() class
- Contact form now has JS user confirmation before sending
- Donation is possible to any player belonging to headTeacher's teams, and not limited to player's team
- New elements in recent public news : replace new monsters / New lessons indications and add places, people, equipments to the list, contextual to the teacher's teams, better display
- Hide clues for very small words during training (sunglasses showing jumble letters)
- Limit players scores to head teacher's teams
- Avoid reloading after front editing players/users for faster edition
- Code : Optimize public news request
- Code : Change 'karma' to 'reputation' to avoid confusion with yearlyKarma
- Design : Tooltip CSS (clear background)
- Design : French version icons
- Design : Monster fights design
- Design : Add quick scroll arrows for teachers (used to be only for admin)

### Fixed
- Monsters list was messed up because of quotes in Quiz data
- Hidden monsters were still hidden even though force visualizer was used
- Monsters list : restricted to logged in teacher (or head teacher for players)
- Forgotten translations
- Wrong task name for penalties
- Global reports fatal error because of namespace
- Training monsters list limited to logged teacher
- Imagemap exercise type (the image was not displayed)
- Variable name for team->freeworld (issue due to wikiCase spelling)
- Wrong character encoding during monster invasions (workaround with a sentence avoiding apostrohes)
- Exercises feedback
- Setting fightable monsters
- Recalculate action based on a non-empty yearly-karma during player initialization
- Display of remaining GC when buying a PDF
- Statistics url in admin's menu
- Scoreboard when no groups are set
- Selecting or deselecting a task or a period did not work correctly for the teacher
- Progress bars width required rounding resulting value
- setYearlyKarma()
- checkStreak() to avoid counting inactivity
- checkActivity()
- Guest header menu icons
- Potion planner : old unused potions are displayed, forgotten translation, cleaner display
- Add last name indication for no team players in Teacher's work
- Anonymize empty PDF for new players
- PDF access 
- Inactivity was displayed as a negative task in team list
- Reset streak when inactivity is recorded
- History display was wrong after recalculating
- Fix 'Manage tasks' display
- Fix adding new users' team
- Save fights/trainings only for logged-in players

### Removed
- Useless files remaining from previous dev


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
[v0.2.0]: https://framagit.org/celfred/planetAlert/tags/v0.2.0
[v0.3.0]: https://framagit.org/celfred/planetAlert/tags/v0.3.0
[v0.4.0]: https://framagit.org/celfred/planetAlert/tags/v0.4.0
[v0.5.0]: https://framagit.org/celfred/planetAlert/tags/v0.5.0
