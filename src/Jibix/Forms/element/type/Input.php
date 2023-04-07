<?php
namespace Jibix\Forms\element\type;
use Closure;
use Jibix\Forms\element\Element;
use pocketmine\form\FormValidationException;


/**
 * Class Input
 * @package Jibix\Forms\element\type
 * @author Jibix
 * @date 05.04.2023 - 16:57
 * @project Forms
 */
class Input extends Element{

    public function __construct(
        string $text,
        protected string $placeholder = "",
        protected string $default = "",
        ?Closure $onSubmit = null,
    ){
        parent::__construct($text, $onSubmit);
    }

    public function getPlaceholder(): string{
        return $this->placeholder;
    }

    public function getDefault(): string{
        return $this->default;
    }

    public function setDefault(string $default): void{
        $this->default = $default;
    }


    protected function getType(): string{
        return "input";
    }

    protected function validateValue(mixed $value): void{
        if (!is_string($value)) throw new FormValidationException("Expected string, got " . gettype($value));
    }

    protected function serializeElementData(): array{
        return [
            "placeholder" => $this->placeholder,
            "default" => $this->default,
        ];
    }
}