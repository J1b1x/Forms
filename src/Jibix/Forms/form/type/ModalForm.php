<?php
namespace Jibix\Forms\form\type;
use Closure;
use Jibix\Forms\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\Utils;


/**
 * Class ModalForm
 * @package Jibix\Forms\form\type
 * @author Jibix
 * @date 05.04.2023 - 17:32
 * @project Forms
 */
class ModalForm extends Form{

    public function __construct(
        string $title,
        protected string $content,
        protected Closure $onSubmit,
        protected string $button1 = "gui.yes",
        protected string $button2 = "gui.no",
    ){
        Utils::validateCallableSignature(function (Player $player, bool $choice){}, $onSubmit);
        parent::__construct($title);
    }

    public static function confirm(string $title, string $content, Closure $onConfirm): self{
        Utils::validateCallableSignature(function (Player $player){}, $onConfirm);
        return new self($title, $content, static function (Player $player, bool $response) use ($onConfirm): void{
            if ($response) $onConfirm($player);
        });
    }

    protected function getType(): string{
        return "modal";
    }

    protected function serializeFormData(): array{
        return [
            "content" => $this->content,
            "button1" => $this->button1,
            "button2" => $this->button2,
        ];
    }

    final public function handleResponse(Player $player, mixed $data) : void{
        if (!is_bool($data)) throw new FormValidationException("Expected bool, got " . gettype($data));
        ($this->onSubmit)($player, $data);
    }
}