<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2007-02-09 12:07:59 CET */ ?>

<div id="login">
	<h1><a href="http://campospe.lanzanoven.net/">Scout</a></h1>
	<form name="loginform" id="loginform" action="/login/in/"	method="post">
		<p>
			<label>Mail:<br /><input type="text" name="mail" id="mail" value="" size="20" tabindex="1" /></label>
		</p>
		<p class="submit">
			<input type="submit" name="submit" id="submit" value="<?php echo $this->_vars['buttonText']; ?>
 &raquo;" tabindex="4" />
		</p>	
	</form>
	<ul>
		<li>
			<a href="/" title="Are you lost?"> &laquo; Back to home</a>
		</li>
	</ul>
</div>