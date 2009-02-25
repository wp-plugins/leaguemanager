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
	if ( rule == 6 ) {
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


Leaguemanager.addGoal = function(match_id) {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "shot_goal_"+new_element_number;
  parent_id = "goals_" + match_id;

  new_element_contents = "";
  new_element_contents += "<td><input type='text' size='10' name='goal_time_"+match_id+"' value='' /></td>\n\r";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true )
  	new_element_contents += "<td><select name='goal_scorer_"+match_id+"'>" + lmTeamRoster + "</select></td>\n\r";
  else
  	new_element_contents += "<td><input type='text' size='20' name='goal_scorer_"+match_id+"' value='' /></td>\n\r";
  new_element_contents += "<td><input type='text' size='5' name='goal_standing_"+match_id+"' value='' /></td>\n\r";
  new_element_contents += "<td  style='text-align: center; width: 12px; vertical-align: middle;'><a class='image_link' href='#' onclick='return Leaguemanager.removeField(\""+new_element_id+"\", \""+parent_id+"\");'><img src='../wp-content/plugins/leaguemanager/images/trash.gif' alt='" + LeagueManagerAjaxL10n.Delete + "' title='" + LeagueManagerAjaxL10n.Delete + "' /></a></td>\n\r";

  new_element = document.createElement('tr');
  new_element.id = new_element_id;

  document.getElementById(parent_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;
  return false;
}


Leaguemanager.addCard = function(match_id) {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "card_"+new_element_number;
  parent_id = "cards_" + match_id;

  new_element_contents = "";
  new_element_contents += "<td><input type='text' size='10' name='card_time_"+match_id+"' value='' /></td>\n\r";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true )
  	new_element_contents += "<td><select name='card_player_"+match_id+"'>" + lmTeamRoster + "</select></td>\n\r";
  else
  	new_element_contents += "<td><input type='text' size='20' name='card_player_"+match_id+"' value='' /></td>\n\r";
  new_element_contents += "<td><select size='1' name='card_type_"+match_id+"'><option value='yellow'>"+LeagueManagerAjaxL10n.Yellow+"</option><option value='red'>"+LeagueManagerAjaxL10n.Red+"</option><option value='yellow-red'>"+LeagueManagerAjaxL10n.Yellow_Red+"</option></td>\n\r";
  new_element_contents += "<td  style='text-align: center; width: 12px; vertical-align: middle;'><a class='image_link' href='#' onclick='return Leaguemanager.removeField(\""+new_element_id+"\", \""+parent_id+"\");'><img src='../wp-content/plugins/leaguemanager/images/trash.gif' alt='" + LeagueManagerAjaxL10n.Delete + "' title='" + LeagueManagerAjaxL10n.Delete + "' /></a></td>\n\r";

  new_element = document.createElement('tr');
  new_element.id = new_element_id;

  document.getElementById(parent_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;
  return false;
}


Leaguemanager.addPlayerExchange = function(match_id) {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "exchange_"+new_element_number;
  parent_id = "exchanges_" + match_id;
  
  new_element_contents = "";
  new_element_contents += "<td><input type='text' size='10' name='exchange_time_"+match_id+"' value='' /></td>\n\r";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true )
  	new_element_contents += "<td><select name='exchange_in_"+match_id+"'>" + lmTeamRoster + "</select></td>\n\r";
  else
  	new_element_contents += "<td><input type='text' size='20' name='exchange_in_"+match_id+"' value='' /></td>\n\r";
  if ( typeof(lmBridge) != 'undefined' && lmBridge == true )
	new_element_contents += "<td><select name='exchange_out_"+match_id+"'>" + lmTeamRoster + "</select></td>\n\r";
  else
  	new_element_contents += "<td><input type='text' size='20' name='exchange_out_"+match_id+"' value='' /></td>\n\r";
  new_element_contents += "<td  style='text-align: center; width: 12px; vertical-align: middle;'><a class='image_link' href='#' onclick='return Leaguemanager.removeField(\""+new_element_id+"\", \""+parent_id+"\");'><img src='../wp-content/plugins/leaguemanager/images/trash.gif' alt='" + LeagueManagerAjaxL10n.Delete + "' title='" + LeagueManagerAjaxL10n.Delete + "' /></a></td>\n\r";

  new_element = document.createElement('tr');
  new_element.id = new_element_id;

  document.getElementById(parent_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;
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