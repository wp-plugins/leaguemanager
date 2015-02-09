<?php
/**
Template page for single race results

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	$season: current season
	$player: player ID or false
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
if ($match && !empty($match->raceresult)) {
	$points = array();
	foreach ($match->raceresult AS $id => $racer) {
		$points[$id] = $racer['points'];
	}
	arsort($points);
}
?>
<?php if ( $match ) : ?>

<h3><?php echo $match->title ?></h3>
<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Races', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th><?php _e( 'Name', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Points', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Time', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Category', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Race Type', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Other Info', 'leaguemanager' ) ?></th>
</tr>
<?php if ( !empty($match->raceresult) ) : $class = ''; ?>
<?php foreach ($points AS $id => $p) : $racer = $match->raceresult[$id]; ?>
<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
<tr class='<?php echo $class ?>'>
	<td><?php echo $racer['name'] ?></td>
	<td><?php echo $racer['points'] ?></td>
	<td><?php echo $racer['time'] ?></td>
	<td><?php echo $racer['category'] ?></td>
	<td><?php echo $match->racetype ?></td>
	<td><?php echo $racer['info'] ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<p><?php echo nl2br($match->description) ?></p>

<?php endif; ?>
