<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
namespace Server;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequest
{
    private $serverRequest;

    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    public function getServerParams()
    {
        return $this->serverRequest->getServerParams();
    }

    public function getCookieParams()
    {
        return $this->serverRequest->getCookieParams();
    }

    public function withCookieParams(array $cookies)
    {
        $serverRequest = $this->serverRequest->withCookieParams($cookies);
        return new static($serverRequest);
    }

    public function getQueryParams()
    {
        return $this->serverRequest->getQueryParams();
    }

    public function withQueryParams(array $query)
    {
        $serverRequest = $this->serverRequest->withQueryParams($query);
        return new static($serverRequest);
    }

    public function getUploadedFiles()
    {
        return $this->serverRequest->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $serverRequest = $this->serverRequest->withUploadedFiles($uploadedFiles);
        return new static($serverRequest);
    }

    public function getParsedBody()
    {
        return $this->serverRequest->getParsedBody();
    }

    public function withParsedBody($data)
    {
        $serverRequest =  $this->serverRequest->withParsedBody($data);
        return new static($serverRequest);
    }

    public function getAttributes()
    {
        return $this->serverRequest->getAttributes();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->serverRequest->getAttribute($name, $default);
    }

    public function withAttribute($name, $value)
    {
        $serverRequest = $this->serverRequest->withAttribute($name, $value);
        return new static($serverRequest);
    }

    public function withoutAttribute($name)
    {
        $serverRequest = $this->serverRequest->withoutAttribute();
        return new static($serverRequest);
    }

    public function getParam($name, $default = null)
    {
        $postParams = $this->getParsedBody();
        $queryParams = $this->getQueryParams();

        if (is_array($postParams) && isset($postParams[$name])) {
            return $postParams[$name];
        } elseif (is_object($postParams) && property_exists($postParams, $name)) {
            return $postParams->$name;
        } elseif (isset($queryParams[$name])) {
            return $queryParams[$name];
        }
        return $default;
    }

    public function getCookieParam($name, $default = null)
    {
        return isset($this->getCookieParams()[$name]) ? $this->getCookieParams()[$name] : $default;
    }

    public function getQueryParam($name, $default = null)
    {
        return isset($this->getQueryParams()[$name]) ? $this->getQueryParams()[$name] : $default;
    }

    public function getServerParam($name, $default = null)
    {
        return isset($this->serverRequest->getServerParams()[$name]) ? $this->getServerParams()[$name] : $default;
    }
}
