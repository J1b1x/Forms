<?php
namespace Jibix\Forms\menu;
use Closure;
use Exception;
use JsonSerializable;
use pocketmine\player\Player;
use pocketmine\utils\Utils;


/**
 * Class Button
 * @package Jibix\Forms\menu
 * @author Jibix
 * @date 05.04.2023 - 16:35
 * @project Forms
 */
class Button implements JsonSerializable{

    protected int $value;

    public function __construct(
        protected string $text,
        protected ?Closure $onSubmit = null,
        protected ?Image $image = null,
    ){
        if ($onSubmit !== null) Utils::validateCallableSignature(function (Player $player, Button $selected){}, $onSubmit);
    }

    public function getText(): string{
        return $this->text;
    }

    public function getOnSubmit(): ?Closure{
        return $this->onSubmit;
    }

    public function setOnSubmit(?Closure $onSubmit): void{
        $this->onSubmit = $onSubmit;
    }

    public function getImage(): ?Image{
        return $this->image;
    }

    public function getValue(): int{
        return $this->value ?? throw new Exception("Trying to access an uninitialized value");
    }

    public function setValue(int $value): self{
        $this->value = $value;
        return $this;
    }

    public function jsonSerialize(): array{
        $data = ["text" => $this->text];
        if ($this->image !== null) $data["image"] = $this->image;
        return $data;
    }
}