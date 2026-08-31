<?php

namespace Give\Vendors\Psr\Http\Client;

use Give\Vendors\Psr\Http\Message\RequestInterface;
use Give\Vendors\Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \Give\Vendors\Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
