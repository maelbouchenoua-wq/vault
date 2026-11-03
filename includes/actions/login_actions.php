<?php

/*
L'utilisateur rentre son email et un mot de passe
momo@gmail.com
mdp

On vérifie que les 2 sont bien renseignés

En base de données, le mot de passe est hashé
le hash de mdp est 123456789

select * from users where email='momo@gmail.com' and password = '123456789';

Si aucun utilisateur existe alors afficher le message :
L'émail renseigné et/ou le mot de passe sont érronés !

 */

require '../db.php';
$pdo = getPDO();
$email = $_POST['email'];
$password = $_POST['password'];


if(empty($email)){
    echo "Please enter your email";
}
if(empty($password)) {
    echo "Please enter your password";
}

if (!empty($email) && !empty($password)) {
    echo "email " . $email . ", password " . $password;
    $mdp_hash = password_hash($password, PASSWORD_DEFAULT);

    echo "mdp_hash [" . $mdp_hash . "]    ";

    $query = $pdo->prepare("SELECT * FROM users WHERE email = :email and password = :password");

    $query->execute([':email' => $email, ':password' => $mdp_hash]);


    $result = $query->fetch();

    if (empty($result)) {
        echo "Account or password unknonn";
    }
    var_dump($result);
}