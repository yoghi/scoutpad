<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2007-02-09 11:54:33 CET */ ?>

<div id="login">
	<h1><a href="http://campospe.lanzanoven.net/">Scout</a></h1>
	<form name="loginform" id="loginform" action="/login/in/"	method="post">
		<p>
			<label>Mail:<br /><input type="text" name="mail" id="mail" value="" size="20" tabindex="1" /></label>
		</p>
		<p>
			<label>Password:<br /> <input type="password" name="password" id="password" value="" size="20" tabindex="2" /></label>
		</p>
		<p>
			<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="3" /> Remember me</label>
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
		<li>
			<a href="/login/lost/" title="Password Lost and Found">Lost your password?</a>
		</li>
	</ul>
</div>