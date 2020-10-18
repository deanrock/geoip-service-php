<?php
declare(strict_types = 1);

namespace GeoIPServer\Server\Middlewares;

use GeoIPServer\Utils\Http;
use GeoIPServer\Utils\Logger;
use Psr\Http\Message\ServerRequestInterface;

class ErrorHandler
{
    private Logger $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        try {
            $response = $next($request);
        } catch (\Exception $e) {
            $this->logger->error($e);

            return Http::jsonResponse(500, ['message' => 'Internal Server Error']);
        }

        return $response;
    }
}
