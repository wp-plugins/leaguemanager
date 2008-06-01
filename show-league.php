<?php
if ( isset($_POST['updateLeague']) AND !isset($_POST['deleteit']) ) {
	if ( 'team' == $_POST['updateLeague'] && check_admin_referer('leaguemanager_manage-teams') ) {
		$home = isset( $_POST['home'] ) ? 1 : 0;
		if ( '' == $_POST['team_id'] )
			$return_message = $leaguemanager->add_team( $_POST['league_id'], $_POST['short_title'], $_POST['team'], $home );
		else
			$return_message = $leaguemanager->edit_team( $_POST['team_id'], $_POST['short_title'], $_POST['team'], $home );
	} elseif ( 'competition' == $_POST['updateLeague'] && check_admin_referer('leaguemanager_manage-competitions') ) {
		$date = $_POST['competition_year'].'-'.str_pad($_POST['competition_month'], 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['competition_day'], 2, 0, STR_PAD_LEFT).' '.str_pad($_POST['begin_hour'], 2, 0, STR_PAD_LEFT).':'.str_pad($_POST['begin_minutes'], 2, 0, STR_PAD_LEFT).':00';
		$home = isset( $_POST['home'] ) ? 1 : 0;
										
		if ( '' == $_POST['cid'] )
			$return_message = $leaguemanager->add_competition( $date, $_POST['competitor'], $home, $_POST['location'], $_POST['league_id'] );
		else
			$return_message = $leaguemanager->edit_competition( $date, $_POST['competitor'], $home, $_POST['location'], $_POST['league_id'], $_POST['cid'] );
	} elseif ( 'table' == $_POST['updateLeague'] && check_admin_referer('leaguemanager_table') ) {
		$return_message = $leaguemanager->update_table( $_POST['team'], $_POST['table_data'] );
	}
	
	echo '<div id="message" class="updated fade"><p><strong>'.__( $return_message, 'leaguemanager' ).'</strong></p></div>';
} elseif ( isset($_POST['deleteit']) AND isset($_POST['delete']) ) {
	if ( (isset( $_POST['item']) && 'teams' == $_POST['item'] ) && check_admin_referer('leaguemanager_table') ) {
		foreach ( $_POST['delete'] AS $team_id )
			$leaguemanager->del_team( $team_id);
	} elseif ( (isset( $_POST['item']) && 'competitions' == $_POST['item'] ) && check_admin_referer('leaguemanager_del-competitions') ) {
		foreach ( $_POST['delete'] AS $cid )
			$leaguemanager->del_competition( $cid );
	}
}

$league_id = $_GET['id'];
$curr_league = $leaguemanager->get_leagues( $league_id );

$league_title = $curr_league['title'];
$team_list = $leaguemanager->get_teams ( 'league_id = "'.$league_id.'"', 'ARRAY' );
?>
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <?php echo $league_title ?></p>
	
	<h2 style="clear: none;"><?php echo $league_title ?></h2>
	
	<p>
		<a href="edit.php?page=leaguemanager/league.php&amp;edit=<?php echo $league_id ?>"><?php _e( 'Settings', 'leaguemanager' ) ?></a> &middot;
		<a href="edit.php?page=leaguemanager/team.php&amp;league_id=<?php echo $league_id ?>"><?php _e( 'Add Team','leaguemanager' ) ?></a> &middot;
		<a href="edit.php?page=leaguemanager/competition.php&amp;league_id=<?php echo $league_id ?>"><?php _e( 'Add Competition','leaguemanager' ) ?></a>
	</p>
	
	<h3><?php _e( 'Standings', 'leaguemanager' ) ?></h3>
	
	<form id="teams-filter" action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_table' ) ?>
			
		<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','leaguemanager' ) ?>" class="button-secondary" /></div>
		
		<table class="widefat" summary="" title="Ergebnisse">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('teams-filter'));" /></th>
			<?php echo $leaguemanager->get_table_head( $league_id ) ?>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $teams = $leaguemanager->get_ranked_teams( $league_id ) ?>
		<?php if ( count($teams) > 0 ) : ?>
		<?php foreach( $teams AS $rank => $team ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $team->id ?>" name="delete[<?php echo $team->id ?>]" /></th>
			<td class="num"><?php echo $rank ?></td>
			<td><a href="edit.php?page=leaguemanager/team.php&amp;edit=<?php echo $team->id; ?>"><?php echo $team->title ?></a><input type="hidden" name="team[<?php echo $team->id ?>]" value="<?php echo $team->title ?>" /></td>
			<?php $leaguemanager->print_table_body( $team->id, 'admin' ) ?>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		<input type="hidden" name="updateLeague" value="table" />
		<input type="hidden" name="item" value="teams" />
		
		<?php if ( count($teams) > 0 ) : ?>
			<p class="submit"><input type="submit" name="updateTable" value="<?php _e( 'Update Table','leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
	</form>
	
	<h3><?php _e( 'Competitions Program','leaguemanager' ) ?></h3>	
	<form id="competitions-filter" action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_del-competitions' ) ?>
		
		<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','leaguemanager' ) ?>" class="button-secondary" /></div>
		
		<table class="widefat" summary="" title="<?php _e( 'Competitions Program','leaguemanager' ) ?>" style="margin-bottom: 2em;">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('competitions-filter'));" /></th>
			<th><?php _e( 'Date','leaguemanager' ) ?></th>
			<th><?php _e( 'Competition','leaguemanager' ) ?></th>
			<th><?php _e( 'Location','leaguemanager' ) ?></th>
			<th><?php _e( 'Begin','leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php if ( $competitions = $leaguemanager->get_competitions( 'league_id = "'.$league_id.'"' ) ) : ?>
			<?php foreach ( $competitions AS $competition ) :
				$class = ( 'alternate' == $class ) ? '' : 'alternate';
			?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $competition->id ?>" name="delete[<?php echo $competition->id ?>]" /></th>
				<td><?php echo $competition->date_day.".".$competition->date_month ?>.</td>
				<td><a href="edit.php?page=leaguemanager/competition.php&amp;edit=<?php echo $competition->id ?>">
				<?php if( $competition->home == 1 ) : ?>
					<?php echo $curr_league['home_team']['title'] ?> - <?php echo $team_list[$competition->competitor]['title'] ?>
				<?php else : ?>
					<?php echo $team_list[$competition->competitor]['title'] ?> - <?php echo $curr_league['home_team']['title'] ?>
				<?php endif; ?></a>
				</td>
				<td><?php echo ( '' == $competition->location ) ? 'N/A' : $competition->location ?></td>
				<td><?php echo ( '00:00' == $competition->hour.":".$competition->minutes ) ? 'N/A' : $competition->hour.":".$competition->minutes . ' Uhr' ?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		
		<input type="hidden" name="item" value="competitions" />
	</form>
	</div>
</div>