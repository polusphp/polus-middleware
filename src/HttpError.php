<?php

namespace Polus\Middleware;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpError implements MiddlewareInterface
{
    protected $defaultHtml = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width"><title>{title}</title><style>body {text-align: center;font-family: helvetica, arial, sans-serif;color: #999;}p {line-height: 1.6;}.image {padding: 2rem 2rem 0;}a:link, a:visited {color: #3a8bbb;}h1 {margin-bottom: 0;}h3 {margin-top: .5rem;}svg {width: 300px;max-width: 100%;fill: #aaa;}</style></head><body><div class="image">{svg}</div><div><h1>{title}</h1>{content}<p><a href="/">{btn}</a>.</p></div></body></html>';

    protected $defaultErrors = [
        '401' => [
            'html_data' => [
                'title' => 'Authorization needed',
                'btn' => 'Click here to login'
            ],
            'json' => [
                "status" => "error",
                "message" => "Authorization needed",
            ]
        ],
        '403' => [
            'html_data' => [
                'title' => 'Access denied',
            ],
            'json' => [
                "status" => "error",
                "message" => "Access denied",
            ]
        ],
        '404' => [
            'html_data' => [
                'title' => 'Page not found',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 511 511" xml:space="preserve"> <g> <path d="M39.5,103c1.97,0,3.91-0.8,5.3-2.2c1.4-1.39,2.2-3.33,2.2-5.3c0-1.98-0.8-3.91-2.2-5.3c-1.39-1.4-3.33-2.2-5.3-2.2 c-1.97,0-3.91,0.8-5.3,2.2c-1.4,1.39-2.2,3.33-2.2,5.3s0.8,3.91,2.2,5.3C35.59,102.2,37.52,103,39.5,103z"></path> <path d="M63.5,103c1.97,0,3.91-0.8,5.3-2.2c1.4-1.39,2.2-3.33,2.2-5.3s-0.8-3.91-2.2-5.3c-1.39-1.4-3.33-2.2-5.3-2.2 c-1.97,0-3.91,0.8-5.3,2.2c-1.4,1.39-2.2,3.33-2.2,5.3s0.8,3.91,2.2,5.3C59.59,102.2,61.53,103,63.5,103z"></path> <path d="M87.5,103c1.97,0,3.91-0.8,5.3-2.2c1.4-1.39,2.2-3.33,2.2-5.3s-0.8-3.91-2.2-5.3c-1.39-1.4-3.33-2.2-5.3-2.2 c-1.97,0-3.91,0.8-5.3,2.2c-1.4,1.39-2.2,3.33-2.2,5.3s0.8,3.91,2.2,5.3C83.59,102.2,85.53,103,87.5,103z"></path> <path d="M119.5,103h304c4.142,0,7.5-3.358,7.5-7.5s-3.358-7.5-7.5-7.5h-304c-4.142,0-7.5,3.358-7.5,7.5S115.358,103,119.5,103z"></path> <path d="M455.5,103h16c4.142,0,7.5-3.358,7.5-7.5s-3.358-7.5-7.5-7.5h-16c-4.142,0-7.5,3.358-7.5,7.5S451.358,103,455.5,103z"></path> <path d="M263.5,224h-16c-17.369,0-31.5,14.131-31.5,31.5v48c0,17.369,14.131,31.5,31.5,31.5h16c17.369,0,31.5-14.131,31.5-31.5v-48 C295,238.131,280.869,224,263.5,224z M280,303.5c0,9.098-7.402,16.5-16.5,16.5h-16c-9.098,0-16.5-7.402-16.5-16.5v-48 c0-9.098,7.402-16.5,16.5-16.5h16c9.098,0,16.5,7.402,16.5,16.5V303.5z"></path> <path d="M199.5,296H191v-64.5c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5V296h-38.094l20.709-62.128 c1.31-3.929-0.814-8.177-4.744-9.487c-3.929-1.311-8.177,0.814-9.487,4.743l-24,72c-0.762,2.287-0.379,4.801,1.031,6.757 S125.089,311,127.5,311H176v16.5c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5V311h8.5c4.142,0,7.5-3.358,7.5-7.5 S203.642,296,199.5,296z"></path> <path d="M383.5,296H375v-64.5c0-4.142-3.358-7.5-7.5-7.5s-7.5,3.358-7.5,7.5V296h-38.094l20.709-62.128 c1.31-3.929-0.814-8.177-4.744-9.487c-3.929-1.311-8.177,0.814-9.487,4.743l-24,72c-0.762,2.287-0.379,4.801,1.031,6.757 S309.089,311,311.5,311H360v16.5c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5V311h8.5c4.142,0,7.5-3.358,7.5-7.5 S387.642,296,383.5,296z"></path> <path d="M471.5,56h-432C17.72,56,0,73.72,0,95.5v320C0,437.28,17.72,455,39.5,455h432c21.78,0,39.5-17.72,39.5-39.5v-320 C511,73.72,493.28,56,471.5,56z M39.5,71h432c13.509,0,24.5,10.991,24.5,24.5V120H15V95.5C15,81.991,25.991,71,39.5,71z M471.5,440 h-432C25.991,440,15,429.009,15,415.5V135h481v280.5C496,429.009,485.009,440,471.5,440z"></path> </g> </svg>'
            ],
            'txt' => "Action not found\n------------------\n",
            'json' => [
                "status" => "error",
                "message" => "Page not found",
            ],
        ],
    ];

    protected $renderer = [];

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    public function addRenderer(int $code, callable $renderer)
    {
        $this->renderer[$code] = $renderer;
    }

    protected function defaultRenderer($code, $format)
    {
        if ($format === 'html') {
            return  str_replace(
                [
                    '{title}',
                    '{content}',
                    '{svg}',
                    '{btn}',
                ], [
                    $this->defaultErrors[$code]['html_data']['title'] ?? '',
                    $this->defaultErrors[$code]['html_data']['svg'] ?? '',
                    $this->defaultErrors[$code]['html_data']['content'] ?? '',
                    $this->defaultErrors[$code]['html_data']['btn'] ?? 'Click here to visit our main page'
                ],
                $this->defaultHtml
            );
        }
        return $this->defaultErrors[$code][$format];
    }

    /**
     * Execute the middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $code = $response->getStatusCode();
        $allowedCodes = array_keys($this->renderer) + array_keys($this->defaultErrors);
        if (in_array($code, $allowedCodes)) {
            if (!$response->getBody()->getSize()) {
                $format = FormatNegotiator::getPreferredFormat($request);

                if (isset($this->renderer[$code])) {
                    $content = $this->renderer[$code]($format);
                } else {
                    $content = $this->defaultRenderer($code, $format);
                }

                if (is_array($content)) {
                    $content = json_encode($content);
                }

                $body = $this->streamFactory->createStream($content);

                $response = $response->withBody($body);
                $response = $response->withStatus($code);

                if ($format === 'json') {
                    $response = $response->withHeader('content-type', 'application/json');
                } else {
                    $response = $response->withHeader('content-type', 'text/html; charset=utf-8');
                }
            }
        }

        return $response;
    }
}
