<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2007-02-16 18:02:54 CET */ ?>

<div align="center">
	<div id="errore">
		<ul>
		<?php if (count((array)$this->_vars['errore'])): foreach ((array)$this->_vars['errore'] as $this->_vars['err']): ?>
			<li><?php echo $this->_vars['err']; ?>
</li>
		<?php endforeach; endif; ?>
		</ul>
	</div>
</div>