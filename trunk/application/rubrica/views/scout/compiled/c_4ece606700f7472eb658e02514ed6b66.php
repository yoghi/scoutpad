<?php /* V2.10 Template Lite 4 January 2007  (c) 2005-2007 Mark Dickenson. All rights reserved. Released LGPL. 2007-02-16 16:52:27 CET */ ?>

<h1><?php echo $this->_vars['title']; ?>
</h1>
<div id="contents">
	<h2>Attivi</h2>
	<p>
		<table border="0px" cellpadding="2px" cellspacing="2px" width="770px">
			<tr>		
				<th>Nome</th>
				<th>Cognome</th>
				<th>Mail</th>
				<th>Cellulare</th>
				<th>Fisso</th>
				<th>Gruppo</th>
				<th>Comandi</th>
			</tr>
			<?php if (count((array)$this->_vars['membri'])): foreach ((array)$this->_vars['membri'] as $this->_vars['membro']): ?>
			<tr>
				<td><?php echo $this->_vars['membro']['nome']; ?>
</td>
				<td><?php echo $this->_vars['membro']['cognome']; ?>
</td>
				<td><a href="mailto:<?php echo $this->_vars['membro']['mail']; ?>
"><?php echo $this->_vars['membro']['mail']; ?>
</a></td>
				<td><?php echo $this->_vars['membro']['cellulare']; ?>
</td>
				<td><?php echo $this->_vars['membro']['fisso']; ?>
</td>
				<td><?php echo $this->_vars['membro']['gruppo']; ?>
</td>
				<td>
					<a href="/rubrica/edit/id/<?php echo $this->_vars['membro']['id']; ?>
">Edit</a>
					<a href="/rubrica/delete/id/<?php echo $this->_vars['membro']['id']; ?>
">Delete</a>
			</td>
			</tr>
			<?php endforeach; endif; ?>
		</table>
	</p>
	<p>
		<a href="/rubrica/add">Aggiungi membro</a><br/><br/>
	</p>
	<p>
	<h2>Collaboratori e uomini ombra</h2>
		<table border="0px" cellpadding="2px" cellspacing="2px" width="770px">
			<tr>		
				<th>Nome</th>
				<th>Cognome</th>
				<th>Mail</th>
				<th>Cellulare</th>
				<th>Fisso</th>
				<th>Gruppo</th>
				<th>Comandi</th>
			</tr>
			<?php if (count((array)$this->_vars['membri_ombra'])): foreach ((array)$this->_vars['membri_ombra'] as $this->_vars['membro']): ?>
			<tr>
				<td><?php echo $this->_vars['membro']['nome']; ?>
</td>
				<td><?php echo $this->_vars['membro']['cognome']; ?>
</td>
				<td><a href="mailto:<?php echo $this->_vars['membro']['mail']; ?>
"><?php echo $this->_vars['membro']['mail']; ?>
</a></td>
				<td><?php echo $this->_vars['membro']['cellulare']; ?>
</td>
				<td><?php echo $this->_vars['membro']['fisso']; ?>
</td>
				<td><?php echo $this->_vars['membro']['gruppo']; ?>
</td>
				<td>
					<a href="/rubrica/edit/id/<?php echo $this->_vars['membro']['id']; ?>
">Edit</a>
					<a href="/rubrica/delete/id/<?php echo $this->_vars['membro']['id']; ?>
">Delete</a>
			</td>
			</tr>
			<?php endforeach; endif; ?>
		</table>
	</p>
</div>