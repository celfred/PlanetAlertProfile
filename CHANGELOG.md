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


## [v1.1.0] - [05/10/2019]
Quite a while since the last update... So many things to add at once. Sorry... 

[Added]
Add Top trained skill for most trained players
Add direct access to team options for teachers
Add link to details and map in showInfo() for Places
Add teacher's donations possibility
Add teacher's access to a particular player's training zone
Add focus when teacher clicks on a topic while managing monsters
Add date on Category reports
Add link to enlarge photo and see attributions on Places details
Add world map on player's PDF pages
Add new page if long list of categories in monster fights
Add edit lesson access for teachers
Add teacher's possibility to copy an exercise when managing monsters
Add in-class restriction in Fight reports
Add link to external avatar's website when transformation potion has been bought
Add teacher's possibility to manually add a fight request from Newsboard
Add free rate info in Main Office
Add free rate info when teacher accesses a player's marketplace
Add cache for People pages
Add Challenges functionality for Memory helmet
Add CSS rules for phone display
Add fight lock in menu when locked by teacher
Add admin option to clear all markup cache at once
Add possibility to quickly add group item to all group members

[Fixed]
Fix training sessions sorting
Fix boolean test when submitting a training session
Fix duplicate recordings of UT sessions in some cases
Fix PHP warning when checking deletion of old players
Fix donation submit form (bug introduced with teachers' donations)
Fix setTeamSkill() call when saving adminTable
Fix inactive top-trained or master player's deletion : Kepp old players having records.
Fix double menu entry
Fix checkStreak()
Fix inactivity checking and cleaning of out-of-middle school inactive players
Fix battle report (limited to current school year)
Fix name detection when importing from SACoche : if a player is not found, look for last name only.
Fix visualizer detection when forced
Fix teacher's access to a player's Training Zone
Fix Memory helmet direct link from player's equipment list
Fix parameters in UT queries
Fix group item status when checking player's history
Fix lastEvent()
Fix mail notification display
Fix sorting on current period participation page
Fix yearlyKarma initialization
Fix recalculating bug which was based on the wrong history page (when several)
Fix monster's access
Fix empty history bug
Fix checkInactivity()
Fix admin announcements' visibility for teachers
Fix cache cleaning after manually editing history
Fix previous invasions indicators while quizzing
Fix player's last name was displayed in donation
Fix list of players needing help on logged in player's Newsboard
Fix fighting zone possibilities
Fix last fight date indication in player's profile after a Fight Request
Fix free progressbar status
Fix available fights in fighting zone
Fix setting fighter role after 10 fights
Fix SACoche import
Fix Reputation recalculation
Fix quick access to player's marketplace from decision menu
Fix caching issues
Fix scores update
Fix PHP warnings
Fix typos

[Changed]
Change Memory helmet catalogue display for a better user experience
Change Monster Invasions : more map quiz, more complete answers Remove city menu in 'All places' list
Change Place/People page appearance
Adapt Place/People PDF (larger mapIndex box)
Change map provider for higher contrast when quizzing in class
Change team cache expiring date
Override player's rank if team's rank exists
Change all 'Login' to 'Username' references
Limit all monsters list to logged in teacher in reports
Show french summary if english summary is empty when managing monsters
Rename markup cache files for easier cleaning
Allow another fight request if not perfect (VV) on a preceding fight request
Paginate users list for admin to avoid memory limit
Change Ajax saving for some team options
Change group team options for checking skills : hence the new setTeamSkill() function.
Update alt attributes for better accessibility
Update README
Update CHANGELOG

[Remove]
Remove player's last name indication in potion planner (except for no-team players)
Remove Electronic visualizer from menu (simple detector functionality for Memory helmet)
Remove Electronic visualizer link from player's equipment list
Remove Training Zone access from Info monsters page
Remove inactivity checking for players having a team
Remove mapIndex indication

[Backend]
Backend modifications for places management (no more country/city subtree)
Add corrupted identity action
Add top-trained skill for most trained players


## [v1.0.0] - [04/03/2019]
Make Planet Alert site profile available for ProcessWire users. They should be able to easily start a Planet Alert website from a blank ProcessWire installation. The other important change with this version is to try and use a lot more of PW cache's options in order to improve wite performances. There are also some minor bug fixes.

[Added]
- Planet Alert profile ZIP file is available in dist/ folder : this should allow easier sharing for people interested in installing a Planet Alert website. Note that version numbers now should indicate how safe it is to update : middle number should reflect a backend change while last minor number should cause no problem by simply updating templates folder
- Cache management for many pages : template cache for guests, markup cache and session cache for logged-in users : There's still a lot of room for improvement, but this is an 'official' start :)

[Changed]
- Newsboard : Sort teacher's work by team names
- Header menu does not highlight any more (because of cache)
- Update .gitignore

[Fixed]
- Period sorting in team options
- Streaks calculation (called twice for some actions)
- Fix meta and img tags
- Clue tooltip was not updated during training session
- Categorize exercise : correct answer after a wrong answer doesn't increment word counter any longer but it shows the correct answer (this was the expected behavior)
- Wrong image size for monsters PDF files
- Out of class activity count was not corect in Reports

[Backend]
- Use cache options for many pages (for guests)


## [v0.8.0] - [21/02/2019]
Major code refactoring and minor bug fixes.

[Fixed]
- Avoid losing Master role when setting a new best time

[Changed]
- Scoreboards are contextual to logged-in player
- Player can see his or her position in team or gloabl scoreboards
- Monster tables show less information
- Remove free rate information for no-team players
- Paginate no-team players
- Load only last 30 days history in player's profile (and a button allows to load complete history)

[Backend]
- New Integer field bestTrainedPlayerId (replacing mostTrained page filed)
- New Integer field bestTimePlayerId
 

## [v0.7.2] - [14/01/2019]
Code optimization and minor bug fixes or features.

[Added]
- New Recovering potion to double newly added fights limit (see below)
- Add PDF for special potions
- Add custom date to adminTable : this should allow the teacher to enter an event for a previous day (when forgotten or if internet was not available). Use with care since it may mess the scores up depending on the added event
- Add possibility to stay on adminTable after saving

[Changed]
- Many database requests have been optimized for faster loading
- allPlayers and allTeams are cached
- Limit fights to 3 per day : this should avoid 'cramming' and better span fights over several days
- Better handling of category filter buttons in adminTable
- Add period dates in period selector
- Add direct PDF links for teachers from teacher's work panel

[Fixed]
- Planet Alert News for recent additions (exercises, lessons...)
- Scoreboards still included 'test' player...
- Fix answer when quizzing about people
- Missing <th> tag in Users table

[Backend]
- 'public' field needs to be added to 'event' template


## [v0.7.1] - [29/12/2018]
Teachers can now manage their group names. Other improvements are more a personal workflow (SACoche, school website...).

[Added]
- Manage group names option for teachers
- Direct access to exercises so a player can log in and do the exercise from an external link
- Import Monster Attacks results from SACoche's CSV file (https://sacoche.sesamath.net/sacoche/index.php)
- Add 'Go to school' submenu for pupils to quickly access the school's specific websites

[Changed]
- New karma and reputation calculating rules
- Cleaner display for users table

[Backend]
- Permissions adjustements for 'group' and 'groups' fields/templates
- CSS ajustements for feel() module


## [v0.7.0] - [26/12/2018]
Players' scores are now cached to avoid timed-out recalculation when players had many events in their history. 

Underground Training impact on karma is limited in order to be more fair towards players not having an easy internet access.

### Added
- Cache management when recalculating history

### Changed
- Reputation recalculation rules are changed
- Karma recalculation rules are changed : simple UT 
- 3UT makes a training session excellent (used to be 5UT) : the goal is to encourage players to obtain +3UT instead of doing 3 sessions at +1UT.

### Backend
- tmpScores repeater added to 'archive' template


## [v0.6.0] - [19/12/2018]
The main change is the publish/unpublish feature for teachers but as always, many bug fixes and some translation issues, along with many small additions.

### Added
- Add possibility to publish/unpublish monsters for teachers
- Add indicator when no description is available for an exercise
- Add topics to exercise list
- Add 'Record an audio message' link to contact form (for logged in users)
- Add admin's possibility to force a monster's visibility

### Changed
- Highscores recalculation are now made individually to avoid request timeout
- Change 'toggle' and 'remove' symbols for better readability in adminTable
- Change exercises access restrictions to take into account the new publish status
- Show team name in page titles : hence better readability in browser's tabs
- Button to take the Memory Helmet off is now underneath the exercise in all cases
- Change bigger font for info popup
- Change French messages to use inclusive writing
- Change (personal workflow) : hkcount is not updated if a player has a penalty under way (not signed)
- CDN include for dataTable stylesheet
- Modules update/cleaning

### Fixed
- Fix finding period dates for work statistics
- Fix UT/FP stats : in-class battles were taken into account
- Fix player's first death
- Fix monsters list from Infos menu
- Fix announcements : Admin's messages are only shown to teachers
- Fix list of monsters : multilanguage field didn't return the monsters having no English summary Sort monsters on training page according to the names
- Fix monster image display during fights
- Fix Add death button in admin actions when checking history
- Fix UT highscores setting
- Fix saving when new highscore is set
- Fix group scoreboards
- Fix deleteFromId function
- Fix character encoding in tecahers' mails
- Fix monsters' links on Profile page
- Fix PHP warnings
- Forgotten translations

### Backend
- exerciseOwner repeater added to 'exercise' template : this is necessary for the publish/unpublish feature
- alwaysVisible field added to 'exercise' template : useful to force monster's visibility


## [v0.5.2] - [11/18/2018]
### Changed
- Fight's results are displayed in recent news (on Main Office)
- Bigger font-size for UT activity in recent news (on Main Office)


## [v0.5.1] - [11/18/2018]
External libraries update

### Changed
- All external libraries have been updated. bower.json manages updates
- Alert messages when saving Team options now use Sweetalert2 style
- Add helpAlert with players risking a penalty when loading adminTable (personal workflow)
- Clean code and libraries (remove deprecated things)

### Fixed
- Notifications positioning sometimes messed up menu display.
- Limit monsters to headteacher's monsters when using the Electronic visualizer


## [v0.5.0] - [11/16/2018]
Teacher's announcements are now possible. Another important changes are the helpAlerts notifications and possibility to print monsters' thumbnails.

### Backend
- New announcement template (teachers can edit and delete their own page)
- New fields : selectPlayers (checkbox), playersList (Page)
- Changes : teachers can add children to team template (with announcement template)
- Changes : exercise template now allows url segments to quickly link to train or fight versions

### Added
- Teacher's announcements to a team or individual players
- helpAlerts notifications to better guide players and teachers through options : a simple message show up on page load during 8 seconds to remind user about a particular option (lots of GC, no visualizer, help needed, ...)
- Ability to print monsters for teachers to give an image to players making successful fights

### Changed
- Alert messages now have a thin border to be more visible
- Train or fight monsters are now accessible via url segments : this allows easy linking in teacher's announcements.
- Update Sweetalert2 library
- CHANGELOG offers only 'major' versions links to avoid clutter : second parameter in version number corresponds to versions causing backend changes
- Successful fights are displayed on Newsboard so teacher can prepare monster's thumbnail and give it in class

### Fixed
- Minimum 20UT is checked before a fight
- PHP errors because of missing isset()


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
[v0.6.0]: https://framagit.org/celfred/planetAlert/tags/v0.6.0
[v0.7.0]: https://framagit.org/celfred/planetAlert/tags/v0.7.0
[v1.0.0]: https://framagit.org/celfred/planetAlert/tags/v1.0.0
[v1.1.0]: https://framagit.org/celfred/planetAlert/tags/v1.1.0
