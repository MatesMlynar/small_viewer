<?php
require_once "../../bootstrap/bootstrap.php";

class EmployeeListPage extends CRUDPage
{
    public string $title = "Seznam zaměstnanců";

    protected function prepareData(): void
    {
        parent::prepareData();
    }

    protected function pageBody(): string
    {
      $html = $this->alert();



      //získat data o zaměstnancích
      $employees = Employee::all();

      foreach($employees as &$employee)
      {
          //disable delete button pro přihlášeného uživatele (admina)
          $employee->ableToManage = $employee->employee_id === $_SESSION['user_id'];
      }

      $html .= MustacheProvider::get()->render("employee_list", ["employees" => $employees, "is_admin" => $_SESSION['admin']]);
      return $html;
    }

    private function alert() : string
    {
        $action = filter_input(INPUT_GET, 'action');
        if (!$action)
            return "";

        $success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
        $data = [];

        switch ($action)
        {
            case self::ACTION_INSERT:
                if ($success === 1)
                {
                    $data['message'] = 'Osoba byla vytvořena';
                    $data['alertType'] = 'success';
                }
                else
                {
                    $data['message'] = 'Chyba při vytvoření osoby';
                    $data['alertType'] = 'danger';
                }
                break;

            case self::ACTION_DELETE:
                if ($success === 1)
                {
                    $data['message'] = 'Osoba byla odebrána';
                    $data['alertType'] = 'success';
                }
                else
                {
                    $data['message'] = 'Chyba při odebrání osoby';
                    $data['alertType'] = 'danger';
                }
                break;
            case self::ACTION_UPDATE:
                if($success === 1)
                {
                    $data['message'] = 'Osoba byla upravena';
                    $data['alertType'] = 'success';
                }
                else
                {
                    $data['message'] = 'Chyba při úpravě osoby';
                    $data['alertType'] = 'danger';
                }
                break;
            default:
                if($success === 1)
                {
                    $data['message'] = 'Heslo bylo změněno';
                    $data['alertType'] = 'success';
                    break;
                }
                else
                {
                    $data['message'] = 'Nastala chyba při změně hesla';
                    $data['alertType'] = 'danger';
                    break;
                }
                break;

        }

        return MustacheProvider::get()->render("alert", $data);
    }


}

$page = new EmployeeListPage();
$page->render();