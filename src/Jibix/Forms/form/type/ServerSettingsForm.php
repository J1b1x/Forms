<?php
namespace Jibix\Forms\form\type;
use Closure;
use Jibix\Forms\menu\Image;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\player\Player;


/**
 * Class ServerSettingsForm
 * @package Jibix\Forms\form\type
 * @author Jibix
 * @date 05.04.2023 - 17:43
 * @project Forms
 */
class ServerSettingsForm extends CustomForm{

    public function __construct(
        string $title,
        array $elements,
        Closure $onSubmit,
        protected ?Image $icon = null
    ){
        parent::__construct($title, $elements, $onSubmit);
    }

    public function getIcon(): ?Image{
        return $this->icon;
    }

    protected function serializeFormData(): array{
        $data = parent::serializeFormData();
        if ($this->icon !== null) $data["icon"] = $this->icon;
        return $data;
    }

    public final function send(Player $player): void{
        Closure::bind(function (Player $player) {
            $id = $player->formIdCounter++;
            if ($player->getNetworkSession()->sendDataPacket(ServerSettingsResponsePacket::create(
                $id,
                json_encode($this, JSON_THROW_ON_ERROR)
            ))) $player->forms[$id] = $this;
        }, $this, Player::class)($player);
    }
}