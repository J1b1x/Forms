<?php
namespace Jibix\Forms\menu\type;
use Closure;
use Jibix\Forms\menu\Button;
use Jibix\Forms\menu\Image;


/**
 * Class CloseButton
 * @package Jibix\Forms\menu\type
 * @author Jibix
 * @date 06.04.2023 - 02:13
 * @project Forms
 */
class CloseButton extends Button{

    public function __construct(string $text = "§cClose", ?Closure $onSubmit = null, ?Image $image = null){
        parent::__construct($text, $onSubmit, $image ?? Image::path("textures/ui/cancel"));
    }
}