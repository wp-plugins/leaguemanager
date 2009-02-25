<?php
if ( isset($_POST['addLeague']) && !isset($_POST['deleteit']) ) {
	check_admin_referer('leaguemanager_add-league');
	$this->addLeague( $_POST['league_title'] );
	$this->printMessage();
} elseif ( isset($_GET['deactivate_league']) ) {
	$this->deactivateLeague( $_GET['deactivate_league'] );
} elseif ( isset( $_GET['activate_league'] ) ) {
	$this->activateLeague( $_GET['activate_league'] );
} elseif ( isset($_POST['doaction']) && $_POST['action'] == 'delete' ) {
	check_admin_referer('leagues-bulk');
	foreach ( $_POST['league'] AS $league_id )
		$this->delLeague( $league_id );
}
?>
<div class="wrap" style="margin-bottom: 1em;">
	<h2><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></h2>
	
	<form id="leagues-filter" method="post" action="">
	<?php wp_nonce_field( 'leagues-bulk' ) ?>
	
	<div class="tablenav" style="margin-bottom: 0.1em;">
		<!-- Bulk Actions -->
		<select name="action" size="1">
			<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
			<option value="delete"><?php _e('Delete')?></option>
		</select>
		<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
	</div>
	
	<table class="widefat" summary="" title="LeagueManager">
		<thead>
		<tr>
                        <th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('leagues-filter'));" /></th>
			<th scope="col" class="num">ID</th>
			<th scope="col"><?php _e( 'League', 'leaguemanager' ) ?></th>
			<th scope="col"><?php _e( 'Seasons', 'leaguemanager' ) ?></th>
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
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $l_id ?>" name="league[<?php echo $l_id ?>]" /></th>
				<td class="num"><?php echo $l_id ?></td>
				<td><a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;id=<?php echo $l_id ?>"><?php echo $league['title'] ?></a></td>
				<td class="num"><?php echo $leaguemanager->getNumSeasons( $l_id ) ?></td>
				<td class="num"><?php echo $leaguemanager->getNumTeams( $l_id ) ?></td>
				<td class="num"><?php echo $leaguemanager->getNumMatches( $l_id ) ?></td>
				<td><?php $this->toggleLeagueStatusText( $league['status'] ) ?></td>
				<td><?php $this->toggleLeagueStatusAction( $league['status'], $l_id ) ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	</form>

	<!-- Add New League -->
	<form action="" method="post" style="margin-top: 3em;">
		<?php wp_nonce_field( 'leaguemanager_add-league' ) ?>
		<h3><?php _e( 'Add League', 'leaguemanager' ) ?></h3>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="league_title"><?php _e( 'League', 'leaguemanager' ) ?></label></th><td><input type="text" name="league_title" id="league_title" value="" size="30" style="margin-bottom: 1em;" /></td>
		</tr>
		</table>
		<p class="submit"><input type="submit" name="addLeague" value="<?php _e( 'Add League', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>
