<?php

abstract class Page
{
    public string $title;
    protected ?User $user;

    protected function prepareData() : void {

    }

    protected function HTTPHeaders() : void {

    }

    protected function HTMLHead() : string {
        return MustacheProvider::get()->render("html_head", ["title" => $this->title]);
    }

    protected function pageHeader() : string {
        $userName = "";
        $isLogged = false;

        if(isset($_SESSION['userName']))
        {
            $userName = $_SESSION['userName'];
            $isLogged = true;
        }


        return MustacheProvider::get()->render("page_header", ["userName" => $userName, "loggedIn" => $isLogged]);
    }

    protected abstract function pageBody() : string;

    public function render() : void {
        try {
            $this->prepareData();

            //pošle http hlavičky
            $this->HTTPHeaders();

            $pageData = [];
            //získá hlavičky
            $pageData["htmlHead"] = $this->HTMLHead();

            //získá záhlaví
            $pageData["pageHeader"] = $this->pageHeader();

            //získá tělo stránky
            $pageData["pageBody"] = $this->pageBody();

            //předá šabloně stránky data pro vykreslení
            echo MustacheProvider::get()->render("page", $pageData);
        }

        catch (BaseException $e) {
            $exceptionPage = new ExceptionPage($e);
            $exceptionPage->render();
            exit;
        }

        catch (Exception $e) {
            if (AppConfig::get('debug'))
                throw $e;

            $e = new BaseException();
            $exceptionPage = new ExceptionPage($e);
            $exceptionPage->render();
            exit;
        }
    }
}