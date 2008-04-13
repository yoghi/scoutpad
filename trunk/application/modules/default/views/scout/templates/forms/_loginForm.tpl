<div id="contents" class="login">

		<div class="testo">
			<h3>Autenticazione</h3>
			Per accedere a determinate aree del sistema, &egrave; necessario essere autorizzati. 
			Se necessiti di un account contattaci <a href="/contact/">qui</a>, verrai ricontattato al pi&uacute; presto. 
			Se invece possiedi un'account valido e hai problemi, scrivi nella ML di riferimento. 
			Per qualunque altro motivo <a href="/contact/">contattaci</a> risponderemo il prima possibile.
		</div>

		<form class="form" name="loginform" action="/login/in/" method="post">
			<fieldset>
				<legend>Form di autenticazione</legend>
				<div>
					<label>Mail:</label>
					<input type="text" name="mail" id="mail" value="" size="20" tabindex="1" />
				</div>
				<div>
					<label>Password:</label>
					<input type="password" name="password" id="password" value="" size="20" tabindex="2" />
				</div>
				<div class="submit">
					<input type="submit" name="submit" id="submit" value="{$buttonText} &raquo;" tabindex="3" />
				</div>
			</fieldset>
		</form>
		
		<ul>
			<li><a href="/" title="Are you lost?"> &laquo; Back to home</a></li>
			<li><a href="/login/lost/" title="Password Lost and Found">Lost your password?</a></li>
		</ul>
	
</div>