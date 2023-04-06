<?php
namespace Jibix\Forms\form\response\imagefix;
use Jibix\Forms\form\response\serversettings\ServerSettingsHandler;
use Jibix\Forms\Forms;
use pocketmine\entity\Attribute;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;


/**
 * Class ImageFixHandler
 * @package Jibix\Forms\form\response\imagefix
 * @author Jibix
 * @date 06.04.2023 - 05:20
 * @project Forms
 * @author Jibix & Muqsit (https://github.com/Muqsit/FormImagesFix)
 */
final class ImageFixHandler{

    private const REPEAT_TIMES = 5;

    private static int $waitId;
    private static array $callbacks = [];

    public static function initialize(): void{
        self::$waitId = ServerSettingsHandler::getWaitId();
    }

    public static function getWaitId(): int{
        return self::$waitId;
    }

    public static function handleRequest(NetworkSession $session): void{
        Forms::getScheduler()?->scheduleDelayedTask(new ClosureTask(function () use ($session): void{
            if (($player = $session->getPlayer()) === null || !$player->isOnline()) return;
            $session->sendDataPacket(NetworkStackLatencyPacket::create($timestamp = ++self::$waitId, true));
            self::$callbacks[$player->getId()][$timestamp] = function () use ($player, $session): void{
                if (!$player->isOnline()) return;
                $times = self::REPEAT_TIMES;
                Forms::getScheduler()?->scheduleRepeatingTask(new ClosureTask(static function () use ($player, $session, &$times): void{
                    if (--$times < 0 || !$session->isConnected()) throw new CancelTaskException("");
                    $attribute = $player->getAttributeMap()->get(Attribute::EXPERIENCE_LEVEL);
                    $session->sendDataPacket(UpdateAttributesPacket::create($player->getId(), [new \pocketmine\network\mcpe\protocol\types\entity\Attribute(
                        $attribute->getId(),
                        $attribute->getMinValue(),
                        $attribute->getMaxValue(),
                        $attribute->getValue(),
                        $attribute->getDefaultValue(),
                        []
                    )], 0));
                }), 10);
            };
        }), 1);
    }

    public static function handleResponse(NetworkSession $session, int $timestamp): void{
        $id = $session->getPlayer()->getId();
        if (!isset(self::$callbacks[$id][$timestamp])) return;
        $callback = self::$callbacks[$id][$timestamp];
        unset(self::$callbacks[$id][$timestamp]);
        if (count(self::$callbacks[$id]) == 0) unset(self::$callbacks[$id]);
        $callback();
    }
}