<?php
namespace Jibix\Forms\menu;


/**
 * Class Image
 * @package Jibix\Forms\menu
 * @author Jibix
 * @date 05.04.2023 - 16:36
 * @project Forms
 */
class Image implements \JsonSerializable{


    private function __construct(
        protected string $data,
        protected string $type
    ){}

    public function getData(): string{
        return $this->data;
    }

    public function getType(): string{
        return $this->type;
    }


    public static function url(string $data): self{
        return new self($data, "url");
    }

    public static function path(string $data): self{
        return new self($data, "path");
    }

    public static function detect(string $data): self{
        return str_starts_with("http", mb_strtolower($data)) ? self::url($data) : self::path($data);
    }

    public function jsonSerialize(): array{
        return [
            "type" => $this->type,
            "data" => $this->data,
        ];
    }
}