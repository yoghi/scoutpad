<html>
	<head>
		<title>{$title}</title>
		<link rel="stylesheet" type="text/css" media="screen" href="/styles/common.css" />
		{$stylesheet}
		<meta name="keywords" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<meta name="description" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<link rel="alternate" type="application/rss+xml" title="SigmaLab RSS Feed" href="/feed/" />
		{if isset($headers) }
			{$headers}
		{/if}
	</head>
	<body>
		<div id="wrapper">
			{include file="head.tpl"}
			<!-- il template del content -->
			{include file="sidebar.tpl"}
			{include file=contents/$actionTemplate}
			{include file="footer.tpl"}
		</div>
			
		{if isset($info_user)}
		
			<em>{$info_user}</em>
		
		{/if}
		
	</body>
</html>