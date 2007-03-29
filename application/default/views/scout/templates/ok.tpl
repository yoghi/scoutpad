<div align="center">
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
</div>