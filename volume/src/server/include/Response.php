<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
namespace Server;

use Psr\Http\Message\ResponseInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\StreamInterface;

class Response
{
    private $response;
    private $stream;

    public function __construct(ResponseInterface $response, StreamInterface $stream)
    {
        $this->response = $response;
        $this->stream = $stream;
    }

    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion($version)
    {
        $response = $this->response->withProtocolVersion($version);
    }

    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader($name)
    {
        return $this->getHeader($name);
    }

    public function getHeaderLine($name)
    {
        return $this->getHeaderLine($name);
    }

    public function withHeader($name, $value)
    {
        $response = $this->response->withHeader($name, $value);
        return new static($response, $this->stream);
    }

    public function withAddedHeader($name, $value)
    {
        $response = $this->response->withAddedHeader($name, $value);
        return new static($response, $this->stream);
    }

    public function withoutHeader($name)
    {
        $response = $this->response->withoutHeader($name);
        return new static($response, $this->stream);
    }

    public function getBody()
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        $response = $this->response->withBody($body);
        return new static($response, $this->stream);
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $response = $this->response->withStatus($code, $reasonPhrase);
        return new static($response, $this->stream);
    }

    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    public function write($string)
    {
        $this->response->getBody()->write($string);
    }

    public function json($data, $status = 200)
    {
        $this->response = $this->response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
        $this->write(json_encode($data));
        $this->emit();
    }

    public function emit()
    {
        (new SapiEmitter())->emit($this->response);
    }
}
