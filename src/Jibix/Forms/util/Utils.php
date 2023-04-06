<?php
namespace Jibix\Forms\util;
use ReflectionClass;


/**
 * Class Utils
 * @package Jibix\Forms\util
 * @author Jibix
 * @date 06.04.2023 - 02:01
 * @project Forms
 */
final class Utils{

    public static function getPropertyFromOutside(object $class, string $property): mixed{
        $property = (new ReflectionClass($class))->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($class);
    }
}