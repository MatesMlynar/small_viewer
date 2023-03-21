<?php
require_once "../bootstrap/bootstrap.php";

class IndexPage extends AuthenticatePage
{
    public string $title = "Prohlížeč databáze";
    private array $errors;
    private $predefinedFormLogin = "";

    protected function prepareData(): void
    {
        $this->errors = [];
        if($_POST){

            if(User::validateLogin($_POST, $this->errors))
            {
                $userLogin = filter_input(INPUT_POST, "login");
                $userPassword = filter_input(INPUT_POST, "password");
                $this->user = User::findByLogin($userLogin,$userPassword, $this->errors);

                if(isset($this->user))
                {
                    session_start();
                    $_SESSION['userName'] = $this->user->name . " " . $this->user->surname;
                    $_SESSION['admin'] = $this->user->isAdmin();
                    //presmerujeme
                    header("Location: /room/list.php");
                    exit;
                }
            }

            $this->predefinedFormLogin = $userLogin;
        }
    }

    protected function pageBody(): string
    {
        $html = $this->alert();

        /*$rooms = Room::all(['phone' => 'DESC']);*/

        $html .= MustacheProvider::get()->render("index", ['errors' => $this->errors, 'predefinedLogin' => $this->predefinedFormLogin]);

        return $html;
    }

    private function alert() : string{

        $action = filter_input(INPUT_GET, 'action');


        if(!$action)
        {
            return "";
        }
        else
        {
           $data['message'] = "Pro vstup do systému je potřeba přihlášení";
           $data['alertType'] = "danger";
        }

        return MustacheProvider::get()->render("alert", $data);

    }

}

$page = new IndexPage();
$page->render();

?>
