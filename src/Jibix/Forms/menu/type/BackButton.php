<?php
namespace Jibix\Forms\menu\type;
use Closure;
use Jibix\Forms\menu\Button;
use Jibix\Forms\menu\Image;


/**
 * Class BackButton
 * @package Jibix\Forms\menu\type
 * @author Jibix
 * @date 06.04.2023 - 02:13
 * @project Forms
 */
class BackButton extends Button{

    public const BACK_ID = "back";

    public function __construct(string $text = "§cBack", ?Closure $onSubmit = null, ?Image $image = null){
        parent::__construct($text, $onSubmit, $image ?? Image::path("textures/ui/refresh_light"));
    }

    public function jsonSerialize(): array{
        return array_merge([self::BACK_ID => true], parent::jsonSerialize());
    }
}