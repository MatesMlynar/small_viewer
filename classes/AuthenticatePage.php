<?php


abstract class AuthenticatePage extends Page
{
    protected ?User $user;

    protected function prepareData(): void
    {
        session_start();
        if(!isset($_SESSION['userName']))
        {
            header("Location: ../index.php?".http_build_query(['action' => "unauthenticated"]));
            exit();
        }

        $this->user = User::findBySession();

    }
}

