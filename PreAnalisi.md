# Utente #

Chi si avvicina a questo sistema puo essere sia un ragazzo sia un adulto.

In entrambi i casi :

  * deve valere la regola del minimo privilegio
  * la responsabilità dell'utente viene espressa mediante _ruoli_
  * è possibile ricoprire più _ruoli_
  * è possibile che appartenga a più _gruppi logici_
  * un ruolo ha un periodi di valità limitato o illimitato

PS: in futuro sarà possibile anche pensare a delle _eccezzioni_ ai ruoli ma per ora meglio essere inflessibili.


# Ruolo #

Un ruolo ha in se il concetto di eredità; per esemplificare

_ruolo SuperPippo eredita da Pippo significa che ha tutte le responsaiblità di Pippo, più eventuali nuove responsabilità_

PS: per semplicità la ereditarietà multipla non viene considerata.

Un ruolo **ha o non ha** accesso ad una specifica risorsa, questo significa che la sua granularità non è molto fina.

# Risorsa #

Il sistema è visto come N moduli che poggiano su una infrastruttura comune; questi moduli sono fruitori delle risorse del sistema e sono anche essi risorsa del sistema. Questo significa che per accedere alle risorse dell'infrastruttura di base i moduli useranno i permessi di accesso dei loro utilizzatori; poiche il sistema dara accesso ai moduli in base ai permessi su di loro, questo significa che sarà necessario specificare sia i permessi sui moduli sia sulle parti comuni. Questo può sembrare molto esoso ma permette di controllare la sicurezza di tutti i dati che vengono manipolati sia il come vengono manipolati.

Es. il documento elenco ragazzi voglio che sia acessibile (visualizzare) a tutti i ragazzi e ai capi (che ne hanno diritto),
ma voglio che venga usato il modulo X (che visualizza dati non sensibili) dai ragazzi e il modulo Y dai capi autorizzati in quanto il modulo Y può prevedere anche la possibilità di modifica di tali dati.

In questo caso X e Y possono essere usati da tutti, solo che se un ragazzo provasse ad usare Y per accedere a quei dati (che lui cmq. potrebbe vedere) non gli deve essere consentito.

Viceversa in alcuni moduli può essere consentito l'accesso solo agli sviluppatori in quanto ancora non testati, sarebbe quindi non un problema di quali dati si visualizzano ma nel voler dare o meno una risorsa ad un determinato ruolo.


# Conclusioni #

Tutto questo è possibile esplicitarlo con le ACL (Access Control List) ed eventualmente estenderlo con le [Permission](Permission.md) per ottenere un grado maggiore di dettaglio sui permessi.
Come si è capito il controllo non è su singolo utente, ma su un gruppo di utenti, che esso corrisponda ad un solo utente o a più utenti;

**Ci sarà proliferazione di ruoli? si certo se non viene usata la dovuta attenzione.**

La creazione dei ruoli per facilità sarebbe il caso che fosse sempre rappresentata come un albero, in modo da coprire tutte le aree in maniera intelligente.

Es:

  * admin (entita che non eredita da nessuno)
  * guest (utente generico, sconosciuto al sistema) - nessun privilegio -

  * member => staff => responsabile => superresponsabile

questa catena allarga sempre i privilegi su una serie di dati prodotti da sempre più persone; ovviamente più si sale meno utenti hanno tale ruolo.

Allo stesso tempo è necessario distinguere _member_ di cosa, ci possono essere più gruppi logici al cui interno c'è un member sarà quindi opportuna specificare bene questa cosa:
_radioamatori\_member_ o _calciatori\_staff_. Per rendere la cosa trasparente si può a questo punto pensare al ruolo sempre a una tupla di due valori

**(gruppo logico, ruolo logico)**