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
<table class="leaguemanager standingstable" summary="" title="<?php _e( 'Standings', 'leaguemanager' ) .' '.$league->title ?>">
<tr>
	<th class="num"><?php _e( 'Pos', 'leaguemanager' ) ?></th>
	
	<?php if ( $league->show_logo ) : ?>
	<th class="logo">&#160;</th>
	<?php endif; ?>
	
	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
	
	<?php if ( 'extend' == $mode ) : ?>
	<th class="num"><?php _e( 'W','leaguemanager' ) ?></th><th class="num"><?php _e( 'T','leaguemanager' ) ?></th><th class="num"><?php _e( 'L','leaguemanager' ) ?></th>
	<?php endif; ?>
	
	<?php if ( 'widget' != $mode ) : ?>
	<?php if ( 'extend' == $mode ) : ?>
	<th class="num"><?php if ( $league->isGymnastics ) _e('AP','leaguemanager'); else _e('Goals','leaguemanager'); ?></th>
	<?php endif; ?>
	<th class="num"><?php _e( 'Diff', 'leaguemanager' ) ?></th>
	<?php endif; ?>
	<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
</tr>
<?php if ( count($teams) > 0 ) : $rank = 0; $class = array(); ?>
<?php foreach( $teams AS $team ) : ?>
<?php $rank++;
      $class = ( in_array('alternate', $class) ) ? array() : array('alternate');
      // Add divider class
      if ( $rank == 1 || $rank == 3 || count($teams)-$rank == 3 || count($teams)-$rank == 1) $class[] =  'divider';
      $team_title = ( 'widget' == $mode ) ? $team['short_title'] : $team['title'];
      if ( 1 == $team['home'] ) $team_title = '<strong>'.$team_title.'</strong>';
      if ( $team['website'] != '' ) $team_title = '<a href="http://'.$team['website'].'" target="_blank">'.$team_title.'</a>';
?>
<tr class='<?php echo implode(' ', $class)?>'>
	<td class='rank'><?php echo $rank ?></td>
	
	<?php if ( $league->show_logo ) : ?>
	<td class="logo">
		<?php if ( $team['logo'] != '' ) : ?>
		<img src='<?php echo parent::getThumbnailUrl($team['logo']) ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$team['title'] ?>' />
		<?php endif; ?>
	</td>
	<?php endif; ?>
	
	<td><?php echo $team_title ?></td>
	<td class='num'><?php echo $team['done_matches'] ?></td>
	
	<?php if ( 'extend' == $mode ) : ?>
	<td class='num'><?php echo $team['won_matches'] ?></td><td class='num'><?php echo $team['draw_matches'] ?></td><td class='num'><?php echo $team['lost_matches'] ?></td>
	<?php endif; ?>
	
	<?php if ( 'widget' != $mode ) : ?>
	<?php if ( 'extend' == $mode ) : ?>
	<td class='num'><?php echo $team['points2']['plus'] ?>:<?php echo $team['points2']['minus'] ?></td>
	<?php endif; ?>
	<td class='num'><?php echo $team['diff'] ?></td>
	<?php endif; ?>
	<td class='num'><?php printf($league->point_format, $team['points']['plus'], $team['points']['minus']) ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>