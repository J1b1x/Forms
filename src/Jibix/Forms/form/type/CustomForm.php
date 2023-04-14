<?php
namespace Jibix\Forms\form\type;
use Closure;
use Jibix\Forms\element\Element;
use Jibix\Forms\element\type\Label;
use Jibix\Forms\form\Form;
use Jibix\Forms\form\response\autoback\AutoBackHandler;
use Jibix\Forms\form\response\CustomFormResponse;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\Utils;


/**
 * Class CustomForm
 * @package Jibix\Forms\form
 * @author Jibix
 * @date 05.04.2023 - 17:24
 * @project Forms
 */
class CustomForm extends Form{

    public static function fromData(array $elements, array $data): array{
        return array_map(
            fn (Element $element): Element => $element instanceof Label ? $element : $element->setDefault($data[$element->getText()] ?? $element->getDefault()),
            $elements
        );
    }

    /**
     * CustomForm constructor.
     * @param string $title
     * @param Element[] $elements
     * @param Closure|null $onSubmit
     * @param Closure|null $onClose
     */
    public function __construct(
        string $title,
        protected array $elements,
        protected ?Closure $onSubmit = null,
        protected ?Closure $onClose = null,
    ){
        if ($onSubmit !== null) Utils::validateCallableSignature(function (Player $player, CustomFormResponse $response){}, $onSubmit);
        if ($onClose !== null) Utils::validateCallableSignature(function (Player $player){}, $onClose);
        parent::__construct($title);
    }

    public function getElements(): array{
        return $this->elements;
    }

    public function getOnClose(): ?Closure{
        return $this->onClose;
    }

    public function setOnClose(?Closure $onClose): void{
        $this->onClose = $onClose;
    }


    protected function getType(): string{
        return "custom_form";
    }

    protected function serializeFormData(): array{
        return ["content" => $this->elements];
    }

    private function validateElements(Player $player, array $data): void{
        if (($actual = count($data)) !== ($expected = count($this->elements))) throw new FormValidationException("Expected $expected result data, got $actual");
        foreach ($data as $index => $value) {
            $element = $this->elements[$index] ?? throw new FormValidationException("Element at offset $index does not exist");
            try  {
                $element->setValue($value);
            } catch (FormValidationException $e) {
                throw new FormValidationException("Validation failed for element " . $element::class . ": " . $e->getMessage(), 0, $e);
            }
        }
        AutoBackHandler::storeLastForm($player, $this);
        foreach ($this->elements as $element) {
            if ($element instanceof Label) continue;
            $element->getOnSubmit()?->__invoke($player, $element);
        }

        $this->onSubmit?->__invoke($player, new CustomFormResponse($this->elements));
    }

    final public function handleResponse(Player $player, mixed $data): void{
        match (true) {
            $data === null => $this->onClose?->__invoke($player),
            is_array($data) => $this->validateElements($player, $data),
            default => throw new FormValidationException("Expected array or null, got " . gettype($data)),
        };
    }
}