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
    private ResponseInterface $response;
    private StreamInterface $stream;

    public function __construct(ResponseInterface $response, StreamInterface $stream)
    {
        $this->response = $response;
        $this->stream = $stream;
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion($version): static
    {
        $response = $this->response->withProtocolVersion($version);
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader($name): string|array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader($name, $value): static
    {
        $response = $this->response->withHeader($name, $value);
        return new static($response, $this->stream);
    }

    public function withAddedHeader($name, $value): static
    {
        $response = $this->response->withAddedHeader($name, $value);
        return new static($response, $this->stream);
    }

    public function withoutHeader($name): static
    {
        $response = $this->response->withoutHeader($name);
        return new static($response, $this->stream);
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        $response = $this->response->withBody($body);
        return new static($response, $this->stream);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = ''): static
    {
        $response = $this->response->withStatus($code, $reasonPhrase);
        return new static($response, $this->stream);
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function write($string): int
    {
        return $this->response->getBody()->write($string);
    }

    public function json($data, $status = 200): bool
    {
        $this->response = $this->response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
        $this->write(json_encode($data));
        return $this->emit();
    }

    public function emit(): bool
    {
        return (new SapiEmitter())->emit($this->response);
    }
}
