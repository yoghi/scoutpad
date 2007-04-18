<div style="width: 700px; text-align: center;">
	
	<div class="checkMacro">
		<b>{$testo_conferma}</b>
		<ul>
		{foreach from=$errore value=err}
			<li>{$err}</li>
		{/foreach}
		</ul>
	</div>
	
	<form class="form" name="queryform" action="{$confirm_uri}" method="post">
			<div class="submit">
				<input type="submit" name="submit" id="submit" value="Clicca qui per confermare &raquo;" tabindex="3" />
			</div>
	</form>
	
	<h4><a style="text-decoration:none;" href="/">&ldquo; Clicca qui per ritornare nella homepage &rdquo; </a></h4>
</div>