<?php
require_once "../../bootstrap/bootstrap.php";

class RoomDeletePage extends CRUDPage
{

    protected function prepareData(): void
    {
        parent::prepareData();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($_SESSION['admin'])
        {
            $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
            if (!$roomId)
                throw new BadRequestException();

            try{
                $result = Room::deleteById($roomId);
                $this->redirect(self::ACTION_DELETE, $result);
            }
            catch (Exception $e)
            {
                $e = new ForbiddenDelete();
                $exceptionPage = new ExceptionPage($e);
                $exceptionPage->render();
                exit;
            }
        }
        else{
            throw new UnauthorizedException();
        }
    }


    protected function pageBody(): string
    {
        return "";
    }
}

$page = new RoomDeletePage();
$page->render();