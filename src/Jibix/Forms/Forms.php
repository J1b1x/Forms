<?php
namespace Jibix\Forms;
use Jibix\Forms\event\ServerSettingsFormEvent;
use Jibix\Forms\form\type\ServerSettingsForm;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\plugin\Plugin;
use pocketmine\Server;


/**
 * Class Forms
 * @package Jibix\Forms
 * @author Jibix
 * @date 05.04.2023 - 19:04
 * @project Forms
 */
final class Forms{

    private static bool $registered = false;

    private static int $waitId;
    private static array $queue = [];

    /**
     * Function register
     * @param Plugin $plugin
     * @return void
     * @throws \ReflectionException
     * @author skymin (https://github.com/sky-min/ServerSettingForm/)
     */
    public static function register(Plugin $plugin): void{
        if (self::$registered) return;
        self::$registered = true;
        self::$waitId = mt_rand() * 1000;
        Server::getInstance()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, static function (DataPacketReceiveEvent $event): void{
            $packet = $event->getPacket();
            if ($packet instanceof ServerSettingsRequestPacket) {
                $session = $event->getOrigin();
                self::$queue[spl_object_id($session)] = 10;
                $session->sendDataPacket(NetworkStackLatencyPacket::request(self::$waitId));
            } elseif ($packet instanceof NetworkStackLatencyPacket && $packet->timestamp === self::$waitId) {
                $session = $event->getOrigin();
                $id = spl_object_id($session);
                if (self::$queue[$id]-- == 0) {
                    (new ServerSettingsFormEvent())->call();
                    unset(self::$queue[$id]);
                } else {
                    $session->sendDataPacket(NetworkStackLatencyPacket::request(self::$waitId));
                }
            }
        }, EventPriority::MONITOR, $plugin);
    }
}