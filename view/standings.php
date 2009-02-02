<table class="leaguemanager standingstable" summary="" title="<?php _e( 'Standings', 'leaguemanager' ) .' '.$this->getLeagueTitle($league_id) ?>">
<tr>
	<th class="num">&#160;</th>
	
	<?php if ( 1 == $preferences->show_logo ) : ?>
	<th class="logo">&#160;</th>
	<?php endif; ?>
	
	<th><?php _e( 'Club', 'leaguemanager' ) ?></th>
	
	<?php if ( 'extend' == $mode ) : ?>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th><th class="num"><?php _e( 'W','leaguemanager' ) ?></th><th class="num"><?php _e( 'T','leaguemanager' ) ?></th><th class="num"><?php _e( 'L','leaguemanager' ) ?></th>
	<?php endif; ?>
	
	<?php if ( 'widget' != $mode ) : ?>
	<th class="num"><?php if ( $this->isGymnasticsLeague( $league_id ) ) _e('AP','leaguemanager'); else _e('Goals','leaguemanager'); ?></th>
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
?>
<tr class='<?php echo implode(' ', $class)?>'>
	<td class='rank'><?php echo $rank ?></td>
	
	<?php if ( 1 == $preferences->show_logo ) : ?>
	<td class="logo">
		<?php if ( $team['logo'] != '' ) : ?>
		<img src='<?php echo $this->getImageUrl($team['logo']) ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$team['title'] ?>' />
		<?php endif; ?>
	</td>
	<?php endif; ?>
	
	<td><?php echo $team_title ?></td>
	
	<?php if ( 'extend' == $mode ) : ?>
	<td class='num'><?php echo $team['done_matches'] ?></td><td class='num'><?php echo $team['won_matches'] ?></td><td class='num'><?php echo $team['draw_matches'] ?></td><td class='num'><?php echo $team['lost_matches'] ?></td>
	<?php endif; ?>
	
	<?php if ( 'widget' != $mode ) : ?>
	<td class='num'><?php echo $team['points2']['plus'] ?>:<?php echo $team['points2']['minus'] ?></td><td class='num'><?php echo $team['diff'] ?></td>
	<?php endif; ?>
	<?php if ( $this->isGymnasticsLeague( $league_id ) ) : ?>
	<td class='num'><?php echo $team['points']['plus']?>:<?php echo $team['points']['minus'] ?></td>
	<?php else : ?>
	<td class='num'><?php echo $team['points']['plus'] ?></td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>