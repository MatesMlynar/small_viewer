<?php

abstract class AuthenticatePage extends Page
{



    protected function isLogged()
    {
        header("Location: index.php".http_build_query(['action' => ""]));
        exit();
    }
}

