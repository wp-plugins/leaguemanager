<?php
     $leaguemanager->init();
if ( isset($_POST['addLeague']) && !isset($_POST['deleteit']) ) {
	check_admin_referer('leaguemanager_add-league');
	$message = $leaguemanager->addLeague( $_POST['league_title'] );
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
} elseif ( isset($_GET['deactivate_league']) ) {
	$leaguemanager->deactivateLeague( $_GET['deactivate_league'] );
} elseif ( isset( $_GET['activate_league'] ) ) {
	$leaguemanager->activateLeague( $_GET['activate_league'] );
} elseif ( isset($_POST['deleteit']) && isset($_POST['delete']) ) {
	check_admin_referer('leaguemanager_delete-league');
	foreach ( $_POST['delete'] AS $league_id )
		$leaguemanager->delLeague( $league_id );
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
			<th scope="col" class="num"><?php _e( 'Matches', 'leaguemanager' ) ?></th>
			<th scope="col"><?php _e( 'Status', 'leaguemanager' ) ?></th>
			<th scope="col"><?php _e( 'Action', 'leaguemanager' ) ?></th>
		</tr>
		<tbody id="the-list">
			<?php if ( $leagues = $leaguemanager->getLeagues() ) : ?>
			<?php foreach ( $leagues AS $l_id => $league ) : ?>
			<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $l_id ?>" name="delete[<?php echo $l_id ?>]" /></th>
				<td class="num"><?php echo $l_id ?></td>
				<td><a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $l_id ?>"><?php echo $league['title'] ?></a></td>
				<td class="num"><?php echo $leaguemanager->getNumTeams( $l_id ) ?></td>
				<td class="num"><?php echo $leaguemanager->getNumMatches( $l_id ) ?></td>
				<td><?php $leaguemanager->toggleLeagueStatusText( $l_id ) ?></td>
				<td><?php $leaguemanager->toggleLeagueStatusAction( $l_id ) ?></td>
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