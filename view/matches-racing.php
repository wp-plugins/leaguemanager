<?php
/**
Template page for full racing results

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	$season: current season
	$roster: ID of individual team member or false
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $matches ) : ?>

<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Races', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th><?php _e( 'Date', 'leaguemanager' ) ?></th>
	<?php if (!$roster) : ?>
	<th><?php _e( 'Name', 'leaguemanager' ) ?></th>
	<?php endif; ?>
	<th><?php _e( 'Event', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Category', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Race Type', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Result', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Other Info', 'leaguemanager' ) ?></th>
</tr>
<?php foreach ( $matches AS $match ) : ?>

<?php if ( !empty($match->raceresult) ) : ?>
<?php foreach ( $match->raceresult AS $id => $racer ) : ?>

<?php if ( !$roster || ( $roster && ($roster == $id || $roster == $racer['name']) ) ) : ?>
<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
<tr class='<?php echo $class ?>'>
	<td><?php echo mysql2date(get_option('date_format'), $match->date) ?></td>
	<?php if (!$roster) : ?>
	<td><?php echo $racer['name'] ?></td>
	<?php endif; ?>
	<td><?php echo $match->title ?></td>
	<td><?php echo $racer['category'] ?></td>
	<td><?php echo $match->racetype ?></td>
	<td><?php echo $racer['result'] ?></td>
	<td><?php echo $racer['info'] ?></td>
</tr>
<?php endif; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php endforeach; ?>
</table>

<?php endif; ?>
