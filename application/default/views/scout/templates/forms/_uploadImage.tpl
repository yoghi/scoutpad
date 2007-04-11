<div id="contents">

	<div class="testo">
			<h3>Inserire un'immagine</h3>
			Per inserire l'immagine : 
			<ul>
				<li>Cliccate su <em>Browser</em> per scegliere quale file inserire nel sistema</li>
				<li>Scegliete il tipo di immagine che state per caricare (&Eacute; bene essere ordinati)</li>
				<li>poi cliccate su <em>Upload</em> per caricarlo</li> 
			</ul>
			Alla fine della procedura se &egrave; andato tutto bene verr&aacute; visualizzata l'immagine inserita;
			potete copiare l'url per usare l'immagine esternamente direttamente dal vostro browser nella barra degli indirizzi.
			Inoltre potrete	usare alcune featers del sistema per ottenere l'immagine manipolata, Es. reflect, tint.
	</div>

	<form class="form" action="/image/{$action}" method="post" enctype="multipart/form-data">
		<p>
			<label>Percorso locale del file : </label>
		</p>
		<p>
			<!-- il valore della dimensione massima &egrave; in byte -->
			<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
			<input type="file" id="file" name="ufile" size="50" accept="image/gif,image/jpeg,image/png" />
		</p>
		<p>
			<label>Tipo di immagine :</label>
		</p>
		<p> 
				<select name="type" id="select">
					<option value="foto">foto</option>
					<option value="emoticons">emoticons</option>
					<option value="icons">icons</option>
					<option value="unknown">- non appartiene ai precedenti -</option>
				</select>
		</p>
		<p class="submit">
			<input type="submit" name="submit" value="{$buttonText} &raquo;" />
		</p>
	</form>

</div>