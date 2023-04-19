<?php
require_once "../../bootstrap/bootstrap.php";

//FORM request se používá pro zobrazení formuláře pro úpravu místnosti (pokud se formulář pouze zobrazuje, je nastaven tento stav)
//STATE_DATA_SENT - v případě, kdy se form. odesílá

class RoomUpdatePage extends CRUDPage
{
    public string $title = "Upravit místnost";
    protected int $state;
    private Room $room;
    private array $errors;

    protected function prepareData(): void
    {
        parent::prepareData();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($_SESSION['admin'])
        {
            //načte stav stránky a podle toho zobrazí formulář pro editaci místnosti nebo přijme odeslaná data a aktualizuje DB
            $this->state = $this->getState();

            switch ($this->state) {
                case self::STATE_FORM_REQUEST:
                    $roomId = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT);
                    if (!$roomId)
                        throw new BadRequestException();

                    $this->room = Room::findByID($roomId);
                    if (!$this->room)
                        throw new NotFoundException();

                    $this->errors = [];
                    break;

                case self::STATE_DATA_SENT:
                    //načíst data
                    $this->room = Room::readPost();
                    //zkontrolovat data
                    $this->errors = [];
                    if ($this->room->validate($this->errors))
                    {
                        //zpracovat
                        $result = $this->room->update($this->errors);
                        //přesměrovat
                        if($result)
                        {
                            $this->redirect(self::ACTION_UPDATE, $result);
                        }
                    }
                    else
                    {
                        //na formulář
                        $this->state = self::STATE_FORM_REQUEST;
                    }
                    break;
            }
        }
        else{
            throw new UnauthorizedException();
        }

    }


    protected function pageBody(): string
    {
        return MustacheProvider::get()->render("room_form",
            [
                'room' => $this->room,
                'errors' => $this->errors
            ]);
        //vyrenderuju
    }

    protected function getState() : int
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            return self::STATE_DATA_SENT;

        return self::STATE_FORM_REQUEST;
    }

}

$page = new RoomUpdatePage();
$page->render();