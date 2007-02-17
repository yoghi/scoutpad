<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2007-02-16 16:53:00 CET */ ?>

<html>
	<head>
		<title><?php echo $this->_vars['title']; ?>
</title>
		<link rel="stylesheet" type="text/css" media="screen" href="/styles/login.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/styles/common.css" />
		<meta name="keywords" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<meta name="description" content="Campetti di Specialita della Zona di Rimini - AGESCI -" />
		<link rel="alternate" type="application/rss+xml" title="SigmaLab RSS Feed" href="/feed/" />
		<?php if (isset ( $this->_vars['headers'] )): ?>
			<?php echo $this->_vars['headers']; ?>

		<?php endif; ?>
	</head>
	<body>
		<?php $_templatelite_tpl_vars = $this->_vars;
echo $this->_fetch_compile_include($this->_vars['actionTemplate'], array());
$this->_vars = $_templatelite_tpl_vars;
unset($_templatelite_tpl_vars);
 ?>
		<?php $_templatelite_tpl_vars = $this->_vars;
echo $this->_fetch_compile_include("footer.tpl", array());
$this->_vars = $_templatelite_tpl_vars;
unset($_templatelite_tpl_vars);
 ?>
	</body>
</html>