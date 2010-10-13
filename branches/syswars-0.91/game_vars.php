<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');

$gameInfo = selectGame(isset($_REQUEST['db_name']) ? $_REQUEST['db_name'] : '');
if (!$gameInfo) {
	print_page('Error', 'Invalid game!');
}

$vars = $db->query('SELECT name, value, descript FROM [game]_db_vars ' .
 'ORDER BY name');


if (!$gameOpt['admin_var_show']) {
	print_page('Error', 'Admin has disabled public viewing of game vars');
}

print_header('Game Variables');

echo <<<END
<h1>{$gameInfo['name']} game variables</h1>
<table class="simple">
	<tr>
	    <th>Name</th>
	    <th>Value</th>
	    <th>Description</th>
	</tr>

END;

while ($var = $db->fetchRow($vars)) {
	echo <<<END
    <tr>
		<td>{$var['name']}</td>
		<td>{$var['value']}</td>
		<td>{$var['descript']}</td>
	</tr>

END;
}

echo "</table>";
print_footer();

?>