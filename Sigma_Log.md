# Introduction #

Sigma\_Log eredita da [Zend\_Log](http://framework.zend.com/manual/en/zend.log.html); ha come caratteristica di avere aggiunto un ulteriore livello di priorita : **AUDIT** e di poter essere inizializzato attraverso un array di opzioni o attraverso [Zend\_Config](http://framework.zend.com/manual/en/zend.config.html) (file .ini per esempio)


# Caratteristiche #

  * Nuovo livello di priorita _AUDIT_
  * Configurazione attraverso _array_ o _[Zend\_Config](http://framework.zend.com/manual/en/zend.config.html)_
  * funzioni di log dirette senza dover specificare il livello, il nome della funzione è il livello. (più veloce del metodo call del Zend\_Log)