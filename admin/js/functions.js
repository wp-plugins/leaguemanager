Leaguemanager.checkAll = function(form) {
   for (i = 0, n = form.elements.length; i < n; i++) {
      if(form.elements[i].type == "checkbox" && !(form.elements[i].getAttribute('onclick',2))) {
         if(form.elements[i].checked == true)
            form.elements[i].checked = false;
         else
            form.elements[i].checked = true;
      }
   }
}


Leaguemanager.checkPointRule = function( forwin, fordraw, forloss ) {
	var rule = document.getElementById('point_rule').value;
	
	// manual rule selected
	if ( rule == 'user' ) {
		new_element_contents = "";
		new_element_contents += "<input type='text' name='forwin' id='forwin' value=" + forwin + " size='2' />";
		new_element_contents += "<input type='text' name='fordraw' id='fordraw' value=" + fordraw + " size='2' />";
		new_element_contents += "<input type='text' name='forloss' id='forloss' value=" + forloss + " size='2' />";
		new_element_contents += "&#160;<span class='setting-description'>" + LeagueManagerAjaxL10n.manualPointRuleDescription + "</span>";
		new_element_id = "point_rule_manual_content";
		new_element = document.createElement('div');
		new_element.id = new_element_id;
		
		document.getElementById("point_rule_manual").appendChild(new_element);
		document.getElementById(new_element_id).innerHTML = new_element_contents;
	} else {
		element_count = document.getElementById("point_rule_manual").childNodes.length;
		if(element_count > 0) {
			target_element = document.getElementById("point_rule_manual_content");
			document.getElementById("point_rule_manual").removeChild(target_element);
		}
  		
	}
	
	return false;
}

Leaguemanager.insertPlayer = function(id, target) {
	tb_remove();
	var player = document.getElementById(id).value
	document.getElementById(target).value = player;
}


Leaguemanager.addGoal = function() {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "goal_"+new_element_number;
  parent_id = "goals";

  new_element_contents = "";
  new_element_contents += "<td><input type='text' size='10' name='stats[goals]["+new_element_number+"][time]' value='' /></td>\n\r";
  new_element_contents += "<td><input type='text' size='20' name='stats[goals]["+new_element_number+"][scorer]' id='goal_scorer_"+new_element_number+"' value='' />";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true ) {
	new_element_contents += "<div id='goal_scorer_box_"+new_element_number+"' style='overflow: auto; display: none;'>";
	new_element_contents += "<select style='display: block; margin: 0.5em auto;' id='goal_scorer_roster_"+new_element_number+"'>" + lmTeamRoster + "</select>";
	new_element_contents += "<div style='text-align: center; margin-top: 1em;'><input type='button' value='"+LeagueManagerAjaxL10n.Insert+"' class='button-secondary' onClick='Leaguemanager.insertPlayer(\"goal_scorer_roster_"+new_element_number+"\", \"goal_scorer_"+new_element_number+"\"); return false;' />&#160;<input type='button' value='"+LeagueManagerAjaxL10n.Cancel+"' class='button-secondary' onClick='tb_remove();' /></div>";
	new_element_contents += "</div>";
	new_element_contents += "<span class='team_roster'><a class='thickbox' href='#TB_inline&width=300&height=100&inlineId=goal_scorer_box_"+new_element_number+"' title='"+LeagueManagerAjaxL10n.AddPlayerFromRoster+"' style='display: inline;'><img src='"+LeagueManagerAjaxL10n.pluginUrl+"/admin/icons/player.png' border='0' alt='"+LeagueManagerAjaxL10n.InsertPlayer+"' /></a></span>";
  }
  new_element_contents += "</td>\n\r";
  new_element_contents += "<td><input type='text' size='5' name='stats[goals]["+new_element_number+"][standing]' value='' /></td>\n\r";
  new_element_contents += "<td  style='text-align: center; width: 12px; vertical-align: middle;'><a class='image_link' href='#' onclick='return Leaguemanager.removeField(\""+new_element_id+"\", \""+parent_id+"\");'><img src='../wp-content/plugins/leaguemanager/images/trash.gif' alt='" + LeagueManagerAjaxL10n.Delete + "' title='" + LeagueManagerAjaxL10n.Delete + "' /></a></td>\n\r";

  new_element = document.createElement('tr');
  new_element.id = new_element_id;

  document.getElementById(parent_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;

  Leaguemanager.reInit();

  return false;
}


Leaguemanager.addCard = function() {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "card_"+new_element_number;
  parent_id = "cards";

  new_element_contents = "";
  new_element_contents += "<td><input type='text' size='10' name='stats[cards]["+new_element_number+"][time]' value='' /></td>\n\r";
  new_element_contents += "<td><input type='text' size='20' name='stats[cards]["+new_element_number+"][player]' id='card_player_"+new_element_number+"' value='' />\n\r";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true ) {
	new_element_contents += "<div id='cards_player_box_"+new_element_number+"' style='overflow: auto; display: none;'>";
	new_element_contents += "<select style='display: block; margin: 0.5em auto;' id='card_player_roster_"+new_element_number+"'>" + lmTeamRoster + "</select>";
	new_element_contents += "<div style='text-align: center; margin-top: 1em;'><input type='button' value='"+LeagueManagerAjaxL10n.Insert+"' class='button-secondary' onClick='Leaguemanager.insertPlayer(\"card_player_roster_"+new_element_number+"\", \"card_player_"+new_element_number+"\"); return false;' />&#160;<input type='button' value='"+LeagueManagerAjaxL10n.Cancel+"' class='button-secondary' onClick='tb_remove();' /></div>";
	new_element_contents += "</div>";
	new_element_contents += "<span class='team_roster'><a class='thickbox' href='#TB_inline&width=300&height=100&inlineId=cards_player_box_"+new_element_number+"' title='"+LeagueManagerAjaxL10n.AddPlayerFromRoster+"' style='display: inline;'><img src='"+LeagueManagerAjaxL10n.pluginUrl+"/admin/icons/player.png' border='0' alt='"+LeagueManagerAjaxL10n.InsertPlayer+"' /></a></span>";
  }
  new_element_contents += "</td>\n\r",
  new_element_contents += "<td><select size='1' name='stats[cards]["+new_element_number+"][type]'><option value='yellow'>"+LeagueManagerAjaxL10n.Yellow+"</option><option value='red'>"+LeagueManagerAjaxL10n.Red+"</option><option value='yellow-red'>"+LeagueManagerAjaxL10n.Yellow_Red+"</option></select></td>\n\r";
  new_element_contents += "<td  style='text-align: center; width: 12px; vertical-align: middle;'><a class='image_link' href='#' onclick='return Leaguemanager.removeField(\""+new_element_id+"\", \""+parent_id+"\");'><img src='../wp-content/plugins/leaguemanager/images/trash.gif' alt='" + LeagueManagerAjaxL10n.Delete + "' title='" + LeagueManagerAjaxL10n.Delete + "' /></a></td>\n\r";

  new_element = document.createElement('tr');
  new_element.id = new_element_id;

  document.getElementById(parent_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;

  Leaguemanager.reInit();

  return false;
}


Leaguemanager.addPlayerExchange = function(match_id) {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "exchange_"+new_element_number;
  parent_id = "exchanges";
  
  new_element_contents = "";
  new_element_contents += "<td><input type='text' size='10' name='stats[exchanges]["+new_element_number+"][time]' value='' /></td>\n\r";
  new_element_contents += "<td><input type='text' size='20' name='stats[exchanges]["+new_element_number+"][in]' id='exchange_in_"+new_element_number+"' value='' />\n\r";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true ) {
	new_element_contents += "<div id='exchange_in_box_"+new_element_number+"' style='overflow: auto; display: none;'>";
	new_element_contents += "<select style='display: block; margin: 0.5em auto;' id='exchange_in_roster_"+new_element_number+"'>" + lmTeamRoster + "</select>";
	new_element_contents += "<div style='text-align: center; margin-top: 1em;'><input type='button' value='"+LeagueManagerAjaxL10n.Insert+"' class='button-secondary' onClick='Leaguemanager.insertPlayer(\"exchange_in_roster_"+new_element_number+"\", \"exchange_in_"+new_element_number+"\"); return false;' />&#160;<input type='button' value='"+LeagueManagerAjaxL10n.Cancel+"' class='button-secondary' onClick='tb_remove();' /></div>";
	new_element_contents += "</div>";
	new_element_contents += "<span class='team_roster'><a class='thickbox' href='#TB_inline&width=300&height=100&inlineId=exchange_in_box_"+new_element_number+"' title='"+LeagueManagerAjaxL10n.AddPlayerFromRoster+"' style='display: inline;'><img src='"+LeagueManagerAjaxL10n.pluginUrl+"/admin/icons/player.png' border='0' alt='"+LeagueManagerAjaxL10n.InsertPlayer+"' /></a></span>";
  }
  new_element_contents += "</td>\n\r";
  new_element_contents += "<td><input type='text' size='20' name='stats[exchanges]["+new_element_number+"][out]' id='exchange_out_"+new_element_number+"' value='' />\n\r";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true ) {
	new_element_contents += "<div id='exchange_out_box_"+new_element_number+"' style='overflow: auto; display: none;'>";
	new_element_contents += "<select style='display: block; margin: 0.5em auto;' id='exchange_out_roster_"+new_element_number+"'>" + lmTeamRoster + "</select>";
	new_element_contents += "<div style='text-align: center; margin-top: 1em;'><input type='button' value='"+LeagueManagerAjaxL10n.Insert+"' class='button-secondary' onClick='Leaguemanager.insertPlayer(\"exchange_out_roster_"+new_element_number+"\", \"exchange_out_"+new_element_number+"\"); return false;' />&#160;<input type='button' value='"+LeagueManagerAjaxL10n.Cancel+"' class='button-secondary' onClick='tb_remove();' /></div>";
	new_element_contents += "</div>";
	new_element_contents += "<span class='team_roster'><a class='thickbox' href='#TB_inline&width=300&height=100&inlineId=exchange_out_box_"+new_element_number+"' title='"+LeagueManagerAjaxL10n.AddPlayerFromRoster+"' style='display: inline;'><img src='"+LeagueManagerAjaxL10n.pluginUrl+"/admin/icons/player.png' border='0' alt='"+LeagueManagerAjaxL10n.InsertPlayer+"' /></a></span>";
  }
  new_element_contents += "</td>";
  new_element_contents += "<td style='text-align: center; width: 12px; vertical-align: middle;'><a class='image_link' href='#' onclick='return Leaguemanager.removeField(\""+new_element_id+"\", \""+parent_id+"\");'><img src='../wp-content/plugins/leaguemanager/images/trash.gif' alt='" + LeagueManagerAjaxL10n.Delete + "' title='" + LeagueManagerAjaxL10n.Delete + "' /></a></td>\n\r";

  new_element = document.createElement('tr');
  new_element.id = new_element_id;

  document.getElementById(parent_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;

  Leaguemanager.reInit();

  return false;
}


Leaguemanager.removeField = function(id, parent_id) {
  element_count = document.getElementById(parent_id).childNodes.length;
  if(element_count > 1) {
    target_element = document.getElementById(id);
    document.getElementById(parent_id).removeChild(target_element);
  }
  return false;
}


Leaguemanager.reInit = function() {
	tb_init('a.thickbox, area.thickbox, input.thickbox');
}


/*
*  Color Picker
*/
function PopupWindow_setSize(width,height) {
	this.width = 360;
	this.height = 210;
}

function syncColor(id, inputID, color) {
	var link = document.getElementById(id);
	if (color == '')
		color='white';
		
	link.style.background = color;
	link.style.color = color;
}

function pickColor(color) {
	if (ColorPicker_targetInput==null) {
		alert("Target Input is null, which means you either didn't use the 'select' function or you have no defined your own 'pickColor' function to handle the picked color!");
		return;
	}
	ColorPicker_targetInput.value = color;
	syncColor("pick_" + ColorPicker_targetInput.id, ColorPicker_targetInput.id, color);
}
var cp = new ColorPicker('window'); // Popup window
