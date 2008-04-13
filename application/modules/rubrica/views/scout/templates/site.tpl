<html>
	<head>
		<title>{$title}</title>
		<link rel="stylesheet" type="text/css" media="screen" href="/styles/login.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/styles/common.css" />
		<meta name="keywords" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<meta name="description" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<link rel="alternate" type="application/rss+xml" title="SigmaLab RSS Feed" href="/feed/" />
		{if isset($headers) }
			{$headers}
		{/if}
	</head>
	<body>
		{include file=$actionTemplate}
		{include file="footer.tpl"}
	</body>
</html>