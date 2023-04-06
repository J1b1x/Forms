<?php
namespace Jibix\Forms;
use pocketmine\plugin\PluginBase;


/**
 * Class Main
 * @package Jibix\Forms
 * @author Jibix
 * @date 06.04.2023 - 01:37
 * @project Forms
 */
final class Main extends PluginBase{

    protected function onEnable(): void{
        Forms::register($this);
    }
}