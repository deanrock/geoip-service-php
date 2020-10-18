<?php
declare(strict_types = 1);

namespace GeoIPServer\Utils;

use Illuminate\Console\Command;

class Logger
{
    private Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function error($string)
    {
        $this->command->error($string);
    }

    public function warn($string)
    {
        $this->command->warn($string);
    }

    public function info($string)
    {
        $this->command->info($string);
    }
}
