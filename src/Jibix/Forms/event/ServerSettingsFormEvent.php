<?php
namespace Jibix\Forms\event;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;


/**
 * Class ServerSettingsFormEvent
 * @package Jibix\Forms\event
 * @author Jibix
 * @date 05.04.2023 - 19:58
 * @project Forms
 */
class ServerSettingsFormEvent extends PlayerEvent{

    public function __construct(Player $player){
        $this->player = $player;
    }
}