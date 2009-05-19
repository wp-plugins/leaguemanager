<?php
/*
* Temporariliy just store some codde
*/


add_action( 'wp_ajax_leaguemanager_save_goals', 'leaguemanager_save_goals' );
add_action( 'wp_ajax_leaguemanager_save_cards', 'leaguemanager_save_cards' );
add_action( 'wp_ajax_leaguemanager_save_exchanges', 'leaguemanager_save_exchanges' );



/**
 * SACK response to save shot goals
 *
 * @since 2.9
 */
function leaguemanager_save_goals() {
	global $wpdb;
	$match_id = intval($_POST['match_id']);
	$goals = $_POST['goals'];
	//$goals = str_replace('-new-', "\n", $goals);
	
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `goals` = '%s' WHERE `id` = '%d'", $goals, $match_id ) );

	die("tb_remove();");
}

/**
 * SACK response to save cards
 *
 * @since 2.9
 */
function leaguemanager_save_cards() {
	global $wpdb;
	$match_id = intval($_POST['match_id']);
	$cards = $_POST['cards'];

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `cards` = '%s' WHERE `id` = '%d'", $cards, $match_id ) );

	die("tb_remove();");
}

/**
 * SACK response to save exchanges
 *
 * @since 2.9
 */
function leaguemanager_save_exchanges() {
	global $wpdb;
	$match_id = intval($_POST['match_id']);
	$exchanges = $_POST['exchanges'];

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `exchanges` = '%s' WHERE `id` = '%d'", $exchanges, $match_id ) );

	die("tb_remove();");
}

	/**
	 * get card name
	 *
	 * @param string $type
	 * @return nice card name
	 */
	function getCardName( $type )
	{
		$cards = array( 'red' => __( 'Red', 'leaguemanager' ), 'yellow' => __( 'Yellow', 'leaguemanager' ), 'yellow-red' => __( 'Yellow/Red', 'leaguemanager' ) );
		return $cards[$type];
	}
?>
