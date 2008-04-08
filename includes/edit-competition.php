<?php $this->print_breadcrumb_navi( $league_id ) ?>
<div class="wrap">
<div class="narrow">
	<h2><?php _e( $form_title,'leaguemanager' ) ?></h2>
	<h3><?php echo $this->leagues[$league_id]['title'] ?></h3>
	
	<form id="wp_league" action="admin.php?page=leaguemanager.php&amp;show_league=<?php echo $league_id?>" method="post">
		<label for="date"><?php _e('Date', 'leaguemanager') ?>:</label>
		<select size="1" name="competition_day">
		<?php for ( $day = 1; $day <= 31; $day++ ) : ?>
			<?php if ( $day == $competition_day ) $selected = ' selected="selected"'; else $selected = ''; ?>
			<option value="<?php echo $day ?>"<?php echo $selected ?>><?php echo $day ?></option>
		<?php endfor; ?>
		</select>
		<select size="1" name="competition_month">
		<?php foreach ( $this->months AS $key => $month ) : ?>
			<?php if ( $key == $competition_month ) $selected = ' selected="selected"'; else $selected = ''; ?>
			<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $month ?></option>
		<?php endforeach; ?>
		</select>
		<select size="1" name="competition_year">
		<?php for ( $year = date("Y"); $year <= date("Y")+1; $year++ ) : ?>
			<?php if ( $year == $competition_year ) $selected = ' selected="selected"'; else $selected = ''; ?>
			<option value="<?php echo $year ?>"<?php echo $selected ?>><?php echo $year ?></option>
		<?php endfor; ?>
		</select>
		<br />
		
		<label for="competitor"><?php _e( 'Opponent', 'leaguemanager' ) ?>:</label>
		<select size="1" name="competitor">
		<?php $teams = $this->get_teams("league_id = '".$league_id."'", 'id ASC'); ?>
		<?php foreach ( $teams AS $team ) : ?>
			<?php if( 0 == $team->home ) : ?>
				<?php if ( $team->id == $competitor ) $selected = 'selected="selected"'; else $selected = ''; ?>
			<option value="<?php echo $team->id ?>"<?php echo $selected?>><?php echo $team->title ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
		</select><br />
	
		<label for="home"><?php _e( 'Home', 'leaguemanager' ) ?></label>
		<input type="checkbox" name="home" id="home" value="1"<?php echo $home_selection ?> /><br />
		
		<label for="location"><?php _e( 'Location','leaguemanager' ) ?></label><input type="text" name="location" id="location" size="20" value="<?php echo $location ?>" size="30" /><br />
		<label for="begin"><?php _e( 'Begin','leaguemanager' ) ?></label>
		<select size="1" name="begin_hour">
		<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
			<?php if ( $hour == $begin_hour ) $selected = 'selected="selected"'; else $selected = ''; ?>
			<option value="<?php echo $hour ?>"<?php echo $selected ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
		<?php endfor; ?>
		</select>
		<select size="1" name="begin_minutes">
		<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
			<?php if ( $minute == $begin_minutes ) $selected = 'selected="selected"'; else $selected = ''; ?>
			<?php if ( 0 == $minute % 15 ) : ?>
			<option value="<?php echo $minute ?>"<?php echo $selected ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
			<?php endif; ?>
		<?php endfor; ?>
		</select>
		
		<input type="hidden" name="cid" value="<?php echo $competition_id ?>" />
		<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
		<input type="hidden" name="updateLeague" value="competition" />
		
		<p class="submit"><input type="submit" name="addCompetition" value="<?php _e( $form_title,'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>
</div> 
