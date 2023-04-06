<?php
namespace Jibix\Forms;
use Jibix\Forms\event\ServerSettingsFormEvent;
use Jibix\Forms\form\Form;
use Jibix\Forms\form\type\MenuForm;
use Jibix\Forms\menu\type\BackButton;
use Jibix\Forms\menu\type\CloseButton;
use Jibix\Forms\util\Utils;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\player\Player;
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

    private const FORM_TIME = 0.3;

    private static bool $registered = false;
    private static bool $autoBack = false;

    private static int $waitId;
    private static array $queue = [];
    private static array $lastClose = [];
    private static array $formStorage = [];

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
        Server::getInstance()->getPluginManager()->registerEvent(DataPacketSendEvent::class, static function (DataPacketSendEvent $event): void{
            if (!self::$autoBack) return;
            foreach ($event->getTargets() as $target) {
                foreach ($event->getPackets() as $packet) {
                    if ($packet instanceof ModalFormRequestPacket) {
                        $player = $target->getPlayer();
                        $id = $packet->formId;
                        $forms = Utils::getPropertyFromOutside($player, "forms");
                        if (isset($forms[$id]) && ($form = $forms[$id]) instanceof Form) self::checkBack($player, $form);
                    }
                }
            }
        }, EventPriority::MONITOR, $plugin);
    }

    public static function setAutoBack(bool $value): void{
        self::$autoBack = $value;
    }


    private static function checkBack(Player $player, Form $form): void{
        $name = $player->getName();
        foreach (self::$lastClose as $name => $time) {
            //Checking expired times
            if ($time <= time()) {
                unset(self::$lastClose[$name]);
                unset(self::$formStorage[$name]);
            }
        }

        if (isset(self::$formStorage[$name])) {
            $forms = self::$formStorage[$name];
            $previous = $forms[$key = array_key_last($forms)];
            unset(self::$formStorage[$name][$key]);
            if ($form instanceof MenuForm) {
                //Setting onSubmit of back buttons
                foreach ($form->getButtons() as $button) {
                    if (
                        $button instanceof BackButton &&
                        $button->getOnSubmit() === null
                    ) $button->setOnSubmit(fn (Player $player) => $player->sendForm($previous));
                }
            }
            //Setting onClose
            if ($form->getOnClose() === null) $form->setOnClose(fn (Player $player) => $player->sendForm($previous));
        } elseif ($form instanceof MenuForm) {
            //Overwriting back buttons with close buttons
            foreach ($form->getButtons() as $key => $button) {
                if ($button instanceof BackButton) $form->overwrite($key, new CloseButton());
            }
        }
    }

    public static function storeLastForm(Player $player, Form $form): void{
        if (!self::$registered || !self::$autoBack) return;
        $name = $player->getName();
        self::$formStorage[$name][] = $form;
        self::$lastClose[$name] = time() + self::FORM_TIME;
    }
}