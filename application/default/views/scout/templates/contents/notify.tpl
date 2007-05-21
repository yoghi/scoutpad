<div id="contents">

	<div class="{$notify_type}">
		<b>{$title}</b>
		<ul>
		{foreach from=$notify_text value=msg}
			<li>{$msg}</li>
		{/foreach}
		</ul>
	</div>
	
	{if isset($notify_link2_text)}
		<h4>
			<p>	
				<a style="text-decoration:none;" href="{$before_page}">&ldquo; {$notify_link_text} &rdquo; </a>
			</p>
			<p style="font-size: 160%" >
				<a style="text-decoration:none;" href="{$next_page}">&ldquo; {$notify_link2_text} &rdquo; </a> 
			</p>
		</h4>
	{else}
		<h4><a style="text-decoration:none;" href="{$before_page}">&ldquo; {$notify_link_text} &rdquo; </a></h4>
	{/if}
	
	
</div>