=== LeagueManager ===
Contributors: Kolja Schleich
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2329191
Tags: sport, sport league, sidebar, widget, post
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 2.5.2

Plugin to manage and present Sports Leagues

== Description ==

This Plugin is designed to manage sports leagues and display them on your blog. It is originally built for gymnastics league, but can be used for any other as well.

**Features**

* add as many leagues as you want to
* easy adding of teams and matches
* add team logo (wp-content directory needs to be writable by the server)
* weekly-based ordering of matches with bulk editing mechanism
* automatic point calculation and ranking of teams
* breadcrumb navigation included
* activate/deactivate league toggling
* widget for each active league
* seperate capability to control access and compatibility with Role Manager
* TinyMCE Button for better usability


For further notes on using the plugin see the section below.

**Translations**

* German
* Dutch
* Swedish
* Polish
* Spanish

= Usage =

To print league results create a new page or post and add the following tag to it:

`[ leaguestandings = league_id ]` (without whitespaces)

where league_id is the ID of the league. This ist printed in the manage section.
This only prints the standings table. To display the compeitions table use the tag

`[ leaguematches = league_id, display ]` (without whitespaces).

Substitute 'league_id' with the respective of the league to be displayed. 'display' can either be left empty to display matches based on match days or "all" to display all matches or "home" to display only the matches of the home team. To display the crosstable put the following code into a post or page

`[ leaguecrosstable = league_id, mode ]` (without whitespaces).

mode is either 'embed' or 'popup', which makes it possible to optionally display the crosstable in a popup window if it is very large.
The widget can also be displayed statically for themes not supporting widgets. See FAQ.

== Installation ==

To install the plugin to the following steps

1. Unzip the zip-file and upload the content to your Wordpress Plugin directory.
2. Activiate the plugin via the admin plugin page.
3. Go to Manage -> League to add and manage leagues
4. Add a league and check out the settings page

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


== Screenshots ==
1. Main page for selected League
2. League Preferences
3. Adding of up to 15 matches simultaneously for one date
4. Easy insertion of tags via TinyMCE Button
5. Widget control panel