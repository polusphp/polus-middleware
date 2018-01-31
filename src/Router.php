<?php
declare(strict_types = 1);

namespace Polus\Middleware;

use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;

class Router implements MiddlewareInterface
{

    /**
     * @var RouterContainer The router container
     */
    private $router;

    /**
     * Set the RouterContainer instance.
     *
     * @param RouterContainer $router
     */
    public function __construct(RouterContainer $router)
    {
        $this->router = $router;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $matcher = $this->router->getMatcher();
        $route = $matcher->match($request);
        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();
            $request = $request->withAttribute('polus:route', $failedRoute);
            $response = new Response();
            switch ($failedRoute->failedRule) {
                case 'Aura\Router\Rule\Allows':
                    $response = $response->withStatus(405)->withHeader('Allow', implode(', ', $failedRoute->allows)); // 405 METHOD NOT ALLOWED
                case 'Aura\Router\Rule\Accepts':
                    $response = $response->withStatus(406); // 406 NOT ACCEPTABLE
                default:
                    $response = $response->withStatus(404); // 404 NOT FOUND
            }
            return $response;
        } else {
            $request = $request->withAttribute('polus:route', $route);
            foreach ($route->attributes as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }
        }
        return $handler->handle($request);
    }
}
