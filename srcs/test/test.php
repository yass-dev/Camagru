<?php

require_once('../YassFramework/mailer/mailer.php');

Mailer::setFrom("yass.490@camagru.com");
Mailer::sendRaw('yassou200121@gmailcom', 'Active your acccount man', "test");

?>