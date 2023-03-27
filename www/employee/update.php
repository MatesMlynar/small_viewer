<?php
require_once "../../bootstrap/bootstrap.php";

//FORM request se používá pro zobrazení formuláře pro úpravu místnosti (pokud se formulář pouze zobrazuje, je nastaven tento stav)
//STATE_DATA_SENT - v případě, kdy se form. odesílá

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
                    $result = $this->employee->update();
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


    protected function pageBody(): string
    {
        //pokud se jedná o přihlášeného uživatele, tak může spravovat své heslo


        return MustacheProvider::get()->render("employee_form",
            [
                'employee' => $this->employee,
                'errors' => $this->errors,
                'rooms' => $this->rooms,
                'session_admin' => $_SESSION['admin'],
            ]);
        //vyrenderuju
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