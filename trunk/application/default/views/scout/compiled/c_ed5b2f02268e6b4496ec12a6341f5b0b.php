<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2007-02-10 03:09:37 CET */ ?>

<div id="login">
	<h1><a href="http://campospe.lanzanoven.net/">Scout</a></h1>
	<form name="loginform" id="loginform" action="/login/confirm/"	method="post">
		<p>
			<label>Mail:<br /><input type="text" name="mail" id="mail" value="" size="20" tabindex="1" /></label>
		</p>
		<p>
			<label>Cellulare:<br /><input type="text" name="cellulare" id="cellulare" value="" size="20" tabindex="1" /></label>
		</p>
		<p>
			<label>Nuova Password:<br /> <input type="password" name="password" id="password" value="" size="20" tabindex="2" /></label>
		</p>
		<p>
			<label>&nbsp; </label>
		</p>
		<p class="submit">
			<input type="submit" name="submit" id="submit" value="<?php echo $this->_vars['buttonText']; ?>
 &raquo;" tabindex="4" />
		</p>	
	</form>
</div>