<div id="contents">

	<div class="infoMacro">
		<b>complete!</b>
		{if count($confirm_text) gt 1}
		<ul>
		{foreach from=$confim_text value=msg}
			<li>{$msg}</li>
		{/foreach}
		</ul>
		{else}
			<p>{$confim_text[0]}</p>
		{(if}
	</div>
	
	<h2><a style="text-decoration:none;" href="/">&ldquo; Clicca qui per ritornare nella homepage &rdquo; </a></h2>
	
</div>