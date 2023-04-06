<?php
namespace Jibix\Forms\element;
use Closure;
use pocketmine\form\FormValidationException;


/**
 * Class SelectElement
 * @package Jibix\Forms\element
 * @author Jibix
 * @date 05.04.2023 - 16:48
 * @project Forms
 */
abstract class SelectElement extends Element{

    public function __construct(
        string $text,
        protected array $options = [],
        protected int $default = 0,
        ?Closure $onSubmit = null
    ){
        parent::__construct($text, $onSubmit);
    }

    public function getSelectedOption(): string{
        return $this->options[$this->getValue()];
    }

    protected function validateValue(mixed $value): void{
        if (!is_int($value)) throw new FormValidationException("Expected int, got " . gettype($value));
        if (!isset($this->options[$value])) throw new FormValidationException("Option {$value} does not exist");
    }
}