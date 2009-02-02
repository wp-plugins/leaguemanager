<?php
$old_options = get_option( 'leaguemanager' );
if (version_compare($old_options['version'], '2.0', '<')) {
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
	if ( !in_array('forloss', $lm_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `forloss` tinyint( 4 ) NOT NULL default '0';" );
	if ( !in_array('match_calendar', $lm_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `match_calendar` tinyint( 1 ) NOT NULL default '1';" );
	if ( !in_array('type', $lm_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `type` tinyint( 1 ) NOT NULL default '2';" );
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
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_apparatus_points` tinyint( 4 ) NULL default NULL;" );
	if ( !in_array('away_apparatus_points', $lm_matches_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_apparatus_points` tinyint( 4 ) NULL default NULL;" );
	if ( !in_array('home_points', $lm_matches_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_points` tinyint( 4 ) NULL default NULL;" );
	if ( !in_array('away_points', $lm_matches_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_points` tinyint( 4 ) NULL default NULL;" );
	if ( !in_array('winner_id', $lm_matches_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `winner_id` int( 11 ) NOT NULL;" );
	if ( !in_array('loser_id', $lm_matches_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `loser_id` int( 11 ) NOT NULL;" );
}

/*
* Upgrade from 2.0 to 2.1p_
*/
if (version_compare($old_options['version'], '2.0', '<')) {
	$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
	if ( in_array('date_format', $lm_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `date_format`" );
	
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} CHANGE `home_teams_only` `match_calendar` TINYINT( 1 ) NOT NULL DEFAULT '1'" );
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} CHANGE `gymnastics` `type` TINYINT( 1 ) NOT NULL DEFAULT '2'" );
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} CHANGE `home_apparatus_points` `home_apparatus_points` TINYINT( 4 ) NULL DEFAULT NULL , 
	CHANGE `away_apparatus_points` `away_apparatus_points` TINYINT( 4 ) NULL DEFAULT NULL ,
	CHANGE `home_points` `home_points` TINYINT( 4 ) NULL DEFAULT NULL ,
	CHANGE `away_points` `away_points` TINYINT( 4 ) NULL DEFAULT NULL" );
}

/*
* Upgrade to Version 2.3.1
*/
if (version_compare($old_options['version'], '2.3.1', '<')) {
	$charset_collate = '';
	if ( $wpdb->supports_collation() ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = "CONVERT TO CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}
	
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} $charset_collate" );
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} $charset_collate" );
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} $charset_collate" );
}

/*
* Upgrade to 2.4.1
*/
if (version_compare($old_options['version'], '2.4.1', '<')) {
	$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
	if ( !in_array('show_logo', $lm_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `show_logo` TINYINT( 1 ) NOT NULL" );
	
	$lm_teams_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager_teams}" );
	if ( !in_array('logo', $lm_teams_cols) )
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `logo` VARCHAR( 50 ) NOT NULL AFTER `short_title`" );
}

/*
 * Upgrade to 2.5
 */
if (version_compare($old_options['version'], '2.5', '<')) {
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `match_day` TINYINT( 4 ) NOT NULL AFTER `away_team`" );
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `num_match_days` TINYINT( 4 ) NOT NULL AFTER `type`" );
		
	/**
	 * Copy Logos to new image directory and delete old one
	 */p_
	$dir_src = WP_CONTENT_DIR.'/leaguemanager';
	$dir_handle = opendir($dir_src);
	if ( wp_mkdir_p( $this->getImagePath() ) ) {
		while( $file = readdir($dir_handle) ) {
			if( $file!="." && $file!=".." ) {
				if ( copy ($dir_src."/".$file, $this->getImagePath()."/".$file) )
					unlink($dir_src."/".$file);
			}
		}
		
		@rmdir($dir_src);
	}
}

/*
* Upgrade to 2.5.1
*/
if (version_compare($old_options['version'], '2.5.1', '<')) {
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `match_calendar`" );
}


/*
* Upgrade to 2.6
*/
if (version_compare($old_options['version'], '2.6', '<')) {
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `post_id` int( 11 ) NOT NULL" );
}

/*
* Upgrade to 2.7
*/
if (version_compare($old_options['version'], '2.7', '<')) {
	$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `points_plus` int( 11 ) NOT NULL, ADD `points_minus` int( 11 ) NOT NULL, ADD `points2_plus` int( 11 ) NOT NULL, ADD `points2_minus` int( 11 ) NOT NULL" );
}
?>