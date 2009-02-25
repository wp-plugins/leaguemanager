<?php
/**
Template page for a single match

The following variables are usable:
	
	$match: contains data of displayed match
	$league: contains data of current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $match ) : ?>

<div class="match" id="match-<?php echo $match->id ?>">
	<h3><?php echo $match->title ?></h3>
	
	<img src="<?php echo $match->homeLogo ?>" alt="" class="alignleft" />
	<img src="<?php echo $match->awayLogo ?>" alt="" class="alignright" />
	
	<?php if ( $match->score == '0:0' ) : ?>
	<p class="matchdate"><?php echo mysql2date(get_option('date_format'), $match->date)." ".$match->start_time." ".$match->location ?></p>
	<?php else : ?>
	<p class="score"><?php echo $match->score ?></p>
	<?php endif; ?>
	
	<br style="clear: both;" />
	<table>
	<tr>
		<th scope="row"><?php _e( 'Goals', 'leaguemanager' ) ?></th>
		<?php foreach ( $match->goals AS $i => $goal ) : ?>
			<?php if ( $i > 0 ) : ?>
			<tr><td>&#160;</td>
			<?php endif; ?>
			<td><?php echo $goal['time'] ?></td>
			<td><?php echo $goal['scorer'] ?></td>
			<td><?php echo $goal['score'] ?></td>
		</tr>
		<?php endforeach; ?>
	</tr>
	</table>
</div>

<?php endif; ?>