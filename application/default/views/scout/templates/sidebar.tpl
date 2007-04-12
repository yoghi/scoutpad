<div id="sidebar">
	
	<em>Menu</em>
	
	<div class="menu">
		<ul id="links">			
			<li><a href="/staff/" title="Chi organizza?" >Lo Staff</a></li>
			<li><a href="/torriana/" title="Luogo : Torriana">Dove si svolge</a></li>
			<li><a href="/file/url/scheda_iscrizione.zip" title="">Scheda Iscrizione</a></li>
			<li><a href="/faq/" title="Qualcuno forse ha gi&agrave; fatto la domanda a cui cerchi risposta...">Domande Frequenti</a></li>
			<li><a href="/contact/" title="Vuoi parlarci?">Per Contattarci</a></li>
		</ul>
	</div>
	
	<em>Stato Iscrizioni</em>
	
	<div class="menu">
		<ul style="color:black;">			
			<li>Astronomi 0/8</li>
			<li>Campeggiatori 0/15</li>
			<li>Cucinieri 0/15</li>
			<li>Maestri Giochi 0/8</li>
			<li>Topografi 0/8</li>
		</ul>
	</div>
	
	{if isset($info_user)}
	 
	<em>Bacheca di {$info_user} </em>
	
	<div class="menu">
		<ul>
			<li><a href="/login/out/">Logout</a></li>
			<li><a href="/mail/">Casella di Posta</a></li>
			<li><a href="/todo/">To Do List</a></li>
			<li><a href="/image/add">Upload Image</a></li>
		</ul>
	</div>
	 
	{else}
	 
	<em>Staff</em>
	
	<div class="menu">
		<ul id="links">			
			<li class="secure"><a href="/login/" title="ALT!! Farsi Riconoscere">Login</a></li>
		</ul>
	</div>
	
	{/if}
	
	<em>Siti Utili</em>
	
	<div class="menu">
		<ul id="links">			
			<li class="external"><a href="http://www.agesci.org" title="Se non sai cos&egrave; indaga!!">Agesci</a></li>
			<li class="external"><a href="http://www.emiro.agesci.it" title="Anche la Regione ha un suo spazio...">Sito Regionale</a></li>
		</ul>
	</div>
	
	<!--
	<div class="nomenu">
		<ul>
			 			
			<li class="external"><a href="http://www.agesci.org/ospiti/centenario"><img src="http://www.agesci.org/img/sitiweb/centenario_small.gif"/></a></li>
			 
		</ul>
	</div>
	-->
	
</div>