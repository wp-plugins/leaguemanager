=== WPLeague ===
Contributors: Kolja Schleich
Tags: sport, sport league, sidebar, widget, post
Requires at least: 2.3
Tested up to: 2.5
Stable tag: 1.0

Plugin to manage and present Sports Leagues

== Description ==

This Plugin is designed to manage sports leagues and display them on your blog. It implements a lot of features:

* add as many leagues as you want to
* standings table structure is controlled via the admin interface
* easy adding of teams and competitions
* dynamic ranking of teams by any number of table columns
* automatic ranking of teams
* breadcrumb navigation included

For further notes on using the plugin see the section below.

= Usage =

To print league results create a new page or post and add the following tag to it: `[ leaguestandings = league_id ]` (without whitespaces) where league_id is the ID of the league. This ist printed in the manage section.
This only prints the standings table. To display the compeitions table use the tag `[ leaguecompetitions = league_id ]` (without whitespaces).

The points are displayed in the following format %d:%d where %d is any number. If you enter the points in the following way %d:NaN it is possible to hide the second part of the standings.

== Installation ==

To install the plugin to the following steps

1. Unzip the zip-file and upload the content to your Wordpress Plugin directory.
2. Activiate the plugin via the admin plugin page.
3. Go to Manage -> League to add and manage leagues

== Frequently Asked Questions == 
**How can I display the widget statically**

In this first version it is not possible to display the widget statically, but this is planned for the next version

== Screenshots ==

== ChangeLog ==