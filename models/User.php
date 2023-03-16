<?php

class User
{
    public ?int $employee_id;
    public ?int $admin;
    public ?string $login;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?int $room;


    public function __construct(array $rawData = []){
        $this->hydrate($rawData);
    }

    private function hydrate(array $rawData) : void
    {
        if (array_key_exists('employee_id', $rawData))
            $this->employee_id = $rawData['employee_id'];
        if(array_key_exists('admin', $rawData))
            $this->admin = $rawData['admin'];
        if(array_key_exists('login', $rawData))
            $this->login = $rawData['admin'];
        if(array_key_exists('name', $rawData))
            $this->name = $rawData['name'];
        if(array_key_exists('surname', $rawData))
            $this->surname = $rawData['surname'];
        if(array_key_exists('job', $rawData))
            $this->job = $rawData['job'];
        if(array_key_exists('wage', $rawData))
            $this->wage = $rawData['wage'];
        if(array_key_exists('room', $rawData))
            $this->room = $rawData['room'];
    }

    public static function findByLogin(string $login, &$errors) : User|null
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `employee` WHERE `login`=:login");
        $stmt->execute(['login' => $login]);

        if($stmt->rowCount() < 1)
        {
            $errors['userNotFound'] = "Učet s těmito přihlašovacími údaji nebyl nalezen";
            return null;
        }

        return new User($stmt->fetch(PDO::FETCH_ASSOC));
    }
    
    public static function validateLogin($request, &$errors)
    {
        if (!$request['login'])
            $errors['login'] = "Uživatelské jméno nemůže být prázdné!";
        if (!$request['password'])
            $errors['password'] = "Heslo nemůže být prázdné!";

        return count($errors) === 0;
    }


}