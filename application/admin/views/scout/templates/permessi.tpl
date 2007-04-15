<div id="contents">
	
	<h3>Parametri di ricerca</h3>
	<label>MODULO : </label>
	{$module_name|ucfirst}
	
	<br/>
	
	<label>Gruppo : </label>
	<select>
		{ foreach from=$role_list  value=role }
			<option>{$role.name}</option>
		{/foreach}
	</select>
	<h3>Autorizzato a </h3>
	<ul>
		{ foreach from=$acl_list  value=acl }
			<li>[{$acl.id}] {$acl.Controller} -> {$acl.Action}  [<a href="/admin/permessi/remove/modulo/{$module_name}/id/{$acl.id}">remove</a>] </li>
		{/foreach}
	</ul>
	
	<a href="/admin/permessi/aggiungi/modulo/{$module_name}/role/{$current_role}">Rilascia permesso a {$current_role}</a>
	
</div>