<?php
namespace Jibix\Forms\form;


/**
 * Class Form
 * @package Jibix\Forms\form
 * @author Jibix
 * @date 05.04.2023 - 17:15
 * @project Forms
 */
abstract class Form implements \pocketmine\form\Form{

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