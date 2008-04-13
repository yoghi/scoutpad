<div id="contents">

	<br/>

	<h3>Modulo per Contattarci</h3>
	
	<div class="testo">

		Il modulo qui di sotto ti permetter&aacute; di contattarci direttamente, ricordati per&oacute; di inserie la mail per la risposta e il numero di telefono 
		in caso di neccessit&aacute; urgenti.    
	
	</div>
	
	<form class="form" name="contactform" action="/contact/invia/" method="post">
		<fieldset>
			<legend>Modulo</legend>
			<div>
				<label>Nome e Cognome:</label>
				<input  style="width:100%" type="text" name="nomecognome" id="nomecognome" value="" size="20" tabindex="1" />
			</div>
			<div>
				<label>Mail:</label>
				<input  style="width:100%" type="text" name="mail" id="mail" value="" size="20" tabindex="2" />
			</div>
			<div>
				<label>Telefono:</label>
				<input  style="width:100%" type="text" name="telefono" id="telefono" value="" size="20" tabindex="3" />
			</div>
			<div>
				<label>Richiesta</label>
				<textarea rows="10" cols="10" name="richiesta" id="richiesta" tabindex="4"></textarea>
			</div>
			<div class="submit">
				<input type="submit" name="submit" id="submit" value="{$buttonText} &raquo;" tabindex="3" />
			</div>
		</fieldset>
	</form>
	
</div>

