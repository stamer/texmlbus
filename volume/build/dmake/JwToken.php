<?php
/**
 * MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

use Nowakowskir\JWT\Base64Url;
use Nowakowskir\JWT\TokenDecoded;
use Nowakowskir\JWT\TokenEncoded;
use Nowakowskir\JWT\JWT;
use Nowakowskir\JWT\Exceptions\IntegrityViolationException;
use Server\RequestFactory;

class JwToken
{
    const PRIVATE_KEYFILE = '/tmp/private.key';
    const PUBLIC_KEY = '/tmp/public.pub';

    /**
     *
     */
    public static function createKeys()
    {
        $command = '/usr/bin/ssh-keygen -t rsa -b 4096 -m PEM -P "" -f ' . self::PRIVATE_KEYFILE;
        exec ($command, $output, $return_var);
        if ($return_var) {
            error_log($command . ' failed!');
        }
        $command = '/usr/bin/openssl rsa -in ' . self::PRIVATE_KEYFILE . ' -pubout -outform PEM -out ' . self::PUBLIC_KEY;
        exec ($command, $output, $return_var);
        if ($return_var) {
            error_log($command . ' failed!');
        }
    }

    /**
     * @return TokenEncoded
     * @throws \Exception
     */
    public static function create()
    {
        $payload = ['random' => base64_encode(random_bytes(32))];
        $tokenDecoded = new TokenDecoded([], $payload);

        $privateKey = @file_get_contents(self::PRIVATE_KEYFILE);
        if ($privateKey === false) {
            echo 'creating Keys..';
            self::createKeys();
            $privateKey = file_get_contents(self::PRIVATE_KEYFILE);
        }

        $tokenEncoded = $tokenDecoded->encode($privateKey, JWT::ALGORITHM_RS256);
        return $tokenEncoded;
    }

    /**
     * @param $token
     * @return bool
     */
    public static function validate($token)
    {
        try {
            $tokenEncoded = new TokenEncoded($token);
        } catch (\Exception $e) {
            error_log("ParseException");
            return false;
        }
        $publicKey = file_get_contents(self::PUBLIC_KEY);
        if ($publicKey === false) {
            echo 'creating Keys..';
            self::createKeys();
            $publicKey = file_get_contents(self::PRIVATE_KEYFILE);
        }
        try {
            $tokenEncoded->validate($publicKey, JWT::ALGORITHM_RS256);
        } catch (IntegrityViolationException $e) {
            // Handle token not trusted
            return false;
        } catch (\Exception $e) {
            // Handle other validation exceptions
            return false;
        }
        return true;
    }

    /**
     * authenticate if sent via ajax-Request, possibly external
     */
    public static function authenticate()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        if ($token === null
            || !self::validate($token)
        ) {
            header('Not authorized', true, 403);
            exit;
        }
    }

    /*
     * authenticate via Cookie
     */
    public static function authenticateByCookie()
    {
        $request = RequestFactory::create();
        $token = $request->getCookieParam('jwToken');
        if ($token === null
            || !self::validate($token)
        ) {
            header('Not authorized', true, 403);
            exit;
        }
    }
}
