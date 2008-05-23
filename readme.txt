=== LeagueManager ===
Contributors: Kolja Schleich
Tags: sport, sport league, sidebar, widget, post
Requires at least: 2.3
Tested up to: 2.5
Stable tag: 1.4

Plugin to manage and present Sports Leagues

== Description ==

This Plugin is designed to manage sports leagues and display them on your blog.

**Features**

* add as many leagues as you want to
* standings table structure is controlled via the admin interface
* easy adding of teams and competitions
* dynamic ranking of teams by any number of table columns
* automatic ranking of teams
* breadcrumb navigation included
* activate/deactivate league toggling (since Version 1.3)
* widget for each active league (since Version 1.3)
* seperate capability to control access and compatibility with Role Manager (since Verison 1.4)

For further notes on using the plugin see the section below.

**Translations**

* German

= Usage =

To print league results create a new page or post and add the following tag to it: `[ leaguestandings = league_id ]` (without whitespaces) where league_id is the ID of the league. This ist printed in the manage section.
This only prints the standings table. To display the compeitions table use the tag `[ leaguecompetitions = league_id ]` (without whitespaces).

The points are displayed in the following format %d:%d where %d is any number. If you enter the points in the following way %d:NaN it is possible to hide the second part of the standings.

The widget can also be displayed statically for themes not supporting widgets. See FAQ.

== Installation ==

To install the plugin to the following steps

1. Unzip the zip-file and upload the content to your Wordpress Plugin directory.
2. Activiate the plugin via the admin plugin page.
3. Go to Manage -> League to add and manage leagues

== Frequently Asked Questions == 
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
