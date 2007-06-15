<?php
class_exists('Savant2') || exit;

$this->pageName = 'Welcome';
$this->title = 'Competitive, web based, space combat game';

include($this->loadTemplate('inc/header_splash.tpl.php'));

?><form id="login" action="<?php
$this->eprint($this->url['self']);
?>" method="post">
	<h2><a href="<?php
$this->eprint($this->url['base'] . '/register.php');
?>">Create an account</a></h2>

	<h2>Continue the War</h2>
	<dl>
		<dt><label for="handle">Account name</label></dt>
		<dd><input type="text" name="handle" id="handle" class="text" /></dd>
	
		<dt><label for="password">Password</label></dt>
		<dd><input type="password" name="password" id="password" class="text" /></dd>

		<dt><input type="submit" value="Enter game" class="button" /></dt>
	</dl><?php
if (isset($this->authProblem) && !empty($this->authProblem)) {
	$authProblem = array(
		'existQuery' => 'There was a problem checking whether that account exists',
		'accountMissing' => 'There is no account associated with that account name, password combination'
	);
?>
	<h3>Problems</h3>
	<ul>
<?php
	foreach ($this->authProblem as $probId) {
		if (isset($authProblem[$probId])) {
?>		<li><?php $this->eprint($authProblem[$probId]); ?></li>
<?php
		}
	}
?>	</ul>
<?php
}
?>
</form>

<div id="loginContent">
<h1>Welcome to System Wars</h1>

<h2>Introduction</h2>
<p><em>System Wars</em> is a highly competitive, web based, <strong>space combat game</strong> based on the <a href="http://www.solarempire.com/">Solar Empire</a> universe.  Master the arts of colonisation and exploration while playing a noble but precarious game of warfare to ensure survival.</p>

<h2>Project information</h2>
<p><a href="http://sourceforge.net/projects/solar-empire/">Solar Empire</a> is an open source project hosted on SourceForge; look at the <a href="http://sourceforge.net/news/?group_id=16534">news section</a> for the latest updates.  The <a href="<?php $this->eprint($this->url['base'] . '/credits.php'); ?>">credits</a> page contains a list of all the contributors to the game</a>.</p>
<p>Visit the <a href="http://forum.syswars.com/">global forums</a> to learn more and help develop this open-source game.</p>

<h2>Operational Servers</h2>
<p><a href="http://sourceforge.net/projects/solar-empire/"><img src="http://sourceforge.net/sflogo.php?group_id=16534&amp;type=3" width="125" height="37" alt="Solar Empire sourceforge project" style="float: right; padding: 1em;" /></a></p>
<ul>
	<li><a href="http://www.solar-empire.net/">Endless War</a></li>
	<li><a href="http://game.quantum-star.com/">QS: Generations</a></li>
	<li><a href="http://www.imperial-empire-se.com/">Imperial Empire</a></li>
	<li><a href="http://solarempire.fuoriradio.com/">Italian Server</a></li>
</ul>
</div>
<?php

include($this->loadTemplate('inc/footer_splash.tpl.php'));

?>
