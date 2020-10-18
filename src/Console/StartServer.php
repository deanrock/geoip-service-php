<?php
declare(strict_types = 1);

namespace GeoIPServer\Console;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use GeoIPServer\Reader\ReloadingReader;
use GeoIPServer\Server\Middlewares\ErrorHandler;
use GeoIPServer\Server\Router;
use GeoIPServer\Server\Routes\DescribeGeoIp;
use GeoIPServer\Utils\Logger;
use Illuminate\Console\Command;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Socket\Server as Server;

class StartServer extends Command
{
    protected $signature = 'server:serve {--host=0.0.0.0} {--port=8080} {--license-key=}';

    protected LoopInterface $loop;

    public function __construct()
    {
        parent::__construct();

        $this->loop = LoopFactory::create();
        $this->logger = new Logger($this);
    }

    private function createServer()
    {
        $reloadingReader = new ReloadingReader($this->loop, $this->option('license-key'));

        $routes = new RouteCollector(new Std(), new GroupCountBased());
        $routes->addRoute('GET', '/describe/{ip:[0-9\.]+}', new DescribeGeoIp($reloadingReader));

        $middlewares = [
            new ErrorHandler($this->logger),
            new Router($routes),
        ];

        $server = new HttpServer($this->loop, ...$middlewares);
        $server->on('error', function ($message) {
            $this->error($message);
        });

        $socket = new Server($this->option('host') . ':' . $this->option('port'), $this->loop);
        $server->listen($socket);
    }

    public function handle()
    {
        $this->info("Starting server on {$this->option('port')}...");

        $this->createServer();

        $this->loop->run();
    }
}
