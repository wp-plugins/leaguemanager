<?php $league_id = $_GET['show_league']; $league_title = $this->leagues[$league_id]['title']; $this->save_teams( $league_id ); ?>
<?php $this->print_breadcrumb_navi( $league_id ) ?>
<div class="wrap">
	<h2 style="clear: none;"><?php echo $league_title ?></h2>
	
	<p>
		<a href="edit.php?page=leaguemanager.php&amp;mode=edit&amp;item=league&amp;league_id=<?php echo $league_id ?>"><?php _e( 'Settings', 'leaguemanager' ) ?></a> &middot;
		<a href="edit.php?page=leaguemanager.php&amp;mode=add&amp;item=team&amp;league_id=<?php echo $league_id ?>"><?php _e( 'Add Team','leaguemanager' ) ?></a> &middot;
		<a href="edit.php?page=leaguemanager.php&amp;mode=add&amp;item=competition&amp;league_id=<?php echo $league_id ?>"><?php _e( 'Add Competition','leaguemanager' ) ?></a>
	</p>
	
	<h3><?php _e( 'Standings', 'leaguemanager' ) ?></h3>
	
	<form id="teams-filter" action="edit.php?page=leaguemanager.php&amp;show_league=<?php echo $league_id ?>" method="post">
		<input type="hidden" name="item" value="teams" />
		<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','leaguemanager' ) ?>" class="button-secondary" /></div>
		
		<table class="widefat" summary="" title="Ergebnisse">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="leaguemanagerCheckAll(document.getElementById('teams-filter'));" /></th>
			<?php echo $this->get_table_head( $league_id ) ?>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $teams = $this->get_ranked_teams( $league_id ) ?>
		<?php if ( count($teams) > 0 ) : ?>
		<?php foreach( $teams AS $rank => $team ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $team->id ?>" name="delete[<?php echo $team->id ?>]" /></th>
			<td class="num"><?php echo $rank ?></td>
			<td><a href="edit.php?page=leaguemanager.php&amp;mode=edit&amp;item=team&amp;team_id=<?php echo $team->id; ?>"><?php echo $team->title ?></a><input type="hidden" name="team[<?php echo $team->id ?>]" value="<?php echo $team->title ?>" /></td>
			<?php $this->print_table_body( $team->id, 'admin' ) ?>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		<input type="hidden" name="updateLeague" value="table" />
		
		<?php if ( count($teams) > 0 ) : ?>
			<p class="submit"><input type="submit" name="updateTable" value="<?php _e( 'Update Table','leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
	</form>
	
	<h3><?php _e( 'Competitions Program','leaguemanager' ) ?></h3>
	<?php $competitions = $this->get_competitions( 'league_id = "'.$league_id.'"' ); ?>
	
	<form id="competitions-filter" action="" method="post">
		<input type="hidden" name="item" value="competitions" />
		<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','leaguemanager' ) ?>" class="button-secondary" /></div>
		
		<table class="widefat" summary="" title="<?php _e( 'Competitions Program','leaguemanager' ) ?>" style="margin-bottom: 2em;">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="leaguemanagerCheckAll(document.getElementById('competitions-filter'));" /></th>
			<th><?php _e( 'Date','leaguemanager' ) ?></th>
			<th><?php _e( 'Competition','leaguemanager' ) ?></th>
			<th><?php _e( 'Location','leaguemanager' ) ?></th>
			<th><?php _e( 'Begin','leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php if ( $competitions ) : ?>
			<?php foreach ( $competitions AS $competition ) :
				$class = ( 'alternate' == $class ) ? '' : 'alternate';
			?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $competition->id ?>" name="delete[<?php echo $competition->id ?>]" /></th>
				<td><?php echo $competition->date_day.".".$competition->date_month ?>.</td>
				<td><a href="edit.php?page=leaguemanager.php&amp;mode=edit&amp;item=competition&amp;cid=<?php echo $competition->id ?>">
				<?php if( $competition->home == 1 ) : ?>
					<?php echo $this->leagues[$league_id]['home_team']['title'] ?> - <?php echo $this->teams[$competition->competitor]['title'] ?>
				<?php else : ?>
					<?php echo $this->teams[$competition->competitor]['title'] ?> - <?php echo $this->leagues[$league_id]['home_team']['title'] ?>
				<?php endif; ?></a>
				</td>
				<td><?php echo ( '' == $competition->location ) ? 'N/A' : $competition->location ?></td>
				<td><?php echo ( '00:00' == $competition->hour.":".$competition->minutes ) ? 'N/A' : $competition->hour.":".$competition->minutes . ' Uhr' ?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
	</form>
	</div>
</div>