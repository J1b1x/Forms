<?php
namespace Jibix\Forms\form\response\autoback;
use Jibix\Forms\form\Form;
use Jibix\Forms\form\type\CustomForm;
use Jibix\Forms\form\type\MenuForm;
use Jibix\Forms\Forms;
use Jibix\Forms\menu\type\BackButton;
use Jibix\Forms\menu\type\CloseButton;
use Jibix\Forms\util\Utils;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;


/**
 * Class AutoBackHandler
 * @package Jibix\Forms\form\response\autoback
 * @author Jibix
 * @date 06.04.2023 - 05:11
 * @project Forms
 */
final class AutoBackHandler{

    private const EXPIRE_TICKS = 8;

    /** @var AutoBackEntry[] */
    private static array $entries = [];

    public static function handleForm(Player $player, int $formId, string $formData): ?string{
        $tick = Server::getInstance()->getTick();
        foreach (self::$entries as $name => $storage) {
            if ($storage->getExpireTick() <= $tick) unset(self::$entries[$name]);
        }
        $name = $player->getName();
        if (isset(self::$entries[$name]) && !self::$entries[$name]->canGoBack($formData)) unset(self::$entries[$name]);
        if (($data = self::overwriteBackButtons($player, $formData)) !== null) {
            return $data;
        } elseif (isset(self::$entries[$name])) {
            Forms::getScheduler()?->scheduleDelayedTask(new ClosureTask(function () use ($player, $formId): void{
                if (!$player->isOnline()) return;
                $forms = Utils::getPropertyFromOutside($player, "forms");
                if (isset($forms[$formId]) && ($form = $forms[$formId]) instanceof Form) self::applyAutoBack($player, $form);
            }), 1);
        }

        return null;
    }

    public static function storeLastForm(Player $player, Form $form): void{
        if (!Forms::isRegistered() || !Forms::isAutoBack()) return;
        $name = $player->getName();
        $tick = Server::getInstance()->getTick() + self::EXPIRE_TICKS;
        if (isset(self::$entries[$name])) {
            $storage = self::$entries[$name];
            $storage->setExpireTick($tick);
            $storage->setPreviousForm($form);
        } else {
            self::$entries[$name] = new AutoBackEntry($tick, $form);
        }
    }

    private static function overwriteBackButtons(Player $player, string $data): ?string{
        if (!isset(self::$entries[$player->getName()])) {
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
        $previous = self::$entries[$name]?->getPreviousForm();
        if (($menu = !$form instanceof MenuForm) && !$form instanceof CustomForm) return;
        if ($menu) {
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
}