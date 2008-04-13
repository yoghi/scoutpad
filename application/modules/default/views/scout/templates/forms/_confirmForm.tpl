<div id="contents" class="login">

		<div class="testo">
			<h3>Conferma Registrazione</h3>
			Sei stato registrato, ora devi scegliere la tua password. Ricorda che tutti i campi sono obbligatori. 
		</div>

		<form class="form" name="loginform" action="/login/confirm/" method="post">
			<p>
				<label>Mail:</label>
			</p>
			<p>
				<input type="text" name="mail" id="mail" value="" size="20" tabindex="1" />
			</p>
			<p>
				<label>Cellulare:</label>
			</p>
			<p>
				<input type="text" name="cellulare" id="cellulare" value="" size="20" tabindex="2" />
			</p>
			<p>
				<label>Password:</label>
			</p>
			<p>
				<input type="password" name="password" id="password" value="" size="20" tabindex="3" />
			</p>
			<p class="submit">
				<input type="submit" name="submit" id="submit" value="{$buttonText} &raquo;" tabindex="4" />
			</p>	
		</form>
		
		<ul>
			<li><a href="/" title="Are you lost?"> &laquo; Back to home</a></li>
		</ul>
	
</div>