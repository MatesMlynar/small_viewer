<?php
require_once "../../bootstrap/bootstrap.php";


class EmployeeUpdatePage extends CRUDPage
{
    public string $title = "Upravit zaměstnance";
    protected int $state;
    private Employee $employee;
    private array $errors;
    private array $rooms = [];

    protected function prepareData(): void
    {
        parent::prepareData();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($_SESSION['admin'])
        {
            $this->rooms = Room::all();
            $this->state = $this->getState();

            switch ($this->state) {
                case self::STATE_FORM_REQUEST:
                    $employeeId = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
                    if (!$employeeId)
                        throw new BadRequestException();

                    $this->employee = Employee::findByID($employeeId);

                    if (!$this->employee)
                        throw new NotFoundException();

                    $this->employee->appendSelected($this->rooms);
                    $this->errors = [];
                    break;

                case self::STATE_DATA_SENT:
                    //načíst data
                    $this->employee = Employee::readPost();
                    //zkontrolovat data
                    $this->errors = [];
                    if ($this->employee->validate($this->errors))
                    {
                        //zpracovat
                        $result = $this->employee->update($this->user->session_id);
                        //přesměrovat
                        $this->redirect(self::ACTION_UPDATE, $result);
                    }
                    else
                    {
                        $this->employee->appendSelected($this->rooms);
                        $this->state = self::STATE_FORM_REQUEST;
                    }
                    break;
            }
        }
        else{
            throw new UnauthorizedException();
        }

    }


    protected function pageBody(): string
    {
            return MustacheProvider::get()->render("employee_form",
                [
                    'employee' => $this->employee,
                    'errors' => $this->errors,
                    'rooms' => $this->rooms,
                    'employeeAdmin' => $this->employee->admin,
                    'session_admin' => $_SESSION['admin'],
                ]);
    }

    protected function getState() : int
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            return self::STATE_DATA_SENT;

        return self::STATE_FORM_REQUEST;
    }

}

$page = new EmployeeUpdatePage();
$page->render();