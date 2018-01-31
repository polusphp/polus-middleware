<?php

namespace Polus\Middleware;

use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Polus\Polus_Interface\DispatchInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;

class Dispatcher implements MiddlewareInterface
{
    /**
     * @var DispatchInterface Controller dispatcher
     */
    private $dispatcher;

    /**
     * @param DispatchInterface $dispatcher
     */
    public function __construct(DispatchInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('polus:route');
        if ($route) {
            return $this->dispatcher->dispatch($route, $request, new Response());
        }

        return (new Response())->withStatus(404);
    }
}
