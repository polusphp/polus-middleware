<?php

namespace Polus\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAgent implements MiddlewareInterface
{
    /**
     * Enable checking of proxy headers (X-Forwarded-For to determined client IP.
     *
     * Defaults to false as only $_SERVER['REMOTE_ADDR'] is a trustworthy source
     * of IP address.
     *
     * @var bool
     */
    protected $checkProxyHeaders;

    /**
     * List of trusted proxy IP addresses
     *
     * If not empty, then one of these IP addresses must be in $_SERVER['REMOTE_ADDR']
     * in order for the proxy headers to be looked at.
     *
     * @var array
     */
    protected $trustedProxies;

    /**
     * Name of the attribute added to the ServerRequest object
     *
     * @var string
     */
    protected $attributeName = 'user_agent';

    /**
     * List of proxy headers inspected for the client IP address
     *
     * @var array
     */
    protected $headersToInspect = [
        'X-User-Agent',
    ];

    /**
     * Constructor
     *
     * @param bool $checkProxyHeaders Whether to use proxy headers to determine client IP
     * @param array $trustedProxies   List of IP addresses of trusted proxies
     * @param string $attributeName   Name of attribute added to ServerRequest object
     * @param array $headersToInspect List of headers to inspect
     */
    public function __construct(
        $checkProxyHeaders = false,
        array $trustedProxies = [],
        $attributeName = null,
        array $headersToInspect = []
    ) {
        $this->checkProxyHeaders = $checkProxyHeaders;
        $this->trustedProxies = $trustedProxies;

        if ($attributeName) {
            $this->attributeName = $attributeName;
        }
        if (!empty($headersToInspect)) {
            $this->headersToInspect = $headersToInspect;
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userAgent = $this->determineClientUserAgent($request);
        return $handler->handle($request->withAttribute($this->attributeName, $userAgent));
    }
    
    /**
     * Find out the client's UserAgent from the headers available to us
     *
     * @param  ServerRequestInterface $request PSR-7 Request
     * @return string
     */
    protected function determineClientUserAgent($request)
    {
        $userAgent = null;
        $serverParams = $request->getServerParams();
        $userAgent = $serverParams['HTTP_USER_AGENT'];

        $checkProxyHeaders = $this->checkProxyHeaders;
        if ($checkProxyHeaders && !empty($this->trustedProxies)) {
            if (!in_array($userAgent, $this->trustedProxies)) {
                $checkProxyHeaders = false;
            }
        }

        if ($checkProxyHeaders) {
            foreach ($this->headersToInspect as $header) {
                if ($request->hasHeader($header)) {
                    $userAgent = trim(current(explode(',', $request->getHeaderLine($header))));
                    break;
                }
            }
        }
        return $userAgent;
    }
}
