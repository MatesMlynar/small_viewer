<?php
require_once "../../bootstrap/bootstrap.php";


class LoginPage extends Page
{
    public string $title = "Přihlášení uživatele";


    protected function pageBody(): string
    {
        return MustacheProvider::get()->render("login", []);
    }

}

$login = new LoginPage();
$login->render();


?>