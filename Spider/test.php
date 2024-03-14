<?php

$email = 'mailto:joursummerschool@gmail.com';
preg_match('/mailto:(.*)/', $email, $email);
$email = $email[1];
echo $email;