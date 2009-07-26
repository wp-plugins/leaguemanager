<?php
/**
Template page for championchip

The following variables are usable:
	
	$league: contains data of current league
	$championchip: championchip object
	$finals: data for finals
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>

<h3><?php _e( 'Final Results', 'leaguemanager' ) ?></h3>
<table class="widefat">
<thead>
<tr>
	<th scope="col"><?php _e( 'Round', 'leaguemanger' ) ?></th>
	<th scope="col" colspan="<?php echo $finals[0]->colspan; ?>" style="text-align: center;"><?php _e( 'Matches', 'leaguemanager' ) ?></td>
</tr>
<tbody id="the-list-finals" class="form-table">
<?php foreach ( $finals AS $final ) : ?>
<tr class="<?php echo $final->class ?>">
	<th scope="row"><strong><?php echo $final->name ?></strong></th>
	<?php foreach ( (array)$final->matches AS $no => $match ) : ?>
	<td colspan="<?php echo $final->colspan ?>" style="text-align: center;">
		<?php if ( $final->isFinal ) : ?>
		<p><span id="final_home" style="margin-right: 0.5em;"></span><?php echo $match->title2 ?><span id="final_away" style="margin-left: 0.5em;"></span></p>
		<?php else : ?>
		<p><?php echo $match->title2 ?></p>
		<?php endif; ?>

		<?php if ( $match->home_points != NULL && $match->away_points != NULL ) : ?>
		<?php if ( $final->isFinal ) : ?>
		<?php $img = '<img style="vertical-align: middle;" src="'.LEAGUEMANAGER_URL . '/admin/icons/cup.png" />'; ?>
		<script type="text/javascript">
			jQuery('span#<?php echo $final->field_id ?>').html('<?php echo addslashes_gpc($img) ?>').fadeIn('fast');
		</script>
		<?php endif; ?>

		<p><strong><?php echo $match->score ?></strong></p>
		<?php endif; ?>
	</td>
	<?php if ( $no%4 == 0 && $no < $final->num_matches ) : ?>
	</tr><tr class="<?php echo $final->class ?>"><th>&#160;</th>
	<?php endif; ?>

	<?php endforeach; ?>
</tr>
<?php endforeach ?>
</tbody>
</table>


<h3><?php _e( 'Final Matches', 'leaguemanager' ) ?></h3>
<?php foreach ( $finals AS $final ) : ?>
<h4><?php echo $final->name ?></h4>
<table class="widefat">
<thead>
<tr>
	<th><?php _e( '#', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Date','leaguemanager' ) ?></th>
	<th><?php _e( 'Match','leaguemanager' ) ?></th>
	<th><?php _e( 'Location','leaguemanager' ) ?></th>
	<th><?php _e( 'Begin','leaguemanager' ) ?></th>
	<th><?php _e( 'Score', 'leaguemanager' ) ?></th>
</tr>
</thead>
<tbody id="the-list-<?php echo $final->key ?>" class="form-table">
<?php foreach ( (array)$final->matches AS $no => $match ) : ?>
<tr class="<?php echo $final->class ?>">
	<td><?php echo $no ?></td>
	<td><?php echo $match->date ?></td> 
	<td><?php echo $match->title ?></td>
	<td><?php echo $match->location ?></td>
	<td><?php echo $match->time ?></td>
	<td><?php echo $match->score ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endforeach; ?>


<h3><?php _e( 'Preliminary Rounds Standings', 'leaguemanager' ) ?></h3>
<?php foreach ( $championchip->getGroups() AS $key => $group ) : ?>
<?php $teams = $leaguemanager->getTeams( "`league_id` = '".$league->id."' AND `season` = '".$league->season."' AND `group` = '".$group."'" ); ?>
<div class="alignleft" style="margin-right: 2em;">
	<h4><?php printf(__('Group %s', 'leaguemanager'), $group) ?></h4>
	<table class="widefat">
	<thead>
		<tr>
			<th scope="col" class="num">#</th>
			<th scope="col"><?php _e( 'Team', 'leaguemanager' ) ?>
			<th scope="col" class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
		</tr>
	</thead>
	<tbody id="the-list-standings-<?php echo $group ?>">
	<?php if ( $teams ) : $class = ''; ?>
	<?php foreach ( $teams AS $team ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
	<tr class="<?php echo $class ?>">
		<td class="num"><?php echo $team->rank ?></td>
		<td><?php echo $team->title ?></td>
		<td class="num"><?php printf($league->point_format, $team->points_plus, $team->points_minus) ?></td>
	</tr>
	<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
	</table>
</div>	
<?php if ( ($key+1)%4 == 0 ) echo '<br style="clear: both;" />'; ?>
<?php endforeach; ?>
