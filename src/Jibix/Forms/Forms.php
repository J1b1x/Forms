<?php
namespace Jibix\Forms;
use Jibix\Forms\form\response\imagefix\ImageFixHandler;
use Jibix\Forms\form\response\serversettings\ServerSettingsHandler;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;


/**
 * Class Forms
 * @package Jibix\Forms
 * @author Jibix
 * @date 05.04.2023 - 19:04
 * @project Forms
 */
final class Forms{

    private static ?Plugin $plugin = null;
    private static bool $autoBack = true;

    public static function register(Plugin $plugin): void{
        if (self::isRegistered()) return;
        self::$plugin = $plugin;
        ServerSettingsHandler::initialize();
        ImageFixHandler::initialize();
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $plugin);
    }

    public static function isRegistered(): bool{
        return self::$plugin !== null;
    }

    public static function isAutoBack(): bool{
        return self::$autoBack;
    }

    public static function setAutoBack(bool $value): void{
        self::$autoBack = $value;
    }

    public static function getScheduler(): ?TaskScheduler{
        return self::$plugin?->getScheduler();
    }
}