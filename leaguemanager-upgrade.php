<?php
/*
 * Drop deprecated tables
 */
$wpdb->query( "DROP TABLE `wp_leaguemanager_leaguemeta`" );
$wpdb->query( "DROP TABLE `wp_leaguemanager_teammeta`" );

/*
 * Update leagues table
 */
$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
if ( !in_array('forwin', $lm_cols) )	
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `forwin` tinyint( 4 ) NOT NULL default '2';" );
if ( !in_array('fordraw', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `fordraw` tinyint( 4 ) NOT NULL default '1';" );
if ( !in_array('fordloss', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `forloss` tinyint( 4 ) NOT NULL default '0';" );
if ( !in_array('date_format', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `date_format` varchar( 25 ) NOT NULL default '%e.%c';" );
if ( !in_array('home_teams_only', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `home_teams_only` tinyint( 1 ) NOT NULL default '0';" );
if ( !in_array('gymnastics', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `gymnastics` tinyint( 1 ) NOT NULL default '0';" );
if ( !in_array('active', $lm_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `active` tinyint( 1 ) NOT NULL default '1';" );


/*
* Update Match table
 */
$wpdb->query( "RENAME TABLE `wp_leaguemanager_competitions` TO `wp_leaguemanager_matches`" ); 

$wpdb->query( "ALTER TABLE  {$wpdb->leaguemanager_matches} DROP `competitor`" );
$wpdb->query( "ALTER TABLE  {$wpdb->leaguemanager_matches} DROP `home`" );

$lm_matches_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager_matches}" );
if ( !in_array('home_team', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_team` int( 11 ) NOT NULL;" );
if ( !in_array('away_team', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_team` int( 11 ) NOT NULL;" );
if ( !in_array('home_apparatus_points', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_apparatus_points` tinyint( 4 ) NOT NULL;" );
if ( !in_array('away_apparatus_points', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_apparatus_points` tinyint( 4 ) NOT NULL;" );
if ( !in_array('home_points', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_points` tinyint( 4 ) NOT NULL;" );
if ( !in_array('away_points', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_points` tinyint( 4 ) NOT NULL;" );
if ( !in_array('winner_id', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `winner_id` int( 11 ) NOT NULL;" );
if ( !in_array('loser_id', $lm_matches_cols) )
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `loser_id` int( 11 ) NOT NULL;" );

/*
* Upgrade from 2.0 to 2.1
*/
if ( in_array('date_format', $lm_cols) )
	$wpdb->query( "ALTER TABLE `wp_leaguemanager_leagues` DROP `date_format`" );

$wpdb->query( "ALTER TABLE `wp_leaguemanager_leagues` CHANGE `home_teams_only` `match_calendar` TINYINT( 1 ) NOT NULL DEFAULT '0'" );
$wpdb->query( "ALTER TABLE `wp_leaguemanager_leagues` CHANGE `gymnastics` `type` TINYINT( 1 ) NOT NULL DEFAULT '0'" );
?>