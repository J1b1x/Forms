<?php
namespace Jibix\Forms\form\response\autoback;
use Jibix\Forms\form\Form;


/**
 * Class AutoBackEntry
 * @package Jibix\Forms\form\response\autoback
 * @author Jibix
 * @date 06.04.2023 - 03:47
 * @project Forms
 */
class AutoBackEntry{

    private string $mainData;

    public function __construct(
        private int $expireTick,
        private Form $previousForm,
    ){
        $this->mainData = json_encode($this->previousForm, JSON_THROW_ON_ERROR);
    }

    public function getExpireTick(): int{
        return $this->expireTick;
    }

    public function setExpireTick(int $expireTick): void{
        $this->expireTick = $expireTick;
    }

    public function getPreviousForm(): Form{
        return $this->previousForm;
    }

    public function setPreviousForm(Form $previousForm): void{
        $this->previousForm = $previousForm;
    }


    public function canGoBack(string $formData): bool{
        return $this->mainData !== $formData;
    }
}