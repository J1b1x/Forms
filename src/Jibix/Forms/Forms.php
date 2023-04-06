<?php
namespace Jibix\Forms;
use Jibix\Forms\event\ServerSettingsFormEvent;
use Jibix\Forms\form\Form;
use Jibix\Forms\form\type\MenuForm;
use Jibix\Forms\menu\type\BackButton;
use Jibix\Forms\menu\type\CloseButton;
use Jibix\Forms\util\AutoBackEntry;
use Jibix\Forms\util\Utils;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;


/**
 * Class Forms
 * @package Jibix\Forms
 * @author Jibix
 * @date 05.04.2023 - 19:04
 * @project Forms
 */
final class Forms{

    private const FORM_TIME = 8;

    private static ?Plugin $plugin = null;
    private static bool $autoBack = true;

    private static int $waitId;
    private static array $queue = [];
    /** @var AutoBackEntry[] */
    private static array $autoBackStorage = [];


    public static function isRegistered(): bool{
        return self::$plugin !== null;
    }

    /**
     * Function register
     * @param Plugin $plugin
     * @return void
     * @throws \ReflectionException
     * @author skymin (https://github.com/sky-min/ServerSettingForm/)
     */
    public static function register(Plugin $plugin): void{
        if (self::isRegistered()) return;
        self::$plugin = $plugin;
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
                        //God, how much i hate PM/Dylan sometimes... This shit could be done within like 3 lines of code if there was an event with the actual form object

                        $player = $target->getPlayer();
                        self::checkAutoBack($player, $packet->formData);
                        if (($data = self::overwriteBackButtons($player, $packet->formData)) !== null) {
                            $packet->formData = $data;
                        } elseif (isset(self::$autoBackStorage[$player->getName()])) {
                            $id = $packet->formId;
                            self::$plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $id): void{
                                if (!$player->isOnline()) return;
                                $forms = Utils::getPropertyFromOutside($player, "forms");
                                if (isset($forms[$id]) && ($form = $forms[$id]) instanceof Form) self::applyAutoBack($player, $form);
                            }), 1);
                        }
                    }
                }
            }
        }, EventPriority::MONITOR, $plugin);

    }

    public static function setAutoBack(bool $value): void{
        self::$autoBack = $value;
    }


    private static function checkAutoBack(Player $player, string $formData): void{
        $tick = Server::getInstance()->getTick();
        foreach (self::$autoBackStorage as $name => $storage) {
            if ($storage->getExpireTick() <= $tick) unset(self::$autoBackStorage[$name]);
        }

        $name = $player->getName();
        if (isset(self::$autoBackStorage[$name]) && !self::$autoBackStorage[$name]->canGoBack($formData)) unset(self::$autoBackStorage[$name]);
    }

    private static function overwriteBackButtons(Player $player, string $data): ?string{
        if (!isset(self::$autoBackStorage[$player->getName()])) {
            $data = json_decode($data, true);
            if ($data['type'] === "form") {
                //Overwriting back buttons with close buttons
                foreach ($data["buttons"] as $key => $button) {
                    if (isset($button[BackButton::BACK_ID])) $data['buttons'][$key] = new CloseButton();
                }
                return json_encode($data, JSON_THROW_ON_ERROR);
            }
        }
        return null;
    }

    private static function applyAutoBack(Player $player, Form $form): void{
        $name = $player->getName();
        $previous = self::$autoBackStorage[$name]?->getPreviousForm();
        if ($form instanceof MenuForm) {
            //Setting onSubmit of back buttons
            foreach ($form->getButtons() as $button) {
                if (
                    $button instanceof BackButton &&
                    $button->getOnSubmit() === null
                ) $button->setOnSubmit(fn (Player $player) => $player->sendForm($previous));
            }
        }
        if ($form->getOnClose() === null) $form->setOnClose(fn (Player $player) => $player->sendForm($previous));
    }

    public static function storeLastForm(Player $player, Form $form): void{
        if (!self::isRegistered() || !self::$autoBack) return;
        $name = $player->getName();
        $tick = Server::getInstance()->getTick() + self::FORM_TIME;
        if (isset(self::$autoBackStorage[$name])) {
            $storage = self::$autoBackStorage[$name];
            $storage->setExpireTick($tick);
            $storage->setPreviousForm($form);
        } else {
            self::$autoBackStorage[$name] = new AutoBackEntry($tick, $form);
        }
    }
}