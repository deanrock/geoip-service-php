<?php
declare(strict_types = 1);

namespace GeoIPServer\Reader;

use GeoIp2\Database\Reader;
use GeoIp2\Model\City;
use GeoIPServer\Utils\Http;
use GeoIPServer\Utils\Utils;
use Illuminate\Filesystem\Filesystem;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Http\Browser;

class ReloadingReader
{
    private LoopInterface $loop;
    private string $dbUrl;
    private ?Reader $geoipReader = null;

    private static function getDbUrl(string $license_key)
    {
        return "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key={$license_key}&suffix=tar.gz";
    }

    public function __construct(LoopInterface $loop, string $license_key)
    {
        $this->loop = $loop;
        $this->dbUrl = $this::getDbUrl($license_key);

        $this->downloadDatabase();
        $this->setTimer();
    }

    public function setTimer()
    {
        $this->loop->addPeriodicTimer(2, function () {
            $this->downloadDatabase();
        });
    }

    public function downloadDatabase()
    {
        $dir = Utils::createTemporaryDir();

        $client = new Browser($this->loop);

        $process = new Process('tar xfz - --wildcards --no-anchored --strip 1  *.mmdb', $dir, null, array(
            array('pipe', 'r'), // stdin
            array('pipe', 'w'), // stdout
            array('pipe', 'w'), // stderr
        ));
        $process->start($this->loop);

        $process->on('exit', function ($exitcode) use ($dir) {
            if ($exitcode === 0) {
                $this->geoipReader = new Reader($dir . '/GeoLite2-City.mmdb');
            } else {
                echo "ERROR\n";
            }

            // Remove directory after file is opened (or if it fails).
            $fs = new Filesystem();
            $fs->deleteDirectory($dir);
        });

        $stream = Http::download($client, $this->dbUrl);
        $stream->pipe($process->stdin);
    }

    public function city(string $ip): City
    {
        if (!$this->geoipReader) {
            throw new \Exception('Database is not available!');
        }

        return $this->geoipReader->city($ip);
    }
}
