<?php
/**
Template page for the standings table in extended form (default)

The following variables are usable:
	
	$league: contains data about the league
	$teams: contains all teams of current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>

<?php if ( isset($_GET['team']) && !$widget ) : ?>
	<?php leaguemanager_team($_GET['team']); ?>
<?php else : ?>

<?php if ( $teams ) : ?>

<table class="leaguemanager standingstable" summary="" title="<?php _e( 'Standings', 'leaguemanager' ) .' '.$league->title ?>">
<tr>
	<th class="num"><?php echo _e( 'Pos', 'leaguemanager' ) ?></th>
	<th class="num">&#160;</th>
	<?php if ( $league->show_logo ) : ?>
	<th class="logo">&#160;</th>
	<?php endif; ?>
	
	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<?php if ( isset($league->standings['pld']) && 1 == $league->standings['pld'] ) : ?>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( isset($league->standings['won']) && 1 == $league->standings['won'] ) : ?>
	<th class="num"><?php echo _e( 'W','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( isset($league->standings['tie']) && 1 == $league->standings['tie'] ) : ?>
	<th class="num"><?php echo _e( 'T','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( isset($league->standings['lost']) && 1 == $league->standings['lost'] ) : ?>
	<th class="num"><?php echo _e( 'L','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php do_action( 'leaguemanager_standings_header_'.$league->sport ) ?>
	<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
</tr>
<?php if ( $teams ) : ?>
<?php foreach( $teams AS $team ) : ?>

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
	<?php if ( isset($league->standings['pld']) && 1 == $league->standings['pld'] ) : ?>
	<td class='num'><?php echo $team->done_matches ?></td>
	<?php endif; ?>
	<?php if ( isset($league->standings['won']) && 1 == $league->standings['won'] ) : ?>
	<td class='num'><?php echo $team->won_matches ?></td>
	<?php endif; ?>
	<?php if ( isset($league->standings['tie']) && 1 == $league->standings['tie'] ) : ?>
	<td class='num'><?php echo $team->draw_matches ?></td>
	<?php endif; ?>
	<?php if ( isset($league->standings['lost']) && 1 == $league->standings['lost'] ) : ?>
	<td class='num'><?php echo $team->lost_matches ?></td>
	<?php endif; ?>
	<?php do_action( 'leaguemanager_standings_columns_'.$league->sport, $team, $league->point_rule ) ?>
	<td class='num'><?php echo $team->points ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php endif; ?>
<?php endif; ?>