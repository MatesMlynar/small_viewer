<?php
require_once "../../bootstrap/bootstrap.php";

class PasswordChange extends AuthenticatePage
{
    public string $title = "Změna hesla";
    private string $name = "";
    private array $errors = [];

    protected function prepareData(): void
    {
        parent::prepareData();
        $this->name = $this->user->name . " " . $this->user->surname;

        if(isset($_POST['password']))
        {
            $userID = $_SESSION['user_id'];
            $newPassword = $_POST['password'];
            $oldPassword = $_POST['oldPassword'];
            $newPasswordCheck = $_POST['passwordNewCheck'];
            $result = Employee::changePassword($userID, $newPassword, $oldPassword,$newPasswordCheck, $this->errors);

            if($result)
            {
                header("Location: ../employee/list.php?".http_build_query(['action' => "passwordChange", 'success' => true]));
                exit();
            }
        }
    }

    protected function pageBody(): string
    {
        return MustacheProvider::get()->render("passwordChange_form",
            [
                'user' => $this->name,
                'errors' => $this->errors
            ]);
    }

}

$login = new PasswordChange();
$login->render();


?>