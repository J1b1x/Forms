<?php
namespace Jibix\Forms\form\type;
use Closure;
use Jibix\Forms\form\Form;
use Jibix\Forms\form\response\autoback\AutoBackHandler;
use Jibix\Forms\menu\Button;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\Utils;


/**
 * Class MenuForm
 * @package Jibix\Forms\form
 * @author Jibix
 * @date 05.04.2023 - 17:16
 * @project Forms
 */
class MenuForm extends Form{

    public function __construct(
        string $title,
        protected string $content = "",
        protected array $buttons = [],
        protected ?Closure $onSubmit = null,
        protected ?Closure $onClose = null,
    ){
        if ($onSubmit !== null) Utils::validateCallableSignature(function (Player $player, Button $selected){}, $onSubmit);
        if ($onClose !== null) Utils::validateCallableSignature(function (Player $player){}, $onClose);
        parent::__construct($title);
    }

    public function getContent(): string{
        return $this->content;
    }

    public function getButtons(): array{
        return $this->buttons;
    }

    public function getOnClose(): ?Closure{
        return $this->onClose;
    }

    public function setOnClose(?Closure $onClose): void{
        $this->onClose = $onClose;
    }


    protected function getType(): string{
        return "form";
    }

    protected function serializeFormData(): array{
        return [
            "buttons" => $this->buttons,
            "content" => $this->content,
        ];
    }

    private function getButton(int $index): Button{
        return $this->buttons[$index] ?? throw new FormValidationException("Button with index $index does not exist");
    }

    final public function handleResponse(Player $player, mixed $data): void{
        if ($data === null) {
            $this->onClose?->__invoke($player);
        } elseif (is_int($data)) {
            AutoBackHandler::storeLastForm($player, $this);
            $button = $this->getButton($data)->setValue($data);
            $button->getOnSubmit()?->__invoke($player, $button);
            $this->onSubmit?->__invoke($player, $button);
        } else {
            throw new FormValidationException("Expected int or null, got " . gettype($data));
        }
    }
}