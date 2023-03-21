<?php
require_once "../../bootstrap/bootstrap.php";

class EmployeeDetailPage extends AuthenticatePage
{
    private $employee;



    protected function prepareData(): void
    {
        parent::prepareData();

        $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);

        if(!$employee_id)
        {
            throw new BadRequestException();
        }

        $this->employee = Employee::findByID($employee_id);
        if(!$this->employee)
        {
            throw new NotFoundException();
        }

        $this->title = htmlspecialchars( "Zaměstnanec {$this->employee->name} ({$this->employee->surname})" );

    }


    protected function pageBody(): string
    {
       return MustacheProvider::get()->render("employee_detail", ["employee" => $this->employee]);
    }


}

$page = new EmployeeDetailPage();
$page->render();