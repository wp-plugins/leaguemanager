=== LeagueManager ===
Contributors: Kolja Schleich
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2329191
Tags: sport, sport league, sidebar, widget, post
Requires at least: 2.7
Tested up to: 2.8
Stable tag: 3.2.2

Plugin to manage and present Sports Leagues

== Description ==

This Plugin is designed to manage sports leagues and display them on your blog.

**Features**

* easy adding of teams and matches
* add team logo (wp-content directory needs to be writable by the server)
* numerous point-rules implemented to also support special rules (e.g. Hockey, Pool, Baseball, Cornhole)
* weekly-based ordering of matches with bulk editing mechanism
* automatic or manual saving of standings table
* automatic or drag & drop ranking of teams
* link posts with specific match for match reports
* unlimited number of widgets
* modular setup for easy implementation of sport types
* seperate capability to control access and compatibility with Role Manager
* dynamic match statistics


For further notes on using the plugin see the [Usage](http://wordpress.org/extend/plugins/leaguemanager/other_notes).

**Translations**

* German
* Dutch
* Swedish
* Polish
* Spanish


[ChangeLog](http://svn.wp-plugins.org/leaguemanager/trunk/changelog.txt)

== Installation ==

To install the plugin to the following steps

1. Unzip the zip-file and upload the content to your Wordpress Plugin directory.
2. Activiate the plugin via the admin plugin page.


== Frequently Asked Questions ==
**I want to implement player registration. Is that possible?**

Yes it is, however not with this plugin, but with my [ProjectManager](http://wordpress.org/extend/plugins/projectmanager/). It is designed to manage any recurrent datasets, such as player profiles. It is also possible to set a hook in the user profile. Any user with the capability *project_user_profile* is able to use this feature. You would also need the [Role Manager](http://www.im-web-gefunden.de/wordpress-plugins/role-manager/) for access control. Further the plugin has a template engine implemented that makes it easy to design your own templates.


**How can I display the widget statically**

Put the following code where you want to display the widget

`<?php leaguemanager_display_widget( league_ID ); ?>`

Replace *league_ID* with the ID of the league you want to display. This will display the widget in a list with css class *leaguemanager_widget*.


== Screenshots ==
1. Main page for selected League
2. League Preferences
3. Adding of up to 15 matches simultaneously for one date
4. Easy insertion of tags via TinyMCE Button
5. Widget control panel


== Usage ==

= Shortcodes =
You can display the league standings with the following code

`[standings league_id=x mode=extend|compact]`


Replace x with the respective league ID to display. *mode* constrols if number if match statistics is displayed (extend) or not (compact).

Display a tabular match calendar with the following code

`[matches league_id=x  mode=all|home]`

Substitute x with the respective of the league ID to display. If *mode* is missing the matches will be displayed ordered by match days (default), *mode=all* causes all matches of this league to be displayed in a single table, *mode=home* only displays matches of home team in one single table. A single match is displayed as folows:

`[match id=x]`

You can also display a crosstable of a league with the following code

`[crosstable league_id=x mode=embed|popup]`


Substitute x with the respective of the league ID to display, *mode* can be either *embed*, to display the crosstable in the page/post, or *popup* to display it in a thickbox popup window. *mode=popup* is useful if you have very much teams.

A list of teams can be shown with the following code

`[teams league_id=x season=Y league_name=name]`

Y is the season name. Use either league_id or league_name, but not both. A single team page is displayed with

`[team id=x]`

You can display an archive of all leagues with the tag

`[leaguearchive]`


= Templates =
LeagueManager Plugin supports templates, which are placed in

`path_to_plugin/view/`

If you want to customize any template to your own needs simply copy it to

`your_theme_directory/leaguemanager`

The template loader will first check the theme directory, so you can edit the template there. To use a specific template use the *template* tag.


= Template Tags =
There are three tags to display the standings table, matches and crosstable manually in your theme.

`leaguemanager_standings( leagueID, logo = 'true|false', mode = 'extend|compact' )`

`leaguemanager_matches( leagueID, mode = '|all|home' )`

`leaguemanager_crosstable( leagueID, mode = '|popup' )`

See **functions.php** for details on using the functions.


== ChangeLog ==
See [changelog.txt](http://svn.wp-plugins.org/leaguemanager/trunk/changelog.txt).

== Credits ==
The LeagueManager icon is taken from the Fugue Icons of http://www.pinvoke.com/.
