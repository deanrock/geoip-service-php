<?php
declare(strict_types = 1);

namespace GeoIPServer\Server\Routes;

use GeoIPServer\Reader\ReloadingReader;
use GeoIPServer\Utils\Http;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class DescribeGeoIp
{
    private ?ReloadingReader $reader;

    public function __construct(ReloadingReader $reader)
    {
        $this->reader = $reader;
    }

    public function __invoke(ServerRequestInterface $request, string $ip): Response
    {
        $record = $this->reader->city($ip);

        if ($record) {
            return Http::jsonResponse(200, $record);
        }

        return Http::jsonResponse(500, ['message' => 'Error while fetching IP record.']);
    }
}
