<?php
$contraseña = "Tomix21";
$hash = password_hash($contraseña, PASSWORD_BCRYPT);
echo $hash;
?>