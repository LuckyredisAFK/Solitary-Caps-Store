<?php

require_once 'vendor/autoload.php';

use Aries\Dbmodel\Models\User;

// Usage example
$user = new User();

// checks if the button is clicked and the form is submitted. If true, run the createUser() function from our User class
// Then pass in an array of data to the createUser class since createUser accepts an array of data
if(isset($_POST['submit'])) {
    $user->createUser([
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'password' => $_POST['password']
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Users</title>
</head>
<body>
    <h1>My List of Users</h1>
    <form method="POST" action="index.php">
        <input type="text" name="name" placeholder="Name">
        <input type="email" name="email" id="" placeholder="Email">
        <input type="password" name="password" placeholder="Password">
        <input type="submit" name="submit" value="Submit">
    </form>
    <?php
        // get all users from the database and return as an array then loop it inside foreach to create a list
        $users = $user->getUsers();
        foreach ($users as $key => $value) {
            echo '<li>'. $value['name'] .'</li>';
        }
    ?>
</body>
</html>