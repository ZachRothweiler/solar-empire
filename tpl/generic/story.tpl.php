<?php
if (!defined('PATH_SAVANT')) exit();

$title = 'The Solar Empire Story';

include('inc/header_splash.tpl.php');

?><h1>The Solar Empire Story</h1>

<p><a href="system.php">Enter game</a></p>
<?php

include_once('inc/stories.inc.php');
echo $story['The_Solar_Empire_Story'];

?><p><a href="system.php">Enter game</a></p>

<?php

include('inc/footer_splash.tpl.php');

?>
