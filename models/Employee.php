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
    public ?string $employee_keys;

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
        if(array_key_exists('employee_keys', $rawData))
            $this->employee_keys = $rawData['employee_keys'];
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

        $employee = $pdo->prepare("select employee_id, employee.name as 'name', surname, job, wage, room.name as 'room_name' from employee join room on employee.room = room.room_id where `employee`.`employee_id`=:employeeID");
        $employee->execute(['employeeID' => $id]);

        $employee_keys = $pdo->prepare("select room.name as 'employee_keys', room.room_id as 'room_id'  from room join `key` on room.room_id = `key`.room where `key`.`employee`=:employeeID");
        $employee_keys->execute(['employeeID' => $id]);

        if ($employee->rowCount() < 1)
            return null;

        $employeeData = $employee->fetch(PDO::FETCH_ASSOC);
        $employeeData['employee_keys'] = MustacheProvider::get()->render("employee_keys", ['employee_keys' => $employee_keys->fetchAll(PDO::FETCH_ASSOC), 'employee' =>  $employeeData]);

        return new Employee($employeeData);
    }
}