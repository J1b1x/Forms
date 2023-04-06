<?php
namespace Jibix\Forms\form\response\serversettings;
use Jibix\Forms\event\ServerSettingsFormEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;


/**
 * Class ServerSettingsHandler
 * @package Jibix\Forms\form\response\serversettings
 * @author Jibix
 * @date 06.04.2023 - 05:25
 * @project Forms
 * @author Jibix & skymin (https://github.com/sky-min/ServerSettingForm/)
 */
final class ServerSettingsHandler{

    private static int $waitId;
    private static array $queue = [];

    public static function initialize(): void{
        self::$waitId = mt_rand() * 1000;
    }

    public static function getWaitId(): int{
        return self::$waitId;
    }

    public static function handleRequest(NetworkSession $session): void{
        self::$queue[spl_object_id($session)] = 10;
        $session->sendDataPacket(NetworkStackLatencyPacket::request(self::$waitId));
    }

    public static function handleResponse(NetworkSession $session): void{
        $id = spl_object_id($session);
        if (self::$queue[$id]-- == 0) {
            (new ServerSettingsFormEvent())->call();
            unset(self::$queue[$id]);
        } else {
            $session->sendDataPacket(NetworkStackLatencyPacket::request(self::$waitId));
        }
    }
}