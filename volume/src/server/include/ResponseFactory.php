<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */

namespace Server;

use Nyholm\Psr7\Factory\Psr17Factory;


class ResponseFactory
{
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function create(): Response
    {
        $psr17Factory = new Psr17Factory();
        $response = $psr17Factory->createResponse();
        $stream = $psr17Factory->createStream();

        $response = new Response($response, $stream);
        return $response;
    }
}
