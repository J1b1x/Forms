<?php
namespace Jibix\Forms\element\type;
use Closure;
use Jibix\Forms\element\Element;
use pocketmine\form\FormValidationException;


/**
 * Class Label
 * @package Jibix\Forms\element\type
 * @author Jibix
 * @date 05.04.2023 - 16:59
 * @project Forms
 */
class Label extends Element{

    protected function getType(): string{
        return "label";
    }

    protected function validateValue(mixed $value) : void{
        if (!is_null($value)) throw new FormValidationException("Expected null, got " . gettype($value));
    }

    protected function serializeElementData(): array{
        return [];
    }
}