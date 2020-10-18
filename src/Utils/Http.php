<?php
declare(strict_types = 1);

namespace GeoIPServer\Utils;

use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use React\Http\Message\Response;
use React\Stream\ReadableStreamInterface;
use function React\Promise\Stream\unwrapReadable;

class Http
{
    public static function download(Browser $browser, string $url): ReadableStreamInterface
    {
        return unwrapReadable(
            $browser->requestStreaming('GET', $url)->then(function (ResponseInterface $response) {
                return $response->getBody();
            })
        );
    }

    public static function jsonResponse(int $status, $message): Response
    {
        $headers = [
            'Content-Type' => 'application/json'
        ];

        return new Response($status, $headers, json_encode($message));
    }
}
