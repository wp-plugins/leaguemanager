<div class="wrap">
<div class="narrow">
	<h2><?php _e( 'Show Leagues', 'leaguemanager' ) ?></h2>
	<!--<p><a href="admin.php?page=leaguemanager.php&amp;item=league&amp;mode=add"><?php _e( 'Add League', 'leaguemanager' ) ?></a></p>-->
	<table class="widefat" summary="" title="LeagueManager">
		<thead>
		<tr>
			<th>ID</th>
			<th><?php _e( 'League', 'leaguemanager' ) ?></th>
			<th colspan="2" style="text-align: center;"><?php _e( 'Actions','leaguemanager' ) ?></th>
		</tr>
		<tbody id="the-list">
			<?php if ( $leagues = $this->get_league() ) : ?>
			<?php foreach ( $leagues AS $l_id => $league_data ) : $del_message = __( 'Are you sure to delete', 'leaguemanager' ) . $this->leagues[$l_id]['title'] .' ' . __( 'All Teams and Competitions will be deleted as well', 'leaguemanager' ) ."!"; ?>
			<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class ?>">
				<td><?php echo $l_id ?></td>
				<td><a href="edit.php?page=leaguemanager.php&amp;show_league=<?php echo $l_id ?>"><?php echo $league_data['title'] ?></a></td>
				<td><a href="admin.php?page=leaguemanager.php&amp;mode=edit&amp;item=league&amp;league_id=<?php echo $l_id ?>"><?php _e( 'Edit', 'leaguemanager' ) ?></a></td>
				<td><a href="admin.php?page=leaguemanager.php&amp;mode=del&amp;item=league&amp;league_id=<?php echo $l_id ?>"><?php _e( 'Delete', 'leaguemanager' ) ?></a></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
</div>

<?php
     $form_title = 'Add League';
     $league_title = ''; $home_team = ''; $league_id = '';

     include 'edit-league.php';
?>