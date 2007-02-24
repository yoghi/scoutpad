<h1>{$title}</h1>
<div id="contents">
	<ul>
		{ foreach from=$moduli  value=modulo }
			<li>{$modulo.nome}</li>
		{/foreach}
	</ul>
</div>