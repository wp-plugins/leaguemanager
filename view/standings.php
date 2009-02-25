<?php
/**
Template page for the standings table

The following variables are usable:
	
	$league: contains data about the league
	$teams: contains all teams of current league
	$mode: can be either 'extend', 'compact' or 'widget' (should only be used in Widget) to control what columns are displayed
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $teams ) : ?>

<table class="leaguemanager standingstable" summary="" title="<?php echo __( 'Standings', 'leaguemanager' ) .' '.$league->title ?>">
<tr>
	<th class="num"><?php echo _c( 'Pos|Position', 'leaguemanager' ) ?></th>
	
	<?php if ( $league->show_logo ) : ?>
	<th class="logo">&#160;</th>
	<?php endif; ?>
	
	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
	
	<?php if ( 'extend' == $mode ) : ?>
	<th class="num"><?php echo _c( 'W|Won','leaguemanager' ) ?></th><th class="num"><?php echo _c( 'T|Tie','leaguemanager' ) ?></th><th class="num"><?php echo _c( 'L|Lost','leaguemanager' ) ?></th>
	<?php endif; ?>
	
	<?php if ( 'extend' == $mode ) : ?>
	<th class="num"><?php if ( $league->isGymnastics ) echo _c('AP|apparatus points','leaguemanager'); else _e('Goals','leaguemanager'); ?></th>
	<?php endif; ?>
	<th class="num"><?php _e( 'Diff', 'leaguemanager' ) ?></th>
	<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
</tr>
<?php if ( $teams ) : ?>
<?php foreach( $teams AS $team ) : ?>

<tr class='<?php echo $team->class ?>'>
	<td class='rank'><?php echo $team->rank ?></td>
	
	<?php if ( $league->show_logo ) : ?>
	<td class="logo">
		<?php if ( $team->logo != '' ) : ?>
		<img src='<?php echo $team->logoURL ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$team->title ?>' />
		<?php endif; ?>
	</td>
	<?php endif; ?>
	
	<td><?php echo $team->title ?></td>
	<td class='num'><?php echo $team->done_matches ?></td>
	
	<?php if ( 'extend' == $mode ) : ?>
	<td class='num'><?php echo $team->won_matches ?></td><td class='num'><?php echo $team->draw_matches ?></td><td class='num'><?php echo $team->lost_matches ?></td>
	<?php endif; ?>
	
	<?php if ( 'extend' == $mode ) : ?>
	<td class='num'><?php echo $team->points2 ?></td>
	<?php endif; ?>
	<td class='num'><?php echo $team->diff ?></td>
	<td class='num'><?php echo $team->points ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php endif; ?>