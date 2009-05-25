<?php
/**
Template page for the standings table in extended form (default)

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
	<th class="num">&#160;</th>
	<?php if ( $league->show_logo ) : ?>
	<th class="logo">&#160;</th>
	<?php endif; ?>
	
	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
	<th class="num"><?php echo _c( 'W|Won','leaguemanager' ) ?></th><th class="num"><?php echo _c( 'T|Tie','leaguemanager' ) ?></th><th class="num"><?php echo _c( 'L|Lost','leaguemanager' ) ?></th>
	<th class="num"><?php echo _c( 'RF|Runs For', 'leaguemanager' ) ?></th>
	<th class="num"><?php echo _c( 'RA|Runs Against', 'leaguemanager' ) ?></th>
	<th class="num"><?php echo _c( 'PCT|Percent Win', 'leaguemanager' ) ?></th>
	<th class="num"><?php echo _c( 'GB|Games Behind', 'leaguemanager' ) ?></th>
	<th class="num"><?php echo _c( 'SO|Shutouts', 'leaguemanager' ) ?></th>
</tr>
<?php if ( $teams ) : ?>
<?php foreach( $teams AS $team ) : ?>

<?php $win_percent = ( $team->done_matches > 0 ) ? round($team->won_matches/$team->done_matches, 3) : 0; ?>
<tr class='<?php echo $team->class ?>'>
	<td class='rank'><?php echo $team->rank ?></td>
	<td class="num"><?php echo $team->status ?></td>
	<?php if ( $league->show_logo ) : ?>
	<td class="logo">
		<?php if ( $team->logo != '' ) : ?>
		<img src='<?php echo $team->logoURL ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$team->title ?>' />
		<?php endif; ?>
	</td>
	<?php endif; ?>
	
	<td><?php echo $team->title ?></td>
	<td class='num'><?php echo $team->done_matches ?></td>
	<td class='num'><?php echo $team->won_matches ?></td><td class='num'><?php echo $team->draw_matches ?></td><td class='num'><?php echo $team->lost_matches ?></td>
	<td class='num'><?php echo $team->runs['for'] ?></td>
	<td class='num'><?php echo $team->runs['against'] ?></td>
	<td class='num'><?php echo $win_percent ?></td>
	<td class='num'><?php echo $team->gb ?></td>
	<td class='num'><?php echo $team->shutouts ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php endif; ?>
