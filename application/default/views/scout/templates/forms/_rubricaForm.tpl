<form action="/rubrica/{$action}" method="post">
	<p>
		<label for="nome">Nome</label>
		<input type="text" class="input-large" name="nome" value="{$staff.nome}"/>
	</p>
	<p>
		<label for="cognome">Cognome</label>
		<input type="text" class="input-large" name="cognome" value="{$staff.cognome}"/>
	</p>
	<p>
		<label for="mail">Mail</label>
		<input type="text" class="input-large" name="mail" value="{$staff.mail}"/>
	</p>
	<p>
		<label for="mail">Cellulare</label>
		<input type="text" class="input-large" name="cellulare" value="{$staff.cellulare}"/>
	</p>
	<p>
		<label for="mail">Fisso</label>
		<input type="text" class="input-large" name="fisso" value="{$staff.fisso}"/>
	</p>
	<p>
		<label for="mail">Gruppo</label>
		<input type="text" class="input-large" name="gruppo" value="{$staff.gruppo}"/>
	</p>
	<p>
		<label for="mail">Status</label>
		{ if ($staff.status) }
			<input type="checkbox" class="input-large" name="status" checked/>
		{ else }
			<input type="checkbox" class="input-large" name="status" />
		{/if}
	</p>
	<p>
		<input type="hidden" name="id" value="{$staff.id}"/> 
		<input type="submit" name="submit" value="{$buttonText}"/>
	</p>
</form>
