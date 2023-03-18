<?php
require_once "../../bootstrap/bootstrap.php";


class LogoutPage extends AuthenticatePage
{
    public string $title = "Odlášení uživatele";

    protected function prepareData(): void
    {
        parent::prepareData();
        session_start();
        session_destroy();

        $this->user = null;

        header('Location: ../index.php');
    }

    protected function pageBody(): string
    {
       return "";
    }

}

$login = new LogoutPage();
$login->render();


?>