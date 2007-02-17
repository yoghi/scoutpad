<h1>{$title}</h1>
{if isset($staff.id) }
<form action="/rubrica/delete" method="post">
<p>Sicuro di voler eliminare {$staff.nome} {$staff.cognome}?</p>
	<div>
		<input type="hidden" name="id" value="{$staff.id}"/> 
		<input type="submit" name="del" value="Yes" />
		<input type="submit" name="del" value="No" />
	</div>
</form>
{else}
<p>Membro non trovato.</p>
{/if}