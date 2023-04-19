<?php

class User extends Employee
{
    public function __construct(array $rawData = []){
        parent::__construct($rawData);
    }

    public static function findByLogin(string $login, string $passwordPOST, &$errors) : User|null
    {
        $pdo = PDOProvider::get();



        $user = $pdo->prepare("SELECT * FROM `employee` WHERE `login`=:login");
        $user->execute(['login' => $login]);
        if($user->rowCount() < 1)
        {
            $errors['userNotFound'] = "Učet s těmito přihlašovacími údaji nebyl nalezen";
            return null;
        }
        else
        {
            $user = $user->fetch(PDO::FETCH_ASSOC);
            $userPasswordInDB = $user['password'];

            //porovnání hesel
            if(password_verify($passwordPOST, $userPasswordInDB))
            {
                //do nově přihlášeného uživatele vložíme jeho sessionID
                $sessionID = session_id();
                $userSessionID = $pdo->prepare("UPDATE `employee` SET `session_id`=:sessionID WHERE `login`=:login");
                $userSessionID->execute(['login' => $login, 'sessionID' => $sessionID]);
                return new User($user);
            }
            else{
                $errors['password'] = "Špatné heslo";
                return null;
            }

        }

        return null;
    }

    public static function findBySession() : User|null
    {
        $pdo = PDOProvider::get();
        $user = $pdo->prepare("SELECT * FROM `employee` WHERE `employee_id`=:employeeID");
        $user->execute(['employeeID' => $_SESSION['user_id']]);
        $user = $user->fetch(PDO::FETCH_ASSOC);
        return new User($user);
    }


    public static function validateLogin($request, &$errors) : bool
    {
        if (!$request['login'])
            $errors['login'] = "Uživatelské jméno nemůže být prázdné!";
        if (!$request['password'])
            $errors['password'] = "Heslo nemůže být prázdné!";

        return count($errors) === 0;
    }



}