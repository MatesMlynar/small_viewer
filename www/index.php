<?php
require_once "../bootstrap/bootstrap.php";

class IndexPage extends Page
{
    public string $title = "Prohlížeč databáze";

    protected function pageBody(): string
    {
        $rooms = Room::all(['phone' => 'DESC']);
        dump($rooms);
        return "";
    }
}

$page = new IndexPage();
$page->render();

?>
