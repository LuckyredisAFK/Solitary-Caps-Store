<?php

require_once 'vendor/autoload.php';

use Aries\Dbmodel\Models\User;

// Usage example
$user = new User();
$users = $user->getUsers();
echo '<pre>';
print_r($users);
echo '</pre>';
?>