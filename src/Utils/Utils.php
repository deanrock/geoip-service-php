<?php
declare(strict_types = 1);

namespace GeoIPServer\Utils;

class Utils
{
    private static function randomTemporaryDir(): string
    {
        return sys_get_temp_dir() . '/' . substr(md5(random_bytes(16)), 0, 16);
    }

    public static function createTemporaryDir(): string
    {
        $name = Utils::randomTemporaryDir();
        while (@mkdir($name) === false) {
            $name = Utils::randomTemporaryDir();
        }

        return $name;
    }
}
