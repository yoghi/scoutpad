# Introduction #

Permission is a extension of canonical ACL.
On default all people can READ but you must specify other permissions or to deny access;

_Utile se si vuole dare accesso ad una risorsa in sola lettura e scrittura , ma non nell'aggiunta di parametri; oppure per negare la scrittura ad un singolo utente una data risorsa ma non la sua visualizzazione._


## The list of permissions ##

  * READ
  * WRITE
  * EXECUTE
  * NOTREAD (_the user can't read the output_)


# Usage #

```
$permission = new Sigma_Acl_Permission();
$permission->hasPermission($user_id,$module_name,$controller_name,$power_to_test);
```

or better

```
$acl = new Sigma_Acl($user_id,$role_name,$module_name);
$acl->hasPermission($controller_name,$power_to_test);
```

**NB: per ora è stato disabilitato per semplicità nello sviluppo delle varie componenti**
_riga 210 file Sigma\_Plugin\_Auth.php_