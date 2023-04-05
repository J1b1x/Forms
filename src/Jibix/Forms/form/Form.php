<?php
namespace Jibix\Forms\form;
use Closure;
use pocketmine\player\Player;


/**
 * Class Form
 * @package Jibix\Forms\form
 * @author Jibix
 * @date 05.04.2023 - 17:15
 * @project Forms
 */
abstract class Form implements \pocketmine\form\Form{

    public static function uncloseable(): Closure{
        return function (Player $player): void{
            Closure::bind(function (Player $player): void{
                $forms = $player->forms;
                if (!$forms) return;
                $player->sendForm($forms[array_key_first($forms)]);
            }, $this, Player::class)($player);
        };
    }


    public function __construct(protected string $title){}

    public function getTitle(): string{
        return $this->title;
    }

    abstract protected function getType(): string;
    abstract protected function serializeFormData(): array;

    final public function jsonSerialize(): array{
        return array_merge([
            "type" => $this->getType(),
            "title" => $this->getTitle()
        ], $this->serializeFormData());
    }
}