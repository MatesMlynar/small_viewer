<?php
require_once "../bootstrap/bootstrap.php";

class IndexPage extends AuthenticatePage
{
    public string $title = "Prohlížeč databáze";
    private ?User $user;
    private array $errors;

    protected function prepareData(): void
    {
        parent::prepareData();

        $this->errors = [];
        if($_POST){

            if(User::validateLogin($_POST, $this->errors))
            {
                $userLogin = filter_input(INPUT_POST, "login");
                $this->user = User::findByLogin($userLogin, $this->errors);

                if(isset($this->user))
                {
                    header("Location: /room/list.php");
                    exit;
                }
            }


        }


    }


    protected function pageBody(): string
    {
        $html = $this->alert();

        /*$rooms = Room::all(['phone' => 'DESC']);*/

        $html .= MustacheProvider::get()->render("index", ['errors' => $this->errors]);

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
