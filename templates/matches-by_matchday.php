<?php
/**
Template page for the match table showing matches divided by match day

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an associative array
	$season: current season
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if (isset($_GET['match']) ) : ?>
	<?php leaguemanager_match($_GET['match']); ?>
<?php else : ?>

<?php if ( $matches ) : ?>
<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
<h3><?php printf(__('Match Day %d'), $i) ?></h3>
<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Match Plan', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
	<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
</tr>

<?php foreach ( $matches AS $match ) : ?>
<?php if ($match->match_day == $i) : ?>
<tr class='<?php echo $match->class ?>'>
	<td class='match'><?php echo $match->date." ".$match->start_time." ".$match->location ?><br /><a href="<?php echo $match->pageURL ?>"><?php echo $match->title ?></a> <?php echo $match->report ?></td>
	<td class='score' valign='bottom'><?php echo $match->score ?></td>
</tr>
<?php endif; ?>
<?php endforeach; ?>

</table>
<?php endfor; ?>
<?php endif; ?>

<?php endif; ?>
