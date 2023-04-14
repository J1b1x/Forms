<?php
namespace Jibix\Forms\element;
use Closure;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use Exception;
use JsonSerializable;
use pocketmine\player\Player;
use pocketmine\utils\Utils;


/**
 * Class Element
 * @package Jibix\Forms\element
 * @author Jibix
 * @date 05.04.2023 - 16:42
 * @project Forms
 */
abstract class Element implements JsonSerializable{

    protected mixed $value;

    public function __construct(protected string $text, protected ?Closure $onSubmit = null){
        if ($onSubmit !== null) Utils::validateCallableSignature(new CallbackType(
            new ReturnType(),
            new ParameterType("player", Player::class),
            new ParameterType("element", static::class, ParameterType::CONTRAVARIANT | ParameterType::OPTIONAL)
        ), $on_submit);
    }

    public function getText(): string{
        return $this->text;
    }

    public function setText(string $text): void{
        $this->text = $text;
    }

    public function getOnSubmit(): ?Closure{
        return $this->onSubmit;
    }

    public function setOnSubmit(?Closure $onSubmit): void{
        $this->onSubmit = $onSubmit;
    }

    public function getValue(): mixed{
        return $this->value ?? throw new Exception("Trying to access an uninitialized value");
    }

    public function setValue(mixed $value): void{
        $this->validateValue($value);
        $this->value = $value;
    }

    abstract protected function getType(): string;
    abstract protected function validateValue(mixed $value): void;
    abstract protected function serializeElementData(): array;

    final public function jsonSerialize(): array{
        return array_merge([
            "type" => $this->getType(),
            "text" => $this->text
        ], $this->serializeElementData());
    }
}