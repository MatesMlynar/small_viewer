<?php

class Employee
{
    public ?int $employee_id;
    public ?int $admin;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?string $room_name;
    public ?int $room_phone;
    public ?int $room_id;
    public ?array $employee_keys;

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
    }

    public static function all() : array{

        $pdo = PDOProvider::get();

        $query = "select CONCAT(employee.name,' ',employee.surname,' ') as 'name', room.name as 'room_name', room.phone as 'room_phone', employee.job as 'job', employee_id from employee join room on employee.room = room_id";
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

        $employeeQuery = $pdo->prepare("select employee_id, employee.name as 'name', surname, job, wage, room.name as 'room_name' from employee join room on employee.room = room.room_id where `employee`.`employee_id`=:employeeID");
        $employeeQuery->execute(['employeeID' => $id]);

        $employee_keysQuery = $pdo->prepare("select room.name as 'keys', room.room_id as 'room_id'  from room join `key` on room.room_id = `key`.room where `key`.`employee`=:employeeID");
        $employee_keysQuery->execute(['employeeID' => $id]);

        if ($employeeQuery->rowCount() < 1)
            return null;

        $employee = new Employee($employeeQuery->fetch(PDO::FETCH_ASSOC));
        $employee->employee_keys = $employee_keysQuery->fetchAll(PDO::FETCH_ASSOC);

        return $employee;
    }


    public static function deleteById($id) : bool
    {
        $query = "DELETE FROM employee WHERE employee_id=:employeeID";

        $pdo = PDOProvider::get();

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
        $employee->job = filter_input(INPUT_POST, 'job', FILTER_DEFAULT);
        $employee->wage = filter_input(INPUT_POST, 'wage', FILTER_VALIDATE_INT);
        $employee->employee_keys = filter_input(INPUT_POST, 'keys', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        return $employee;
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

        if (is_string($this->room_name))
            $this->room_name = trim($this->room_name);
        if (!$this->room_name)
            $errors['room'] = "Místnost nemůže být prázdná";

        if (is_string($this->job))
            $this->job = trim($this->job);
        if (!$this->job)
            $errors['job'] = "Pozice nemůže být prázdná";

        if (is_int($this->wage))
            $this->wage = trim($this->wage);
        if (!$this->wage)
            $errors['wage'] = "Plat nemůže být prázdný a musí mít hodnotu celého čísla";

        return count($errors) === 0;
    }


    public function insert() : bool
    {
        $success = false;


        //budeme vkladat vse krome klice do tabulky employee
        $employeeTableQuery = "INSERT INTO employee (`name`, `surname`, `room`, `job`, `wage`) VALUES (:name, :surname, :room, :job, :wage);";
        $pdo = PDOProvider::get();

        //do tabulky key budeme vkladat data na zaklade employee_id a room_id

        //1.) získáme uživatele podle "name" a "surname" //TODO dodělat login a password (pro uniq)
        $employeeIDQuery = "SELECT employee_id from employee where `name` = :name AND `surname` = :surname";
        $employee_id = $pdo->prepare($employeeIDQuery);
        $employee_id->execute(['name' => $this->name, 'surname' => $this->surname]);
        $finalEmployeeID = $employee_id->fetch(PDO::FETCH_ASSOC);

        //2.) projdeme cyklem pole "employee_keys" a v každé iteraci uděláme dotaz do DB pro vložení



        $keyTableQuery = "";



        $employeeTableData = $pdo->prepare($employeeTableQuery);
        $success = $employeeTableData->execute([
            'name' => $this->name,
            'surname' => $this->surname,
            'room' => $this->room_name,
            'job' => $this->job,
            'wage' => $this->wage,
        ]);



        return $success;
    }


}