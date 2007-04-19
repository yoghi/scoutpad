<div id="contents" class="login">

	<div class="testo">
		<h3>Lost Password</h3>
		Nel caso ti sia dimenticato la password, inserisci nel riquadro sottostante la tua mail; ti verr&aacute; resettata la password e rispedita. 
	</div>
		
	
	<form class="form" name="loginform" action="/login/in/"	method="post">
		<fieldset>
			<legend>Lost Password</legend>
			<div>
				<label>Mail:<br /><input type="text" name="mail" id="mail" value="" size="20" tabindex="1" /></label>
			</div>
			<div class="submit">
				<input type="submit" name="submit" id="submit" value="{$buttonText} &raquo;" tabindex="4" />
			</div>	
		</fieldset>
	</form>
	
	<ul>
		<li>
			<a href="/" title="Are you lost?"> &laquo; Back to home</a>
		</li>
	</ul>
	
</div>