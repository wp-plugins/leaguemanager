<?php
if ( isset($_POST['addLeague']) && !isset($_POST['deleteit']) && check_admin_referer('leaguemanager_add-league') ) {
	$return_message = $leaguemanager->add_league( $_POST['league_title'] );

	echo '<div id="message" class="updated fade"><p><strong>'.__( $return_message, 'leaguemanager' ).'</strong></p></div>';
} elseif ( isset($_GET['deactivate_league']) ) {
	$leaguemanager->deactivate_league( $_GET['deactivate_league'] );
} elseif ( isset( $_GET['activate_league'] ) ) {
	$leaguemanager->activate_league( $_GET['activate_league'] );
} elseif ( isset($_POST['deleteit']) && isset($_POST['delete']) && check_admin_referer('leaguemanager_delete-league') ) {
	foreach ( $_POST['delete'] AS $league_id )
		$leaguemanager->del_league( $league_id );
}
?>
<div class="wrap" style="margin-bottom: 1em;">
	<h2><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></h2>
	
	<form id="leagues-filter" method="post" action="">
	<?php wp_nonce_field( 'leaguemanager_delete-league' ) ?>
	
	<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','leaguemanager' ) ?>" class="button-secondary" /></div>
	
	<table class="widefat" summary="" title="LeagueManager">
		<thead>
		<tr>
                        <th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('leagues-filter'));" /></th>
			<th scope="col" class="num">ID</th>
			<th scope="col"><?php _e( 'League', 'leaguemanager' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Teams', 'leaguemanager' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Competitions', 'leaguemanager' ) ?></th>
			<th scope="col"><?php _e( 'Status', 'leaguemanager' ) ?></th>
			<th scope="col"><?php _e( 'Action', 'leaguemanager' ) ?></th>
		</tr>
		<tbody id="the-list">
			<?php if ( $leagues = $leaguemanager->get_leagues() ) : ?>
			<?php foreach ( $leagues AS $l_id => $league_data ) : ?>
			<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $l_id ?>" name="delete[<?php echo $l_id ?>]" /></th>
				<td class="num"><?php echo $l_id ?></td>
				<td><a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $l_id ?>"><?php echo $league_data['title'] ?></a></td>
				<td class="num"><?php echo $leaguemanager->get_num_teams( $l_id ) ?></td>
				<td class="num"><?php echo $leaguemanager->get_num_competitions( $l_id ) ?></td>
				<td><?php $leaguemanager->toggle_league_status_text( $l_id ) ?></td>
				<td><?php $leaguemanager->toggle_league_status_action( $l_id ) ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	</form>
</div>


<!-- Add New League -->
<form class="leaguemanager" action="" method="post">
<?php wp_nonce_field( 'leaguemanager_add-league' ) ?>
<div class="wrap"><div class="narrow">
	<h2><?php _e( 'Add League', 'leaguemanager' ) ?></h2>
	<label for="league_title"><?php _e( 'League', 'leaguemanager' ) ?>:</label><input type="text" name="league_title" id="league_title" value="" size="30" style="margin-bottom: 1em;" /><br />
		
	<input type="hidden" name="league_id" value="" />
		
	<p class="submit"><input type="submit" name="addLeague" value="<?php _e( 'Add League', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
</div></div>
</form>

<!-- Plugin Uninstallation -->
<div class="wrap">
	<h3 style='clear: both; padding-top: 1em;'><?php _e( 'Uninstall Leaguemanager', 'leaguemanager' ) ?></h3>
	<form method="get" action="index.php">
		<input type="hidden" name="leaguemanager" value="uninstall" />
		
		<p><input type="checkbox" name="delete_plugin" value="1" id="delete_plugin" /> <label for="delete_plugin"><?php _e( 'Yes I want to uninstall Leaguemanager Plugin. All Data will be deleted!', 'leaguemanager' ) ?></label> <input type="submit" value="<?php _e( 'Uninstall Leaguemanager', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>