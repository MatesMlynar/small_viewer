<?php
require_once "../../bootstrap/bootstrap.php";

class EmployeeDeletePage extends CRUDPage
{

    protected function prepareData(): void
    {
        parent::prepareData();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($_SESSION['admin'])
        {

            $employeeID = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

            if(!$employeeID)
            {
                throw new BadRequestException();
            }

            $result = Employee::deleteById($employeeID, $_SESSION['user_id']);
            $this->redirect(self::ACTION_DELETE, $result);
        }
        else{
            throw new UnauthorizedException();
        }

    }


    protected function pageBody(): string
    {
        return "";
    }

}

$page = new EmployeeDeletePage();
$page->render();