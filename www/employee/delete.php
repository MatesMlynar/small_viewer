<?php
require_once "../../bootstrap/bootstrap.php";

class EmployeeDeletePage extends CRUDPage
{

    protected function prepareData(): void
    {
        parent::prepareData();

        $employeeID = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

        if(!$employeeID)
        {
            throw new BadRequestException();
        }

        $result = Employee::deleteById($employeeID);
        $this->redirect(self::ACTION_DELETE, $result);
    }


    protected function pageBody(): string
    {
        return "";
    }

}

$page = new EmployeeDeletePage();
$page->render();