<?php

class Employee
{
    public ?int $employee_id;
    public ?bool $admin;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?string $room_name;
    public ?int $room_phone;
    public ?int $room_id;
    public ?array $employee_keys;
    public ?string $login;
    public ?string $password;


    public function __construct(array $rawData = []){
        $this->hydrate($rawData);
    }

    private function hydrate(array $rawData) : void
    {
        if (array_key_exists('employee_id', $rawData))
            $this->employee_id = $rawData['employee_id'];
        if(array_key_exists('admin', $rawData))
            $this->admin = $rawData['admin'];
        if(array_key_exists('name', $rawData))
            $this->name = $rawData['name'];
        if(array_key_exists('surname', $rawData))
            $this->surname = $rawData['surname'];
        if(array_key_exists('job', $rawData))
            $this->job = $rawData['job'];
        if(array_key_exists('wage', $rawData))
            $this->wage = $rawData['wage'];
        if(array_key_exists('room_name', $rawData))
            $this->room_name = $rawData['room_name'];
        if(array_key_exists('room_id', $rawData))
            $this->room_id = $rawData['room_id'];
        if(array_key_exists('room_phone', $rawData))
            $this->room_phone = $rawData['room_phone'];
        if(array_key_exists('keys', $rawData))
            $this->employee_keys = $rawData['keys'];
        if(array_key_exists('password', $rawData))
            $this->password = $rawData['password'];
        if(array_key_exists('login', $rawData))
            $this->login = $rawData['login'];
    }

    public static function all() : array{

        $pdo = PDOProvider::get();

        $query = "select admin, CONCAT(employee.name,' ',employee.surname,' ') as 'name', room.name as 'room_name', room.phone as 'room_phone', employee.job as 'job', employee_id from employee join room on employee.room = room_id";
        $stmt = $pdo->query($query);

        $result = [];
        while($employee = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $result[] = new Employee($employee);
        }

        return $result;
    }


    public static function findByID(int $id) : Employee|null
    {
        $pdo = PDOProvider::get();

        $employeeQuery = $pdo->prepare("select login, password, admin, room as 'room_id', employee_id, employee.name as 'name', surname, job, wage, room.name as 'room_name' from employee join room on employee.room = room.room_id where `employee`.`employee_id`=:employeeID");
        $employeeQuery->execute(['employeeID' => $id]);

        $employee_keysQuery = $pdo->prepare("select room.name as 'keys', room.room_id as 'room_id' from room join `key` on room.room_id = `key`.room where `key`.`employee`=:employeeID");
        $employee_keysQuery->execute(['employeeID' => $id]);

        if ($employeeQuery->rowCount() < 1)
            return null;

        $employee = new Employee($employeeQuery->fetch(PDO::FETCH_ASSOC));
        $employee->employee_keys = $employee_keysQuery->fetchAll(PDO::FETCH_ASSOC);
        return $employee;
    }


    public static function deleteById($id) : bool
    {
        $pdo = PDOProvider::get();

        //smazat všechny klíče, které jsou propojeny s daty v tabulce "employee"
        $keyQuery = "DELETE FROM `key` WHERE employee=:employeeID";
        $keySTMT = $pdo->prepare($keyQuery);
        $keySTMT->execute(['employeeID' => $id]);

        //smazat samotnou osobu
        $query = "DELETE FROM employee WHERE employee_id=:employeeID";

        $stmt = $pdo->prepare($query);
        return $stmt->execute(['employeeID' => $id]);
    }

    public static function readPost() : Employee
    {
        $employee = new Employee();

        $employee->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $employee->name = filter_input(INPUT_POST, 'name', FILTER_DEFAULT);
        $employee->surname = filter_input(INPUT_POST, 'surname', FILTER_DEFAULT);
        $employee->room_name = filter_input(INPUT_POST, 'room', FILTER_DEFAULT);
        $employee->room_id = filter_input(INPUT_POST, 'room_id', FILTER_DEFAULT);
        $employee->job = filter_input(INPUT_POST, 'job', FILTER_DEFAULT);
        $employee->wage = filter_input(INPUT_POST, 'wage', FILTER_VALIDATE_INT);
        if(filter_input(INPUT_POST, 'keys', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY))
        {
            $employee->employee_keys = array_map(function ($e) {return ['room_id' => $e];}, filter_input(INPUT_POST, 'keys', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY));
        }

        $pdo = PDOProvider::get();
        //pokud není posláno žádné heslo, tak původní hodnotu "aktualizujeme" původním heslem
        $oldPasswordQuery = "select password from employee where `employee_id` = :employeeID";
        $oldPasswordValue = $pdo->prepare($oldPasswordQuery);
        $oldPasswordValue->execute(['employeeID' => $employee->employee_id]);
        $stmt = $oldPasswordValue->fetch(PDO::FETCH_ASSOC);

        $newPassword = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
        if(isset($newPassword) && $newPassword !== "")
        {
            $employee->password = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        else
        {
            $employee->password = $stmt['password'];
        }

        $employee->login = filter_input(INPUT_POST, 'login', FILTER_DEFAULT);
        $employee->admin = filter_input(INPUT_POST, 'admin', FILTER_DEFAULT);
        return $employee;
    }

    public static function changePassword($userID, $password) : bool
    {
        $pdo = PDOProvider::get();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "UPDATE employee SET `password` = :password WHERE `employee_id` = :userID";
        $employeeTableData = $pdo->prepare($query);

        return $employeeTableData->execute(['password' => $hashedPassword, 'userID' => $userID]);
    }


    public function validate(array &$errors = []) : bool
    {
        if (is_string($this->name))
            $this->name = trim($this->name);
        if (!$this->name)
            $errors['name'] = "Jméno nemůže být prázdné";

        if (is_string($this->surname))
            $this->surname = trim($this->surname);
        if (!$this->surname)
            $errors['surname'] = "Příjmení nemůže být prázdné";

        if (is_int($this->room_id))
            $this->room_id = trim($this->room_id);
        if (!$this->room_id)
            $errors['room_id'] = "Místnost nemůže být prázdná";

        if (is_string($this->job))
            $this->job = trim($this->job);
        if (!$this->job)
            $errors['job'] = "Pozice nemůže být prázdná";

        if (is_int($this->wage))
            $this->wage = trim($this->wage);
        if (!$this->wage)
            $errors['wage'] = "Plat nemůže být prázdný a musí mít hodnotu celého čísla";

        if (is_string($this->login)){
            //bereme data z databaze kvuli loginu, aby nevznikla duplikace (login by mel byt unikatni)
            $pdo = PDOProvider::get();
            $stmt = $pdo->prepare("SELECT login, employee_id FROM employee where login = :login");
            $stmt->execute(['login' => $this->login]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            switch ($_POST['action'])
            {
                case 'insert':
                    //zjistim, zda stejny login jiz existuje
                    if ($stmt->rowCount() !== 0)
                    {
                        $errors['login'] = "Tento přihlašovací login je již zabrán";
                        break;
                    }
                    else
                    {
                        $this->login = trim($this->login);
                    }
                    break;

                case 'update':
                    //pokud uživatel s aktuálním employee_id (hodnota z databáze) má shodný login i ve formuláři, poté není problém
                    if ($this->employee_id === $employee['employee_id'])
                    {
                        $this->login = trim($this->login);
                        break;
                    }
                    else if($stmt->rowCount() >= 1 && !($this->employee_id === $employee['employee_id']))
                    {
                        $errors['login'] = "Tento přihlašovací login je již zabrán";
                    }
                    break;
            }

        }
        if (!$this->login)
            $errors['login'] = "Login nemůže být prázdný";


        return count($errors) === 0;
    }


    public function insert() : bool
    {
        $pdo = PDOProvider::get();

        //budeme vkladat vse krome klice do tabulky employee
        $employeeTableQuery = "INSERT INTO employee (`name`, `surname`, `room`, `job`, `wage`, `login`, `password`, `admin`) VALUES (:name, :surname, :room, :job, :wage, :login, :password, :admin);";

        $employeeTableData = $pdo->prepare($employeeTableQuery);
        $success = $employeeTableData->execute([
            'name' => $this->name,
            'surname' => $this->surname,
            'room' => $this->room_id,
            'job' => $this->job,
            'wage' => $this->wage,
            'login' => $this->login,
            'password' => $this->password,
            'admin' => $this->admin ? "1" : "0",
        ]);

        //ziskame id zaměstnance (slouží pro přidání dat do tabulky keys)
        $employee_id = $pdo->query("SELECT max(employee_id) as max_id FROM employee");
        $employee_id = $employee_id->fetch(PDO::FETCH_ASSOC);
        $employee_id = $employee_id['max_id'];

        foreach ($this->employee_keys as $key)
        {
            $keyTableQuery = $pdo->prepare("INSERT INTO `key` (`room`, `employee`) VALUES (:room, :employee)");
            $keyTableQuery->execute(['room' => $key['room_id'], 'employee' => $employee_id]);
        }

        return $success;
    }

    public function update() : bool {
        $pdo = PDOProvider::get();

        $employeeTableQuery = "UPDATE employee SET `name` = :name, `surname` = :surname, `room` = :room, `job` = :job, `wage` = :wage, `login` = :login, `password` = :password, admin = :admin  WHERE `employee_id` = :employeeID";
        $employeeTableData = $pdo->prepare($employeeTableQuery);


        $success = $employeeTableData->execute([
            'name' => $this->name,
            'surname' => $this->surname,
            'room' => $this->room_id,
            'job' => $this->job,
            'wage' => $this->wage,
            'employeeID' => $this->employee_id,
            'login' => $this->login,
            'password' => $this->password,
            'admin' => $this->admin ? "1" : "0",
        ]);


        //smažu všechny klíče, které měl uživatel doposud a přepíšu je
        $query = "DELETE FROM `key` WHERE employee=:employeeID";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['employeeID' => $this->employee_id]);

        //přidám nový výběr klíčů
        if(isset($this->employee_keys))
        {
            foreach ($this->employee_keys as $key)
            {
                $keyTableQuery = $pdo->prepare("INSERT INTO `key` (`room`, `employee`) VALUES (:room, :employee)");
                $keyTableQuery->execute(['room' => $key['room_id'], 'employee' => $this->employee_id]);
            }
        }
        return $success;
    }


    public function appendSelected(&$rooms)
    {
        $checkedRooms = array_map(function($e) {return $e['room_id'];}, $this->employee_keys);

        foreach($rooms as &$room)
        {
            if($this->room_id === $room->room_id)
            {
                $room->is_room_selected = true;
            }

            $roomArray = $room->ToArray();
            $room->is_key_selected = in_array($roomArray['room_id'], $checkedRooms);
        }

    }
}
