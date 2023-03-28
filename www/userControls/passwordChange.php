<?php
require_once "../../bootstrap/bootstrap.php";

class PasswordChange extends AuthenticatePage
{
    public string $title = "Změna hesla";
    private string $name = "";

    protected function prepareData(): void
    {
        parent::prepareData();
        $this->name = $this->user->name . " " . $this->user->surname;

        if(isset($_POST['password']))
        {
            $userID = $_SESSION['user_id'];
            $unhashedPassword = $_POST['password'];
            $result = Employee::changePassword($userID, $unhashedPassword);

            if($result)
            {
                header("Location: ../employee/list.php?".http_build_query(['action' => "passwordChange", 'success' => true]));
                exit();
            }
            else
            {
                header("Location: ../employee/list.php?".http_build_query(['action' => "passwordChange", 'success' => false]));
                exit();
            }
        }
    }

    protected function pageBody(): string
    {
        return MustacheProvider::get()->render("passwordChange_form",
            [
                'user' => $this->name,
            ]);
    }

}

$login = new PasswordChange();
$login->render();


?>