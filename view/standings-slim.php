<?php
/**
Template page for the standings table in slim form

The following variables are usable:
	
	$league: contains data about the league
	$teams: contains all teams of current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $teams ) : ?>

<table class="leaguemanager standingstable" summary="" title="<?php _e( 'Standings', 'leaguemanager' ) .' '.$league->title ?>">
<tr>
	<th class="num"><?php echo _c( 'Pos|Position', 'leaguemanager' ) ?></th>
	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
	<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
</tr>
<?php if ( $teams ) : ?>
<?php foreach( $teams AS $team ) : ?>

<tr class='<?php echo $team->class ?>'>
	<td class='rank'><?php echo $team->rank ?></td>
	<td><?php echo $team->title ?></td>
	<td class='num'><?php echo $team->done_matches ?></td>
	<td class='num'><?php echo $team->points ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php endif; ?>
