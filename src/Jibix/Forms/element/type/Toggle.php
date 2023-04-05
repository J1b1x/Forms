<?php
namespace Jibix\Forms\element\type;
use Closure;
use Jibix\Forms\element\Element;
use pocketmine\form\FormValidationException;


/**
 * Class Toggle
 * @package Jibix\Forms\element\type
 * @author Jibix
 * @date 05.04.2023 - 17:10
 * @project Forms
 */
class Toggle extends Element{

    public function __construct(
        string $text,
        protected bool $default = false,
        ?Closure $onSubmit = null,
    ){
        parent::__construct($text, $onSubmit);
    }

    public function getDefault(): bool{
        return $this->default;
    }

    public function hasChanged(): bool{
        return $this->default !== $this->getValue();
    }


    protected function getType(): string{
        return "toggle";
    }

    protected function validateValue(mixed $value) : void{
        if (!is_bool($value)) throw new FormValidationException("Expected bool, got " . gettype($value));
    }

    protected function serializeElementData(): array{
        return ["default" => $this->default];
    }
}