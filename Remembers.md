#cose da ricordare

# Introduction #

id = 0 è l'utente guest, quindi attenzione alla tabella user!!!

Es. ACL\_MANAGER

```
$acl_manager = new Sigma_Acl_Manager($user_id,$role,$modulo);

$acl_manager->Acl->allowed($role,$controller,$action);
```
o
```
$acl_manager->Acl->hasPermission($permesso);
```



// MOLTO COMODO :

$request->isXmlHttpRequest()....