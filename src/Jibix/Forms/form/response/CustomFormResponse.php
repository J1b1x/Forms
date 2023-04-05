<?php
namespace Jibix\Forms\form\response;
use Jibix\Forms\element\Element;
use Jibix\Forms\element\type\Dropdown;
use Jibix\Forms\element\type\Input;
use Jibix\Forms\element\type\Label;
use Jibix\Forms\element\type\Slider;
use Jibix\Forms\element\type\StepSlider;
use Jibix\Forms\element\type\Toggle;


/**
 * Class CustomFormResponse
 * @package Jibix\Forms\form\response
 * @author Jibix
 * @date 05.04.2023 - 17:29
 * @project Forms
 */
class CustomFormResponse{

    public function __construct(protected array $elements){}

    public function getElements(): array{
        return $this->elements;
    }

    public function get(string $expected): Element{
        $element = array_shift($this->elements);
        return match (true) {
            $element === null => throw new \UnexpectedValueException("There are no elements in the container"),
            $element instanceof Label => $this->get($expected), //skip labels
            !$element instanceof $expected => throw new \UnexpectedValueException("Unexpected type of element"),
            default => $element,
        };
    }

    public function getDropdown(): Dropdown{
        return $this->get(Dropdown::class);
    }

    public function getInput(): Input{
        return $this->get(Input::class);
    }

    public function getSlider(): Slider{
        return $this->get(Slider::class);
    }

    public function getStepSlider(): StepSlider{
        return $this->get(StepSlider::class);
    }

    public function getToggle(): Toggle{
        return $this->get(Toggle::class);
    }

    public function getValues(): array{
        $values = [];
        foreach ($this->elements as $element) {
            if ($element instanceof Label) continue;
            $values[] = $element instanceof Dropdown ? $element->getSelectedOption() : $element->getValue();
        }
        return $values;
    }
}