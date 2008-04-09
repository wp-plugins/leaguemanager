<?php if ( isset($_POST['updateLeague']) || isset($_POST['deleteit']) ) $this->get_leagues(); ?>
<div class="wrap" style="margin-bottom: 1em;">
<!--<div class="narrow">-->
	<h2><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></h2>
	<!--<p><a href="admin.php?page=leaguemanager.php&amp;item=league&amp;mode=add"><?php _e( 'Add League', 'leaguemanager' ) ?></a></p>-->
	
	<form id="leagues-filter" method="post" action="edit.php?page=leaguemanager.php">
		
	<input type="hidden" name="item" value="leagues" />
	<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','leaguemanager' ) ?>" class="button-secondary" /></div>
	
	<table class="widefat" summary="" title="LeagueManager">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="leaguemanagerCheckAll(document.getElementById('leagues-filter'));" /></th>
			<th scope="col" class="num">ID</th>
			<th scope="col"><?php _e( 'League', 'leaguemanager' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Teams', 'leaguemanager' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Competitions', 'leaguemanager' ) ?></th>
		</tr>
		<tbody id="the-list">
			<?php if ( $leagues = $this->get_league() ) : ?>
			<?php foreach ( $leagues AS $l_id => $league_data ) : ?>
			<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $l_id ?>" name="delete[<?php echo $l_id ?>]" /></th>
				<td class="num"><?php echo $l_id ?></td>
				<td><a href="edit.php?page=leaguemanager.php&amp;show_league=<?php echo $l_id ?>"><?php echo $league_data['title'] ?></a></td>
				<td class="num"><?php echo $this->get_num_teams( $l_id ) ?></td>
				<td class="num"><?php echo $this->get_num_competitions( $l_id ) ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	</form>
<!--</div>-->
</div>

<?php
     $form_title = 'Add League';
     $league_title = ''; $home_team = ''; $league_id = '';

     include 'edit-league.php';
?>

<div class="wrap">
	<h2><?php _e( 'Uninstall Leaguemanager', 'leaguemanager' ) ?></h2>
	<form method="get" action="index.php">
		<input type="hidden" name="leaguemanager" value="uninstall" />
		
		<p><input type="checkbox" name="delete_plugin" value="1" /> <?php _e( 'Yes I want to uninstall Leaguemanager Plugin. All Data will be deleted!', 'leaguemanager' ) ?> <input type="submit" value="<?php _e( 'Uninstall Leaguemanager','leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>