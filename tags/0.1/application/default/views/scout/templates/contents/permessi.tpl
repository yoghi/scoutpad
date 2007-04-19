<div id="contents">

	<div class="testo">
			<h3>Modifica Permessi di Accesso</h3>
			Qui &egrave; possibile limitare l'accesso ad ogni singolo modulo (o plugin), controller e ad ogni singola azione, eseguita sul sistema. 
			Il sistema di <em>default</em> nega qualunque richiesta ai moduli diversi da quello principale. Ci&oacute; significa che dovete essere voi ad autorizzare
			ogni gruppo ad accedere ad un singolo modulo, controller e a permettergli determinata azioni su quel controller. Ovviamente avrete anche la capacit&agrave;
			di dare a tutti la possibilit&agrave; di accesso settando a <em>null</em> il campo Role. 
			<br/>

<!-- 

			<table style="padding-left: 10px;padding-top: 10px;">
				<caption style="padding-top: 10px;"><strong>TABELLA ACL (Esempio)</strong></caption>
				<tr>
					<th style="font-style: oblique;">Id</th>
					<th style="font-style: oblique;">Modulo</th>
					<th style="font-style: oblique;">Controller</th>
					<th style="font-style: oblique;">Action</th>
					<th style="font-style: oblique;">Role</th>
				</tr>
				<tr>
					<td>1</td>
					<td>default</td>
					<td>index</td>
					<td style="font-style: oblique;">NULL</td>
					<td style="font-style: oblique;">NULL</td>
				</tr>
				<tr>
					<td>2</td>
					<td>admin</td>
					<td>permessi</td>
					<td>index</td>
					<td style="font-style: oblique;">NULL</td>
				</tr>
				<tr>
					<td>3</td>
					<td>admin</td>
					<td>permessi</td>
					<td>change</td>
					<td>admin</td>
				</tr>
			</table>
 -->

		<h3>{$title_acl}</h3>
		
		{foreach from=$acl_list key=role value=acl_modulo  }
				
				<table>

					<tr>
						<th>&nbsp;Id)&nbsp;</th>
						<th>&nbsp;Modulo&nbsp;</th>
						<th>-&gt;</th>
						<th>&nbsp;Controller&nbsp;</th>
						<th>-&gt;</th>
						<th>&nbsp;Action&nbsp;</th>
						<th>&nbsp;Comandi Vari&nbsp;</th>						
					</tr>
		
					{foreach from=$acl_modulo key=id value=acl }
					<tr align="center">
						<td>{$id})</td>
						<td>{$acl.Modulo}</td>
						<td>-&gt;</td>
						<td>{$acl.Controller}</td>
						<td>-&gt;</td>
						<td>{$acl.Action}</td>
						<td>
							
							<ul style="list-style: none;">								
								<li style="display: inline;"><a href="/admin/permessi/remove/id/{$id}">remove</a></li>
								<li style="display: inline;margin-left: 1.4em;"><a href="/admin/permessi/edit/id/{$id}">edit</a></li>
								<li style="display: inline;margin-right: 1.4em;">&nbsp;</li> 
							</ul>
						
						</td>
					</tr>
					{/foreach}
					
				</table>
	
		{/foreach}
		
	 </div>
	 
	 <form class="form" name="queryform" action="/admin/permessi/" method="post">
		<fieldset>
			<legend>Ricerca</legend>
			<div>
				<label>Modulo : </label>	
				<select name="modulo">
					{ html_options options=$module_options selected=$current_modulo }
				</select>
				<label>Gruppo : </label>
				<select name="role">
					{ html_options options=$role_options selected=$current_role }
				</select>
			</div>
			<div class="submit">
				<input type="submit" name="submit" id="submit" value="{$buttonText} &raquo;" tabindex="3" />
			</div>
		</fieldset>
	</form>
	 
	 <form class="form" name="addform" action="{$base_url}/add/" method="post">
		<fieldset>
			<legend>Aggiungi ACL</legend>
			<div>
				<label>Quando aggiungi una ACL ricorda sempre che per selezionare "tutti" la parola chiave &eacute; <em>null</em></label>
			</div>
			<div>
				<label>Modulo : </label>	
				<select style="width:100%" name="modulo">
					{ html_options options=$module_options selected=$current_modulo }
				</select>
			</div>
			<div>
				<label>Controller : </label>	
				<input style="width:100%" type="text" name="controller" id="controller" value="{$current_controller}" size="20" tabindex="1" />
			</div>
			<div>
				<label>Action : </label>	
				<input style="width:100%" type="text" name="action" id="action" value="{$current_action}" size="20" tabindex="2" />
			</div>
			<div>
				<label>Gruppo : </label>
				<select name="role" style="width:100%">
					{ html_options options=$role_options selected=$current_role }
				</select>
			</div>
			<div class="submit">
				<input type="submit" name="submit" id="submit" value="Aggiungi &raquo;" tabindex="3" />
			</div>
		</fieldset>
	</form>
	 
</div>