<?php
/** Widget class for the WordPress plugin LeagueManager
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2008-2009
*/

class LeagueManagerWidget extends LeagueManager
{
	/**
	 * index for matches in widget
	 *
	 * @var array
	 */
	var $match_index = array( 'next' => 0, 'prev' => 0 );


	/**
	 * widget prefix
	 *
	 * @var string 
	 */
	var $prefix = 'leaguemanager-widget';


	/**
	 * initialize
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_action( 'leaguemanager_widget_next_match', array(&$this, 'showNextMatchBox'), 10, 5 );
		add_action( 'leaguemanager_widget_prev_match', array(&$this, 'showPrevMatchBox'), 10, 5 );
		return;
	}
	function LeagueManagerWidget()
	{
		$this->__construct();
	}
	
	
	/**
	 * register widget
	 *
	 * @param none
	 */
	function register()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		
		$options = get_option('leaguemanager_widget');

		$name = __( 'League', 'leaguemanager' );
		$widget_ops = array('classname' => 'leaguemanager_widget', 'description' => __('League results and upcoming matches at a glance', 'leaguemanager') );
		$control_ops = array( 'width' => 200, 'height' => 200, 'id_base' => $this->prefix );

		if(isset($options[0])) unset($options[0]);

		if (!empty($options)) {
			foreach ( array_keys($options) AS $widget_number ) {
				wp_register_sidebar_widget( $this->prefix.'-'.$widget_number, $name , array( &$this, 'display' ), $widget_ops, array('number' => $widget_number));
				wp_register_widget_control( $this->prefix.'-'.$widget_number, $name, array( &$this, 'control' ), $control_ops, array('number' => $widget_number));
			}
		} else {
			wp_register_sidebar_widget( $this->prefix.'-1', $name , array( &$this, 'display' ), $widget_ops, array('number' => -1));
			wp_register_widget_control( $this->prefix.'-1', $name, array( &$this, 'control' ), $control_ops, array('number' => -1));
		}
	}
	
	
	/**
	 * get index for current match
	 *
	 * @param string $type next|prev
	 * @return the index
	 */
	function getMatchIndex( $type )
	{
		return $this->match_index[$type];
	}
	
	
	/**
	 * set index for current match
	 *
	 * @param int $index
	 * @param string $type
	 * @return void
	 */
	function setMatchIndex( $index, $type )
	{
		$this->match_index[$type] = $index;
	}
	
		
	/**
	 * displays widget
	 *
	 * @param $args
	 * @param $widget_args
	 */
	function display( $args, $widget_args = 1 )
	{
		global $lmBridge, $lmShortcodes, $leaguemanager;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract($widget_args, EXTR_SKIP);

		$options = get_option('leaguemanager_widget');
		$options = $options[$number];

		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'widget_number' => $number,
			'league_id' => $options['league'],
			'season' => $options['season'],
		);
		$args = array_merge( $defaults, $args );
		extract( $args , EXTR_SKIP );
	
		$this->league_id = $league_id;
		
		$league = parent::getLeague( $league_id );
		if (empty($season))  $season = $leaguemanager->getSeason($league, false, 'name');

		echo $before_widget . $before_title . $league->title . " " . $season . $after_title;
		
		echo "<div class='leaguemanager_widget'>";
		if ( $options['match_display'] != 'none' ) {
			$show_prev_matches = $show_next_matches = false;
			if ( $options['match_display'] == 'prev' )
				$show_prev_matches = true;
			elseif ( $options['match_display'] == 'next' )
				$show_next_matches = true;
			elseif ( $options['match_display'] == 'all' )
				$show_prev_matches = $show_next_matches = true;
			
			$match_limit = ( intval($options['match_limit']) > 0 ) ? $options['match_limit'] : false;			
			
			if ( $show_next_matches ) {
				echo "<div id='next_matches_".$widget_number."'>";
				do_action( 'leaguemanager_widget_next_match', $widget_number, $this->league_id, $season, $match_limit );
	//			$this->showNextMatchBox($widget_number, $this->league_id, $season, $match_limit);
				echo "</div>";
			}

			if ( $show_prev_matches ) {
				echo "<div id='prev_matches_".$widget_number."'>";
				do_action( 'leaguemanager_widget_prev_match', $widget_number, $this->league_id, $season, $match_limit );
//				$this->showPrevMatchBox($widget_number, $this->league_id, $season, $match_limit);
				echo "</div>";
			}
	
		}
		
		if ( $options['table'] ) {
			$show_logos = ( $options['show_logos'] ) ? true : false;
			echo "<h4 class='standings'>". __( 'Table', 'leaguemanager' ). "</h4>";
			echo $lmShortcodes->showStandings( array('template' => $options['table'], 'league_id' => $league_id, 'season' => $season, 'logo' => $show_logos), true );
		}

		echo "</div>";
		echo $after_widget;
	}


	/**
	 * show next match box
	 *
	 * @param int $widget_number
	 * @param int $league_id
	 * @param int $season
	 * @param int $match_limit
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showNextMatchBox($widget_number, $league_id, $season, $match_limit, $echo = true)
	{
		global $leaguemanager;

		$options = get_option('leaguemanager_widget');
		$options = $options[$widget_number];

		$search = "`league_id` = '".$league_id."' AND `final` = '' AND `season` = '".$season."' AND DATEDIFF(NOW(), `date`) <= 0";
		if ( isset($options['home_only']) )
			$search .= parent::buildHomeOnlyQuery($league_id);
			
		$matches = parent::getMatches( $search, $match_limit );
		if ( $matches ) {
			$this->teams = parent::getTeams( 'league_id = '.$league_id, 'ARRAY' );
			$curr = $this->getMatchIndex('next');
			$match = $matches[$curr];
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			
			$logos = ( 1 == $options['show_logos'] ) ? true : false;
			
			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchBox(".$curr.", \"next\", \"next\", ".$league_id.", \"".$match_limit_js."\", ".$widget_number.", ".$season."); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchBox(".$curr.", \"prev\", \"next\", ".$league_id.", \"".$match_limit_js."\", ".$widget_number.", ".$season."); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
	
			$out = "<div id='next_match_box_".$widget_number."' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Next Match', 'leaguemanager' )."$next_link</h4>";
						
			$out .= "<div class='match' id='match-".$match->id."'>";
				
			if ( $logos && $this->teams[$match->home_team]['logo'] != '' && $this->teams[$match->away_team]['logo'] != '' ) {
				$home_team = "<img src='".$this->teams[$match->home_team]['logo']."' alt=".$this->teams[$match->home_team]['title']." />";
				$away_team = "<img src='".$this->teams[$match->away_team]['logo']."' alt=".$this->teams[$match->away_team]['title']." />";
				$spacer = ' ';
			} else {
				$home_team = $this->teams[$match->home_team]['title'];
				$away_team = $this->teams[$match->away_team]['title'];
				$spacer = ' &#8211; ';
			}
							
			if ( $this->teams[$match->home_team]['website'] != '' )
				$home_team = "<a href='http://".$this->teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $this->teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$this->teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			if ( !isset($match->title) ) $match->title = $home_team . $spacer . $away_team;
			$out .= "<p class='match'>". $match->title."</p>";
							
			if ( !empty($match->match_day) )
			$out .= "<p class='match_day'>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</p>";
			
			$time = ( '00:00' == $match->hour.":".$match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
			$out .= "<p class='date'>".mysql2date($options['date_format'], $match->date)." <span class='time'>".$time."</span> <span class='location'>".$match->location."</span></p>";
			
			$out .= "</div></div>";
		
	
			if ( $echo )
				echo $out;
				
			return $out;
		}
	}
	
	
	/**
	 * show previous match box
	 *
	 * @param int $widget_number
	 * @param int $league_id
	 * @param int $season
	 * @param int $match_limit
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showPrevMatchBox($widget_number, $league_id, $season, $match_limit, $echo = true)
	{
		global $leaguemanager;

		$options = get_option('leaguemanager_widget');
		$options = $options[$widget_number];

		$search = "league_id = '".$league_id."' AND `final` = '' AND `season` = '".$season."' AND DATEDIFF(NOW(), `date`) > 0";
		if ( isset($options['home_only']) )
			$search .= parent::buildHomeOnlyQuery($league_id);

		$matches = parent::getMatches( $search, $match_limit, '`date` DESC' );
		if ( $matches ) {
			$this->teams = parent::getTeams( 'league_id = '.$league_id, 'ARRAY' );
			$curr = $this->getMatchIndex('prev');
			$match = $matches[$curr];
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			
			$logos = ( $options['show_logos'] ) ? true : false;
			
			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchBox(".$curr.", \"next\", \"prev\", ".$league_id.", \"".$match_limit_js."\", ".$widget_number.", ".$season."); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchBox(".$curr.", \"prev\", \"prev\", ".$league_id.", \"".$match_limit_js."\", ".$widget_number.", ".$season."); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
					
			$out = "<div id='prev_match_box_".$widget_number."' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Last Match', 'leaguemanager' )."$next_link</h4>";
										
			
			$out .= "<div class='match' id='match-".$match->id."'>";
			
			$match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;
			$match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;

			if ( $logos && $this->teams[$match->home_team]['logo'] != '' && $this->teams[$match->away_team]['logo'] != '' ) {
				$home_team = "<img src='".$this->teams[$match->home_team]['logo']."' alt=".$this->teams[$match->home_team]['title']." />";
				$away_team = "<img src='".$this->teams[$match->away_team]['logo']."' alt=".$this->teams[$match->away_team]['title']." />";
				$spacer = ' ';
			} else {
				$home_team = $this->teams[$match->home_team]['title'];
				$away_team = $this->teams[$match->away_team]['title'];
				$spacer = ' &#8211; ';
			}

			if ( $this->teams[$match->home_team]['website'] != '' )
				$home_team = "<a href='http://".$this->teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $this->teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$this->teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			if ( !isset($match->title) ) $match->title = $home_team . $spacer . $away_team;
			$out .= "<p class='match'>". $match->title."</p>";
		
			if ( !empty($match->match_day) )
			$out .= "<p class='match_day'>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</p>";
		
			if ( $match->hadPenalty )
				$score = sprintf("%d - %d", $match->penalty['home'], $match->penalty['away'])." "._c( 'o.P.|on penalty', 'leaguemanager' );
			elseif ( $match->hadOvertime )
				$score = sprintf("%d - %d", $match->overtime['home'], $match->overtime['away'])." "._c( 'AET|after extra time', 'leaguemanager' );
			else
				$score = sprintf("%d - %d", $match->home_points, $match->away_points);
			$out .= "<p class='result'>".$score."</p>";
							
			if ( $match->post_id != 0 && $options['match_report'] == 1 )
				$out .=  "<p class='report'><a href='".get_permalink($match->post_id)."'>".__( 'Report', 'leaguemanager' )."&raquo;</a></p>";
					
			$out .= "</div></div>";
		
			if ( $echo )
				echo $out;
			
			return $out;
		}
	}
	
	
	/**
	 * widget control panel
	 *
	 * @param int|array $widget_args
	 */
	function control( $widget_args = 1 )
	{
		global $wp_registered_widgets;
		static $updated = false;
		
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract($widget_args, EXTR_SKIP);
		
		$options = get_option( 'leaguemanager_widget' );
		if(empty($options)) $options = array();

		if( !$updated && !empty($_POST['sidebar']) ) {
			// Tells us what sidebar to put the data in
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			// search unused options
			foreach ( $this_sidebar as $_widget_id ) {
				if(preg_match('/'.$this->prefix.'-([0-9]+)/i', $_widget_id, $match)){
					$widget_number = $match[1];
 
					// $_POST['widget-id'] contain current widgets set for current sidebar
					// $this_sidebar is not updated yet, so we can determine which was deleted
					if(!in_array($match[0], $_POST['widget-id']))
						unset($options[$widget_number]);
				}
			}


			foreach($_POST[$this->prefix] as $widget_number => $values){
				if(empty($values) && isset($options[$widget_number])) // user clicked cancel
					continue;
			
				$options[$widget_number] = $values;	
			}
			update_option('leaguemanager_widget', $options);
			$updated = true;
		}

		/* $number - is dynamic number for multi widget, given by WP
		 * by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
		 * to allow WP generate number automatically
		 */
		if ( $number == -1 ) $number = '%i%';

		// Set options of current widget
		$opts = @$options[$number];

		echo '<div class="leaguemanager_widget_control" id="leaguemanager_widget_control_'.$number.'">';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_league">'.__('League','leaguemanager').'</label>';
		echo '<select size="1" name="'.$this->prefix.'['.$number.'][league]" id="'.$this->prefix.'_'.$number.'_league">';
		foreach ( parent::getLeagues() AS $league ) {
			$selected = ( $opts['league'] == $league->id ) ? ' selected="seleccted"' : '';
			echo '<option value="'.$league->id.'"'.$selected.'>'.$league->title.'</option>';
		}
		echo '</select>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_season">'.__('Season','leaguemanager').'</label><input type="text" name="'.$this->prefix.'['.$number.'][season]" id="'.$this->prefix.'_'.$number.'_season" size="8" value="'.$opts['season'].'" /></p>';

		echo '<p><label for="'.$this->prefix.'_'.$number.'_match_display">'.__('Matches','leaguemanager').'</label>';
		$match_display = array( 'none' => __('Do not show','leaguemanager'), 'prev' => __('Last Matches','leaguemanager'), 'next' => __('Next Matches','leaguemanager'), 'all' => __('Next & Last Matches','leaguemanager') );
		echo '<select size="1" name="'.$this->prefix.'['.$number.'][match_display]" id="'.$this->prefix.'_'.$number.'_match_display">';
		foreach ( $match_display AS $key => $text ) {
			$selected = ( $key == $opts['match_display'] ) ? ' selected="selected"' : '';
			echo '<option value="'.$key.'"'.$selected.'>'.$text.'</option>';
		}
		echo '</select></p>';
		$checked = ( isset($opts['home_only']) ) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->prefix.'['.$number.'][home_only]" id="'.$this->prefix.'_'.$number.'_home_only" value="1"'.$checked.' /><label for="'.$this->prefix.'_'.$number.'_home_only" class="right">'.__('Only own matches','leaguemanager').'</label></p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_match_limit">'.__('Limit','leaguemanager').'</label><input type="text" name="'.$this->prefix.'['.$number.'][match_limit]" id="'.$this->prefix.'_'.$number.'_match_limit" value="'.$opts['match_limit'].'" size="5" /></p>';

		$table_display = array( 'none' => __('Do not show','leaguemanager'), 'compact' => __('Compact Version','leaguemanager'), 'extend' => __('Extend Version','leaguemanager'), 'slim' => __('Slim Version', 'leaguemanager')  );
		echo '<p><label for="'.$this->prefix.'_'.$number.'_table">'.__('Table','leaguemanager').'</label>';
		echo '<select size="1" name="'.$this->prefix.'['.$number.'][table]" id="'.$this->prefix.'_'.$number.'_tablle">';
		foreach ( $table_display AS $key => $text ) {
			$selected = ( $key == $opts['table'] ) ? ' selected="selected"' : '';
			echo '<option value="'.$key.'"'.$selected.'>'.$text.'</option';
		}
		echo '</select></p>';
		$checked = ( $opts['show_logos'] ) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->prefix.'['.$number.'][show_logos]" id="'.$this->prefix.'_'.$number.'_show_logos" value="1"'.$checked.' /><label for="'.$this->prefix.'_'.$number.'_show_logos" class="right">'.__('Show Logos','leaguemanager').'</label></p>';
		$checked = ( $opts['report'] ) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->prefix.'['.$number.'][report]" id="'.$this->prefix.'_'.$number.'_report" value="1"'.$checked.' /><label for="'.$this->prefix.'_'.$number.'_report" class="right">'.__('Link to report','leaguemanager').'</label></p>';
		echo '<p><label for="'.$this->prefix.'_'.$number.'_date_format">'.__('Date Format').'</label><input type="text" id="'.$this->prefix.'_'.$number.'_date_format" name="'.$this->prefix.'['.$number.'][date_format]" value="'.$opts['date_format'].'" size="6" /></p>';
		echo '</div>';
		
		return;
	}
}

?>
