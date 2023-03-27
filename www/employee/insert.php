<?php
require_once "../../bootstrap/bootstrap.php";

class EmployeeInsertPage extends CRUDPage
{
    public string $title = "Vytvořit novou osobu";
    protected int $state;
    private Employee $employee;
    private array $rooms = [];


    protected function prepareData(): void
    {
        parent::prepareData();
        $this->rooms = Room::all();

        $this->state = $this->getState();

        switch ($this->state)
        {
            case self::STATE_FORM_REQUEST:
                $this->employee = new Employee();
                $this->errors = [];
                break;

            case self::STATE_DATA_SENT:
                $this->employee = Employee::readPost();
                $this->errors = [];
                if($this->employee->validate($this->errors))
                {
                    $result = $this->employee->insert();
                    $this->redirect(self::ACTION_INSERT, $result);
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
        return MustacheProvider::get()->render("employee_form",
            [
                'employee' => $this->employee,
                'errors' => $this->errors,
                'rooms' => $this->rooms,
            ]);
        //vyrenderuju
    }

    protected function getState() : int{
        if($_SERVER['REQUEST_METHOD'] === 'POST')
            return self::STATE_DATA_SENT;

        return self::STATE_FORM_REQUEST;
    }

}

$page = new EmployeeInsertPage();
$page->render();