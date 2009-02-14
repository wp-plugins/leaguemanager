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
	var $matches = array( 'next' => 0, 'prev' => 0 );


	/**
	 * initialize
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		$this->loadOptions();
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
		
		// Add options
		add_option( 'leaguemanager_widget', array(), 'Leaguemanager Widget Options', 'yes' );
		foreach ( $this->getActiveLeagues() AS $league_id => $league ) {
			$name = __( 'League', 'leaguemanager' ) .' - '. $league['title'];
			$widget_id = sanitize_title($name);
			$widget_ops = array('classname' => 'widget_leaguemanager', 'description' => __('League results and upcoming matches at a glance', 'leaguemanager') );
			wp_register_sidebar_widget( sanitize_title($name), $name , array( &$this, 'display' ), $widget_ops );
			wp_register_widget_control( sanitize_title($name), $name, array( &$this, 'control' ), array('width' => 250, 'height' => 200), array( 'league_id' => $league_id, 'widget_id' => $widget_id ) );
		
			$this->options[$widget_id] = $league_id;
		}
		update_option( 'leaguemanager_widget', $this->options );
	}
	
	
	/**
	 * load Options
	 *
	 * @param none
	 * @return void
	 */
	function loadOptions()
	{
		$this->options = get_option( 'leaguemanager_widget' );
	}
	
	
	/**
	 * get index for current match
	 *
	 * @param string $type next|prev
	 * @return the index
	 */
	function getMatchIndex( $type )
	{
		return $this->matches[$type];
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
		$this->matches[$type] = $index;
	}
	
	
	/**
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function display( $args )
	{
		global $leaguemanager_loader, $leaguemanager;
		
		$widget_id = $args['widget_id'];
		
		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'league_id' => $this->options[$widget_id],
			
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		$this->league_id = $league_id;
		$options = $this->options[$league_id];
		
		$league = parent::getLeague( $league_id );
		echo $before_widget . $before_title . $league->title . $after_title;
		
		echo "<div class='leaguemanager_widget'>";
		if ( $options['match_display'][0] != 'none' ) {
			$show_prev_matches = $show_next_matches = false;
			if ( $options['match_display'][0] == 'prev_matches' )
				$show_prev_matches = true;
			elseif ( $options['match_display'][0] == 'next_matches' )
				$show_next_matches = true;
			elseif ( $options['match_display'][0] == 'all' )
				$show_prev_matches = $show_next_matches = true;
			
			$match_limit = ( $options['match_display'][1] == 'all' && $options['match_limit'] > 0 ) ? $options['match_limit'] : false;			
			
			if ( $show_next_matches ) {
				echo "<div id='next_matches'>";
				$this->showNextMatchBox($this->league_id, $match_limit);
				echo "</div>";
			}

			if ( $show_prev_matches ) {
				echo "<div id='prev_matches'>";
				$this->showPrevMatchBox($this->league_id, $this->match_limit);
				echo "</div>";
			}
	
		}
		
		if ( 1 == $options['table_display'] ) {
			$show_logo = ( $options['show_logo'] == 1 ) ? true : false;
			echo "<h4 class='standings'>". __( 'Table', 'leaguemanager' ). "</h4>";
			echo $leaguemanager_loader->shortcodes->showStandings( array('league_id' => $league_id, 'mode' => 'compact', 'logo' => $show_logo) );
		}
		//if ( $options['info'] AND '' != $options['info'] )
		//	echo "<li class='info'><a href='".get_permalink( $options['info'] )."'>".__( 'More Info', 'leaguemanager' )."</a></li>";

		echo "</div>";
		echo $after_widget;
	}


	/**
	 * show next match box
	 *
	 * @param int $league_id
	 * @param int $match_limit
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showNextMatchBox( $league_id, $match_limit, $echo = true)
	{
		$options = $this->options[$league_id];
		$home_only = ( 'home' == $options['match_display'][1] ) ? true : false;
		
		$matches = parent::getMatches( "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) < 0", $match_limit );
		if ( $matches ) {
			$this->teams = parent::getTeams( $league_id, 'ARRAY' );
			$curr = $this->getMatchIndex('next');
			$match = $matches[$curr];
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			
			
			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"next\", \"next\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"prev\", \"next\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
	
			$out = "<div id='next_match_box' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Next Match', 'leaguemanager' )."$next_link</h4>";
						
			$out .= "<div class='match' id='match-".$match->id."'>";
				
			if ( $this->teams[$match->home_team]['logo'] != '' && $this->teams[$match->away_team]['logo'] != '' ) {
				$home_team = "<img src='".parent::getImageUrl($this->teams[$match->home_team]['logo'])."' alt=".$this->teams[$match->home_team]['short_title']." />";
				$away_team = "<img src='".parent::getImageUrl($this->teams[$match->away_team]['logo'])."' alt=".$this->teams[$match->away_team]['short_title']." />";
				$spacer = ' ';
			} else {
				$home_team = $this->teams[$match->home_team]['short_title'];
				$away_team = $this->teams[$match->away_team]['short_title'];
				$spacer = ' &#8211; ';
			}
							
			if ( $this->teams[$match->home_team]['website'] != '' )
				$home_team = "<a href='http://".$this->teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $this->teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$this->teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			$out .= "<p class='match'>". $home_team . $spacer . $away_team."</p>";
							
			$out .= "<p class='match_day'><small>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</small></p>";
			
			$time = ( '00:00' == $match->hour.":".$match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
			$out .= "<p class='date'><small>".mysql2date($options['date_format'], $match->date)." <span class='time'>".$time."</span></small></p>";
			
			$out .= "</div></div>";
		
	
			if ( $echo )
				echo $out;
				
			return $out;
		}
	}
	
	
	/**
	 * show previous match box
	 *
	 * @param int $league_id
	 * @param int $match_limit
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showPrevMatchBox($league_id, $match_limit, $echo = true)
	{
		$options = $this->options[$league_id];
		$home_only = ( 'home' == $options['match_display'][1] ) ? true : false;
		
		$matches = parent::getMatches( "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) > 0", $match_limit );
		if ( $matches ) {
			$this->teams = parent::getTeams( $league_id, 'ARRAY' );
			$curr = $this->getMatchIndex('prev');
			$match = $matches[$curr];
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			
			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"next\", \"prev\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"prev\", \"prev\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
					
			$out = "<div id='prev_match_box' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Last Match', 'leaguemanager' )."$next_link</h4>";
										
			
			$out .= "<div class='match' id='match-".$match->id."'>";
				
			if ( $this->teams[$match->home_team]['logo'] != '' && $this->teams[$match->away_team]['logo'] != '' ) {
				$home_team = "<img src='".parent::getImageUrl($this->teams[$match->home_team]['logo'])."' alt=".$this->teams[$match->home_team]['short_title']." />";
				$away_team = "<img src='".parent::getImageUrl($this->teams[$match->away_team]['logo'])."' alt=".$this->teams[$match->away_team]['short_title']." />";
				$spacer = ' ';
			} else {
				$home_team = $this->teams[$match->home_team]['short_title'];
				$away_team = $this->teams[$match->away_team]['short_title'];
				$spacer = ' &#8211; ';
			}

			if ( $this->teams[$match->home_team]['website'] != '' )
				$home_team = "<a href='http://".$this->teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $this->teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$this->teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			$out .= "<p class='match'>". $home_team . $spacer . $away_team."</p>";
			
			$out .= "<p class='match_day'><small>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</small></p>";
							
			$out .= "<p class='result'>".sprintf("%d - %d", $match->home_points, $match->away_points)."</p>";
							
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
	 * @param none
	 */
	function control( $args )
	{
		extract( $args );
		
		$options = get_option( 'leaguemanager_widget' );
		$options[$widget_id] = $league_id;
		update_option( 'leaguemanager_widget', $options );
		
		echo '<p>'.sprintf(__( "The Widget Settings are controlled via the <a href='%s'>League Settings</a>", 'leaguemanager'), 'admin.php?page=leaguemanager/settings.php&league_id='.$league_id).'</p>';
	}
	
	
	/**
	 * get all active leagues
	 *
	 * @param none
	 * @return array
	 */
	function getActiveLeagues()
	{
		return ( parent::getLeagues( false, 'WHERE active = 1' ) );
	}
}

?>