<h1>{$title}</h1>
<div id="contents">
	<h2>Attivi</h2>
	<p>
		<table border="0px" cellpadding="2px" cellspacing="2px" width="770px">
			<tr>		
				<th>Nome</th>
				<th>Cognome</th>
				<th>Mail</th>
				<th>Cellulare</th>
				<th>Fisso</th>
				<th>Gruppo</th>
				<th>Comandi</th>
			</tr>
			{ foreach from=$membri  value=membro }
			<tr>
				<td>{$membro.nome}</td>
				<td>{$membro.cognome}</td>
				<td><a href="mailto:{$membro.mail}">{$membro.mail}</a></td>
				<td>{$membro.cellulare}</td>
				<td>{$membro.fisso}</td>
				<td>{$membro.gruppo}</td>
				<td>
					<a href="/rubrica/edit/id/{$membro.id}">Edit</a>
					<a href="/rubrica/delete/id/{$membro.id}">Delete</a>
			</td>
			</tr>
			{/foreach}
		</table>
	</p>
	<p>
		<a href="/rubrica/add">Aggiungi membro</a><br/><br/>
	</p>
	<p>
	<h2>Collaboratori e uomini ombra</h2>
		<table border="0px" cellpadding="2px" cellspacing="2px" width="770px">
			<tr>		
				<th>Nome</th>
				<th>Cognome</th>
				<th>Mail</th>
				<th>Cellulare</th>
				<th>Fisso</th>
				<th>Gruppo</th>
				<th>Comandi</th>
			</tr>
			{ foreach from=$membri_ombra  value=membro }
			<tr>
				<td>{$membro.nome}</td>
				<td>{$membro.cognome}</td>
				<td><a href="mailto:{$membro.mail}">{$membro.mail}</a></td>
				<td>{$membro.cellulare}</td>
				<td>{$membro.fisso}</td>
				<td>{$membro.gruppo}</td>
				<td>
					<a href="/rubrica/edit/id/{$membro.id}">Edit</a>
					<a href="/rubrica/delete/id/{$membro.id}">Delete</a>
			</td>
			</tr>
			{/foreach}
		</table>
	</p>
</div>