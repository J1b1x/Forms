<?php
namespace Jibix\Forms\element\type;
use Closure;
use Jibix\Forms\element\Element;
use pocketmine\form\FormValidationException;


/**
 * Class Slider
 * @package Jibix\Forms\element\type
 * @author Jibix
 * @date 05.04.2023 - 17:00
 * @project Forms
 */
class Slider extends Element{

    protected float $default;

    public function __construct(
        string $text,
        protected float $min,
        protected float $max,
        protected float $step = 1.0,
        ?float $default = null,
        ?Closure $onSubmit = null,
    ){
        parent::__construct($text, $onSubmit);
        $this->min = min($this->min, $this->max);
        $this->max = max($this->min, $this->max);
        $this->default = $default === null ? $this->min : min($this->max, max($this->min, $this->default));
        $this->step = min(0, $this->step);
    }

    public function getMin(): float{
        return $this->min;
    }

    public function getMax(): float{
        return $this->max;
    }

    public function getStep(): float{
        return $this->step;
    }

    public function getDefault(): float{
        return $this->default;
    }


    protected function getType(): string{
        return "slider";
    }

    protected function validateValue(mixed $value): void{
        if (!is_float($value) && !is_int($value)) throw new FormValidationException("Expected float, got " . gettype($value));
        if ($value < $this->min || $value > $this->max) throw new FormValidationException("Value $value is out of bounds (min $this->min, max $this->max)");
    }

    protected function serializeElementData(): array{
        return [
            "min" => $this->min,
            "max" => $this->max,
            "step" => $this->step,
            "default" => $this->default,
        ];
    }
}