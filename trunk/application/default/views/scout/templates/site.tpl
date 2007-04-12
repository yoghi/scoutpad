<html>
	<head>
		<title>{$title}</title>
		<link rel="stylesheet" type="text/css" media="screen" href="/styles/common.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/styles/single.css" />
		{if isset($stylesheet) }
			{$stylesheet}
		{/if}
		<meta name="keywords" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<meta name="description" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<link rel="alternate" type="application/rss+xml" title="SigmaLab RSS Feed" href="/feed/" />
		{if isset($headers) }
			{$headers}
		{/if}
	</head>
	<body>
		<div align="center" id="wrapper">
			{include file="head.tpl"}
			<!-- il template del content -->
			{include file=$actionTemplate}
			{include file="footer.tpl"}
		</div>
	</body>
</html>