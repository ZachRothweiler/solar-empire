<?php

require_once('inc/user.inc.php');

$filename = 'options.php';
$error_str = "";

// change player options
if(isset($player_op) && $player_op == 1){
	$error_str .= "You can ammend your present user data here.";
	$error_str .= make_table(array("",""));
	$error_str .= "<form method=post action=options.php>";
	$error_str .= quick_row("Your Signature (100 Char Max):","<textarea name=sig cols=25 rows=10>".stripslashes($user['sig'])."</textarea>");
	$error_str .= "</table><input type=hidden name=player_op value=2><br><br>";
	$error_str .= "<input type=submit name=Submit></form><br><br>";
	print_page("Change Player Information",$error_str);

} elseif(isset($player_op) && $player_op == 2){
	dbn("update ${db_name}_users set sig = '".addslashes($sig)."' where login_id = '$user[login_id]'");
	$error_str .= "User information updated.";
}

#save changes to vars
if (isset($save_vars)) {
	foreach ($_POST as $var => $value) {
		$option_check="";
		if ($var == 'save_vars' || !(isset($user_options[$var]) &&
		     $value != $user_options[$var])) {
			continue;
		}

		#ensure option is in range
		db("SELECT `option_min`, `option_max` FROM `option_list` WHERE " .
		 "`option_name` = '$var'");
		$option_check=dbr();
		#option out of range
		if($value < $option_check['option_min'] || $value > $option_check['option_max']){
			$error_str .= "<br><b class=b1>$var</b> out of range.";
		} else { #option in range
			dbn("update ${db_name}_user_options set $var = '$value' where login_id = '$user[login_id]'");
			$user_options[$var] = $value;
			$error_str .= "<br><b class=b1>$var</b> updated to <b>$value</b>";
		}
	}
}

// retire
if(isset($retire)) {
	if(!isset($sure)) {
		if($retire_period != 0){
			$retire_text_xtra = "<p>You will <b class=b1>Not</b> be able to rejoin this game for <b>$retire_period</b> hours.";
		}
		get_var("Retire","options.php","<p><b class=b1>Warning!</b> This will permanently remove your account from this game. <br>Are you sure you want to retire?".$retire_text_xtra,"sure","yes");
	} else {
		if ($user['clan_id'] > 0) {
			db("select leader_id from ${db_name}_clans where clan_id = '$user[clan_id]'");
			$temp_1 = dbr();
			db("select count(distinct login_id) from ${db_name}_users where clan_id = '$user[clan_id]' && login_id > 5");
			$temp_2 = dbr();
			$clan = array('members' => $temp_1[0], 'leader' => $temp_2[0]);
			if($clan['members'] > 1 && $user['login_id'] == $clan['leader_id'] && !$what_to_do){
				$new_page = "Before you retire you must first select whether you want your clan to be disbanded, or assign a new leader to it:";
				$new_page .= "<form action=options.php method=POST name=retiring>";
				while (list($var, $value) = each($HTTP_POST_VARS)) {
					$new_page .= "<input type=hidden name=$var value='$value'>";
				}
				$new_page .= "<p>Disband Clan <INPUT type=radio name=what_to_do value=1 CHECKED> / Assign New Clan Leader<INPUT type=radio name=what_to_do value=2><p><INPUT type=submit value='Submit'></form>";
				print_page("Retiring",$new_page);
			} elseif($clan['members'] < 2 || $what_to_do == 1){
				dbn("update ${db_name}_users set clan_id = 0 where clan_id = $user[clan_id]");
				dbn("update ${db_name}_planets set clan_id = -1 where clan_id = $user[clan_id]");
				dbn("delete from ${db_name}_clans where clan_id = $user[clan_id]");
				dbn("delete from ${db_name}_messages where clan_id = $user[clan_id]");
			} elseif($what_to_do == 2 && !$leader_id){
				$new_page = "Please select which of the below you would like to be the new clan leader:";
				$new_page .= "<form action=options.php method=POST name=retiring2>";
				#$new_page .= "<input type=hidden name=what_to_do value='$what_to_do'>";
				db2("select login_id,login_name from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '$user[login_id]'");
				$new_page .= "<select name=leader_id>";
				while ($member_name = dbr2()) {
					$new_page .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
				}
				$new_page .= "</select>";
				while (list($var, $value) = each($HTTP_POST_VARS)) {
					$new_page .= "<input type=hidden name=$var value='$value'>";
				}
				$new_page .= "<p><INPUT type=submit value='Submit'></form>";
				print_page("Assign New Clan Leader",$new_page);
			} else{
					//dbn("update ${db_name}_clans set leader_id = $leader_id where clan_id = $user[clan_id]");
			}
		}
		retire_user($user['login_id']);
		$rs = "<p><a href=game_listing.php>Go to Game List</a>";
		print_header("Account Removed");
		insert_history($user['login_id'],"Retired From Game");
		echo "You have been removed from the Game.";
		print_footer();
		exit();
	}
}


// change password
if(isset($changepass)) {
  $rs = "<br><a href=options.php>Back To Options</a>";
  if($changepass == 'change') {
	$temp_str = "<h1>Enter a new password</h1>";
	$temp_str .= "<form action=options.php method=post><input type=hidden name=changepass value=changed>";
	$temp_str .= "<table><tr><td align=right>Old Password:</td><td><input type=password name=oldpass></td></tr>";
	$temp_str .= "<tr><td align=right>New Password:</td><td><input type=password name=newpass></td></tr>";
    $temp_str .= "<tr><td align=right>Re-type New Password:</td><td><input type=password name=newpass2></td></tr>";
	$temp_str .= "<tr><td></td><td><input type=submit value='Change Password'></td></tr></table></form><br>";
	print_page("Change Password",$temp_str);
  } elseif ($changepass == 'changed') {
		if($user['login_id'] == ADMIN_ID){ //admin pass. Not encrypted
			db("select admin_pw from se_games where db_name = '$db_name'");
		} else {
			db("select passwd from user_accounts where login_id = " . $user['login_id']);
		}

		$pass = dbr(0);
		$userPass = md5($pass[0]);
		$oldPassEnc = md5($oldpass);
		$newPassEnc = md5($newpass);

		if (isset($newpass) && ($newpass == $newpass2)) {
			if ($newPassEnc === $oldPassEnc || $newPassEnc === $userPass) { //make sure it's not the same as the old one.
			   $temp_str = "Really. You want your new pass to be the same as your old one? Are you just wasting my bandwith?";
			   $temp_str = "<p><a href=javascript:history.back()>Back to Pass-Change Form</a>";
			} elseif ($enc_oldpass === $userPass) {
				if ($user['login_id'] == ADMIN_ID) {
					dbn("update se_games set admin_pw='$newPassEnc' where db_name = '$db_name'");
					$temp_str .= "<p>Admin password will remain after game is wiped</p>";
				} else {
					dbn("update user_accounts set passwd = '$newPassEnc' where login_id = " . $user['login_id']);
				}
				$p_user['passwd'] = $newPassEnc;
				$temp_str = "<p>Password changed successfully</p>";
				insert_history($user['login_id'], "Password Changed");
			} else {
				$temp_str = "The old password is not correct!<br><br>";
				$temp_str .= "<a href='javascript:back()'>Go back</a><br>";
			}
		} else {
			$temp_str = "Password mismatch!<br>";
			$temp_str .= "<a href='javascript:back()'>Go back</a><br>";
		}

		print_page("Change Password",$temp_str);
	}
}


// change colour scheme
if(isset($scheme)) {

$checked[$user_options['color_scheme']] = " checked";

$error_str .= "Select a colour scheme you like the sound of:";
$error_str .= "<FORM method=POST action=options.php>";
$error_str .= "<input type=radio name=style value=1".$checked[1]."> <b class=b1><font color='red'>Classic</font></b>";
$error_str .= " - Default<br>";

$error_str .= "<input type=radio name=style value=2".$checked[2]."> <b class=b1><font color='#0091ff'>Blue & Black</font></b> - afphreak<br>";

$error_str .= "<input type=radio name=style value=5".$checked[5]."> <b><font color='#007900'>Green & Black</font></b> - Pinkus<br>";

$error_str .= "<input type=radio name=style value=4".$checked[4]."> <b><font color='#99FFAA'>Light Green & Black</font></b> - Moriarty<br>";

$error_str .= "<input type=radio name=style value=6".$checked[6]."> <b><font color='#FFFF00'>Yellow & Black</font></b> - Moriarty<br>";

$error_str .= "<input type=radio name=style value=3".$checked[3]."> <b><font color='#808080'>Grey</font></b>/<b><font color='#004080'>Blue</font></b>/<b><font color='#00FF00'>Lime</b></font> - Pinkus<br>";

$error_str .= "<input type=radio name=style value=7".$checked[7]."> <b><font color='#808080'>Ru</font></b><b><font color='#1f677e'>st</font></b><b><font color='#a58421'>ic</font></b> - TheMadWeaz<br>";

$error_str .= "<input type=radio name=style value=8".$checked[8]."> <b><font color='#808080'>Al</font></b><font color='#008e8e'><b>ie</b></font><b><font color='#9cbf75'>n</b></font> - TheMadWeaz<br>";

$error_str .= "<input type=radio name=style value=9".$checked[9]."> <b><font color='#ffffff'>Ice</font></b> <b><font color='#c0c5fe'>Age</b></font> - TheMadWeaz<br>";

$error_str .= "<input type=radio name=style value=10".$checked[10]."> <b><font color='#DDDDF5'>Ch</font></b><b><font color='#C5C5CC'>ro</font></b><b><font color='#CCCCCC'>me</b></font> - KilerCris<br>";

$error_str .= "<input type=radio name=style value=11".$checked[11]."> <b><font color='#9c9d8a'>The</b></font> <b><font color='#a8a800'>Golden</b></font> <b><font color='#fbcf04'>Age</b></font> - TheMadWeaz<br>";

$error_str .= "<input type=radio name=style value=12".$checked[12]."> <b><font color='#77888a'>The</b></font> <b><font color='#b8b8b8'>Silver</b></font> <b><font color='#9b9493'>Age</b></font> - TheMadWeaz<br>";

$error_str .= "<input type=radio name=style value=13".$checked[13]."> <b><font color='#b70000'>Mo</b></font><b><font color='#ff8000'>lt</b></font><b><font color='#f7b324'>en</b></font> - TheMadWeaz<br>";

$error_str .= "<input type=radio name=style value=15".$checked[15]."> Artic Ice - Thalias<br>";

$error_str .= "<input type=radio name=style value=16".$checked[16]."> Galactic Gold - TimmyP<br>";

$error_str .= "<p><INPUT type=submit value=Submit>";
$error_str .= "</form>";
print_page("Select Scheme",$error_str);

#demo of style sheet
} elseif (isset($style)) {

	$temp = "style".$style.".css";

	echo"<html>\n";
	echo"<head>\n";
	echo"<title>[ Solar Empire - ".SERVER_NAME." : Test Scheme ]</title>\n";
	echo "<link rel=stylesheet href=$temp>\n";
	echo "</head>\n";
	echo "<body text=#FFFFFF>\n";
	print_status();

	$error_str .="Here is an example of what things may look like.";

	$error_str .= "<p>Normal text.\n";
	$error_str .= "<br><b>Bold text.</b>\n";
	$error_str .= "<br><b class=b1>Bold class 1</b>\n";
	$error_str .= "<br><b class=b2>Bold class 2</b>\n";
	$error_str .= "<br><b class=b3>Bold class 3</b>\n";
	$error_str .= "<br><a href=>Normal Link</a>\n";
	$error_str .= "<br><a href=location.php>Visited Links</a>\n";
	$error_str .= "<br><b class=cloak>Cloaked vessels</b>\n";

	$error_str .= "<p>Do you want to:\n";
	$error_str .= "<br><a href=options.php?keep=$style>Keep it.</a>\n";
	$error_str .= "<br><a href=options.php?scheme=1>Select a different Scheme.</a>\n";

	echo $error_str;
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit();

#keep new style sheet.
} elseif (isset($keep)) {
	$user_options['color_scheme'] = $keep;
	dbn("update ${db_name}_user_options set color_scheme = '$keep' where login_id = '$user[login_id]'");
	$error_str .="Colour Scheme changed to Number <b>$keep</b>.";
	$error_str .="<br>You can change it again at any time using the same procedure.";
	$rs .= "<br><a href=options.php>Back To Options</a>";
	print_page("Colour Scheme Changed",$error_str);
}




#print main page
$error_str .= "<p>On this page you will find a range of options that will enable you to customise Solar Empire.";
$error_str .= "<p><a href=options.php?changepass=change>Change your Password</a>";
$error_str .= "<br><a href=options.php?scheme=1>Change your Colour Scheme</a>";
$error_str .= "<br><a href=options.php?player_op=1>Change your player information</a> (signature)";


if($user['login_id'] != ADMIN_ID){
	$error_str .= "<p><a href=options.php?retire=1>Retire from Game</a>";
}

#list other options
$error_str .= "<p>Here are some other Options that you may set.<form method=POST name=get_var_form action=options.php>";

$error_str .= "<br><input type=submit value=\"Submit Vars\">";

#select and output all the user options
db("select * from option_list order by option_name asc");
while($gen_options=dbr()){

	#radio boxes.
	if($gen_options['option_type'] == 1){
		$ct = 0;
		$desc_vars = preg_split("/ &&& /", $gen_options['option_desc']);
		$error_str.= "<p><table border=2 cellspacing=1 width=350><tr bgcolor='#333333'><td><b><font color='#AAAAEE'>$gen_options[option_name]</font></b></td></tr><tr bgcolor='#555555'><td>$desc_vars[0]<br>";
		$checked = array();
		$checked = array_pad($checked,5,"");
		$checked[$user_options[$gen_options['option_name']]] = " checked";
#		var_dump($checked);
#		$checked = array_pad
		$sec_count = 1; #used to extract definitions for array (arrays start at 0).

		#loop through the possible selections for each option
		for($ct=$gen_options['option_min'];$ct <= $gen_options['option_max'];$ct++){
			$error_str .= "<br><input type=radio name='".$gen_options['option_name']."' value='$ct'".$checked[$ct].">".$desc_vars[$sec_count];
			$sec_count ++;
		}

		$error_str .= "</td></tr></table>\n";

	#numerical interface
	} elseif($gen_options['option_type'] == 2){
		$error_str .= "<p><table border=2 cellspacing=1 width=350><tr bgcolor='#333333'><td width='250'><b><font color='#AAAAEE'>$gen_options[option_name]</font></b></td><td align='right'><input type='text' name='$gen_options[option_name]' size='4' value='".$user_options[$gen_options['option_name']]."'> </td></tr><tr bgcolor='#555555'><td colspan='2'><blockquote>$gen_options[option_desc]<br>Min: <b>$gen_options[option_min]</b>, Max: <b>$gen_options[option_max]</b></blockquote></td></tr></table>\n";
	}
}

$error_str .= "<br><input type='hidden' name='save_vars' value='1'><input type=submit value=\"Submit Vars\"></form>";



print_page("Account Options", $error_str);

?>