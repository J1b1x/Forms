<?php
namespace Jibix\Forms\element\type;
use Jibix\Forms\element\SelectElement;


/**
 * Class Dropdown
 * @package Jibix\Forms\element\type
 * @author Jibix
 * @date 05.04.2023 - 16:57
 * @project Forms
 */
class Dropdown extends SelectElement{

    protected function getType(): string{
        return "dropdown";
    }

    protected function serializeElementData(): array{
        return [
            "options" => $this->options,
            "default" => $this->default,
        ];
    }
}