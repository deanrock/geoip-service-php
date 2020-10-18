<?php
declare(strict_types = 1);

namespace GeoIPServer\Server;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use GeoIPServer\Utils\Http;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    private GroupCountBased $dispatcher;

    public function __construct(RouteCollector  $routes)
    {
        $this->dispatcher = new GroupCountBased($routes->getData());
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            return Http::jsonResponse(404, ['message' => 'Not found']);
        case Dispatcher::METHOD_NOT_ALLOWED:
            return Http::jsonResponse(405, ['message' => 'Method now allowed']);
        case Dispatcher::FOUND:
            $params = $routeInfo[2];
            return $routeInfo[1]($request, ... array_values($params));
        }

        throw new \Exception('Routing error');
    }
}
