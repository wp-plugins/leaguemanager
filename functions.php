<?php
/**
 * SACK response for showing match date selection in TinyMCE button
 *
 * @since 2.4
 */
function leaguemanager_show_match_date_selection() {
	global $wpdb;
	$el_id = $_POST['el_id'];
	$league_id = intval($_POST['league_id']);
	
	$matches = $wpdb->get_results( "SELECT DATE_FORMAT(`date`, '%Y-%m-%d') AS date FROM {$wpdb->leaguemanager_matches} WHERE `league_id` = {$league_id}" );

	$dates = array();
	foreach ( $matches AS $match )
		if ( !in_array($match->date, $dates) )
			$dates[] = $match->date;

	$date_selection = "<select size='1' name='match_date' id='match_date'><option value=''>".__( 'All Matches', 'leaguemanager' )."</option>";
	foreach ( $dates AS $date )
		$date_selection .= "<option value='".$date."'>".mysql2date(get_option('date_format'), $date)."</option>";
	$date_selection .= "</select>";

	$date_selection = addslashes_gpc($date_selection);
	die( "function displayMatchDateSelection() {
		var leagueId = ".$league_id.";
		dateTitle = '".__("Date", "leaguemanager")."';
		dateSelection = '".$date_selection."';
		if ( leagueId != 0 ) {
			out = \"<td><label for='match_date'>\" + dateTitle + \"</label></td>\";
			out += \"<td>\" + dateSelection + \"</td>\";
			document.getElementById('$el_id').innerHTML = out;

			note = '".__( '<strong>Note:</strong> If you want to display matches of more than one date, you need to add them manually separated by |.', 'leaguemanager' )."';
			document.getElementById('match_note').innerHTML = note;
		}
	}
	displayMatchDateSelection();
	");
}

?>
