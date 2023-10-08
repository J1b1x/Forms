<?php
namespace Jibix\Forms;
use Jibix\Forms\form\response\autoback\AutoBackHandler;
use Jibix\Forms\form\response\imagefix\ImageFixHandler;
use Jibix\Forms\form\response\serversettings\ServerSettingsHandler;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;


/**
 * Class EventListener
 * @package Jibix\Forms
 * @author Jibix
 * @date 06.04.2023 - 05:08
 * @project Forms
 */
class EventListener implements Listener{

    public function onPacketReceive(DataPacketReceiveEvent $event): void{
        $packet = $event->getPacket();
        if ($packet instanceof ServerSettingsRequestPacket) {
            ServerSettingsHandler::handleRequest($event->getOrigin());
        } elseif ($packet instanceof NetworkStackLatencyPacket && $event->getOrigin()->getPlayer() !== null) {
            if (str_starts_with($packet->timestamp, ServerSettingsHandler::getWaitId())) {
                ServerSettingsHandler::handleResponse($event->getOrigin());
            } else {
                ImageFixHandler::handleResponse($event->getOrigin(), $packet->timestamp);
            }
        }
    }

    public function onPacketSend(DataPacketSendEvent $event): void{
        foreach ($event->getPackets() as $packet) {
            if ($packet instanceof ModalFormRequestPacket) {
                foreach ($event->getTargets() as $target) {
                    if (($player = $target->getPlayer()) === null) continue;
                    if (Forms::isAutoBack() && ($data = AutoBackHandler::handleForm($player, $packet->formId, $packet->formData)) !== null) $packet->formData = $data;
                    ImageFixHandler::handleRequest($target);
                }
            }
        }
    }
}