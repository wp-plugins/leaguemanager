=== LeagueManager ===
Contributors: Kolja Schleich
Tags: sport, sport league, sidebar, widget, post
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 2.2

Plugin to manage and present Sports Leagues

== Description ==

This Plugin is designed to manage sports leagues and display them on your blog. It is originally built for gymnastics league, but can be used for any other as well. Version 2.0 brings some major changes.

**Features**

* add as many leagues as you want to
* easy adding of teams and matches
* automatic point calculation and ranking of teams
* breadcrumb navigation included
* activate/deactivate league toggling
* widget for each active league
* seperate capability to control access and compatibility with Role Manager
* TinyMCE Button for better usability (since Version 2.2)


For further notes on using the plugin see the section below.

The plugin was tested with WP 2.3.3, 2.6.2 and 2.7-hemorrhage. If you encounter any problems, please contact me.

**Translations**

* German
* Dutch
* Swedish

= Usage =

To print league results create a new page or post and add the following tag to it:

`[ leaguestandings = league_id ]` (without whitespaces)

where league_id is the ID of the league. This ist printed in the manage section.
This only prints the standings table. To display the compeitions table use the tag

`[ leaguematches = league_id ]` (without whitespaces).

To display the crosstable put the following code into a post or page

`[ leaguecrosstable = league_id ]` (without whitespaces).

The widget can also be displayed statically for themes not supporting widgets. See FAQ.

== Installation ==

To install the plugin to the following steps

1. Unzip the zip-file and upload the content to your Wordpress Plugin directory.
2. Activiate the plugin via the admin plugin page.
3. Go to Manage -> League to add and manage leagues
4. Add a league and check out the settings page

== Frequently Asked Questions ==
**How can I change the color scheme**
You can simply overwrite the default color scheme by putting the following code into your theme style file. Displayed is the default scheme, simply replace the colors with those of your choice.

`table.leaguemanager th { background-color: #dddddd; }
table.leaguemanager tr.alternate { background-color: #ffffff; }
table.leaguemanager tr { background-color: #efefef; }
table.crosstable th, table.crosstable td { border: 1px solid #ffffff; }`

**How can I display the widget statically**

Since Version 1.1 you can display the widget statically with the following code

`<?php
leaguemanager_display_widget(array(
     "league_id" => $league_id,
     "match_display" => $match_display,
     "table_display" => $table_display,
     "info_page_id" => $info_page_id
));
?>`
Replace $league\_id with the ID of the league to display. $match\_display and $table\_display can either be 0, 1 or 2. 0 hides the competitions or standings table. 1 displays the team names in full length, 2 in short form. $info\_page\_id is the ID of the page where you put additional information about the league (this is optional, if this key is not passed to the function there will be no link displayed). The widget uses the following defaults for displaying:

`<?php
$defaults = array(
     'before_widget' => '<li id="league" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
     'after_widget' => '</li>',
     'before_title' => '<h2 class="widgettitle">',
     'after_title' => '</h2>',
);`
They can be overriden by passing the respective array elements to leaguemanager\_display\_widget function.

== ChangeLog ==

**Version 2.2**, *14-November-2008*
- FIXED: secondary ranking of teams by goal difference if not gymnastics league
- NEW: implemented crosstable for easy overview of all match results
- NEW: TinyMCE Button
- more styling upgrades

**Version 2.1**, *05-November-2008*
- NEW: adding of up to 15 matches simultaneously for one date
- NEW: using date and time formats from Wordpress settings
- Fixed bug for results determination if score was 0:0
- fixed some styling issues

**Version 2.0**, *11-October-2008*

- some major changes
- NEW: automatic point calculation

**Version 1.5**, *02-September-2008*

- NEW: design standings table display in widget
- Tested database table creation upon plugin activation

**Version 1.4.2**, *29-June-2008*

- Bugfix with check_admin_referer for WP 2.3.x

**Version 1.4.1**, *01-June-2008*

- Bugfix: saving of standings table

**Version 1.4**, *22-May-2008*

- NEW: wp_nonce_field for higher security
- NEW: seperate capability to control access
- some bugfixes

**Version 1.3**, *23-April-2008*

- activation/deactivation switch
- widget for every active league
- use of short title for widget

**Version 1.2.2**, *16-April-2008*

- Javascript fix for adding table columns

**Version 1.2.1**, *16-April-2008*

- fixed database creation bug

**Version 1.2**, *15-April-2008*

- remodeling of the plugin structure
- fixed bug to sort teams in widget
- some code cleansing
- load javascript only on Leaguemanager admin pages

**Version 1.1**, *9-April-2008*

 - several bug fixes concerning table structure settings and deleting leagues, teams or competitions
 - deletion of multiple leagues, teams or competitions
 - implemented function to display widget statically. See FAQ for usage
 - uninstallation method implemented
