<?php
namespace Jibix\Forms\element\type;
use Jibix\Forms\element\SelectElement;


/**
 * Class StepSlider
 * @package Jibix\Forms\element\type
 * @author Jibix
 * @date 05.04.2023 - 17:09
 * @project Forms
 */
class StepSlider extends SelectElement{

    protected function getType(): string{
        return "step_slider";
    }

    protected function serializeElementData(): array{
        return [
            "steps" => $this->options,
            "default" => $this->default,
        ];
    }
}