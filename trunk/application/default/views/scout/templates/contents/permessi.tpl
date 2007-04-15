<div id="contents">
	
	<h3>Parametri di ricerca</h3>
	<form class="form" name="queryform" action="/admin/permessi/" method="post">
		<p>
		<label>Modulo : </label>	
		<select name="modulo">
			{ html_options options=$module_options selected=$module_name }
		</select>
		<label>Gruppo : </label>
		<select name="role">
			{ html_options options=$role_options selected=$current_role }
		</select>
		</p>
		<p class="submit">
			<input type="submit" name="submit" id="submit" value="{$buttonText} &raquo;" tabindex="3" />
		</p>
	</form>
	
	<h3>Nel modulo {$module_name|ucfirst}, {$current_role|ucfirst} &eacute; autorizzato a </h3>
	<ul>
		{ foreach from=$acl_list  value=acl }
			<li>[{$acl.id}] {$acl.Controller} -> {$acl.Action}  [<a href="/admin/permessi/remove/id/{$acl.id}">remove</a>] </li>
		{/foreach}
	</ul>
	
	<a href="/admin/permessi/aggiungi/modulo/{$module_name}/role/{$current_role}">Rilascia permesso a {$current_role}</a>
	
</div>