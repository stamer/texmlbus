<?php
/**
 * MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

use JetBrains\PhpStorm\ArrayShape;

class GitControl
{
    public const PULL = 'pull -ff';
    public const VALID_COMMANDS = [self::PULL];

    public const OVERLEAF_PROTOCOL = 'https';
    public const OVERLEAF_HOST = 'git.overleaf.com';

    public function getKey(string $protocol, string $host, string $username): string
    {
        // this is just to create different ids for different urls.
        $key = crc32($protocol . $host . $username);
        return $key;
    }

    /**
     * Determines the username of document by parsing the url in .git/config.
     */
    public function getUsernameByDir(string $dir): string
    {
        $configFile = $dir . '/.git/config';
        $file = file_get_contents($configFile);
        if (empty($file)) {
            return '';
        }
        if (!preg_match('/url\s*=\s*(\S+)/', $file, $matches)) {
            return '';
        }

        if (empty($matches[1])) {
            return '';
        }

        $result = parse_url($matches[1]);
        $username = urldecode($result['user']);
        return $username;
    }

    /**
     * Get the cached credentials, otherwise an empty string is returned.
     */
    public function getCredentials(string $protocol, string $host, string $username) :string
    {
        $key = $this->getKey($protocol, $host, $username);
        $shm = new SharedMem($key);

        if (!$shm->has()) {
            $password = '';
        } else {
            $password = json_decode($shm->get());
        }
        return $password;
    }

    public function hasCredentials(string $protocol, string $host, string $username) :bool
    {
        $password = $this->getCredentials($protocol, $host, $username);
        return $password !== '';
    }

    /**
     * Writes the credentials in the shared memory cache.
     */
    public function putCredentials(string $protocol, string $host, string $username, string $password): void
    {
        $key = $this->getKey($protocol, $host, $username);
        $shm = new SharedMem($key);
        $shm->put(json_encode($password));
    }

    /**
     * Resets shared credentials.
     */
    public function resetCredentials(string $protocol, string $host, string $username) :void
    {
        $this->putCredentials($protocol, $host, $username, '');
    }

    /**
     * Creates a script header for the given password.
     * The file only contains echo "$PSW";
     */
    public function getScriptHeader(string $password) : string
    {
        $script = 'PSW=\'' . $password . '\' && export PSW && GIT_ASKPASS=$(mktemp) && chmod a+rx $GIT_ASKPASS && export GIT_ASKPASS' . "\n"
            . 'cat > $GIT_ASKPASS <<\'EOF\'' . PHP_EOL
            . '#!/bin/sh' . PHP_EOL
            . 'exec echo "$PSW"' . PHP_EOL
            . 'EOF' . PHP_EOL;
        return $script;
    }

    #[ArrayShape(['return_var' => "", 'message' => "false|string", 'success' => "bool"])]
    public function clone(
        string $protocol,
        string $host,
        string $path,
        string $username,
        string $password,
        bool $cache,
        string $destDir) :array
    {
        $cachedPassword = false;
        if (empty($password)) {
            $password = $this->getCredentials(
                $protocol,
                $host,
                $username
            );
            $cachedPassword = true;
        }

        $url = $protocol . '://' . urlencode($username) . '@' . $host . $path;

        $cfg = Config::getConfig();
        $script = $this->getScriptHeader($password)
                    . $cfg->app->git . ' clone ' . $url . ' ' . $destDir . ' 2>&1'
                    . '; rm -f $GIT_ASKPASS';

        $lastline = exec($script, $output, $return_var);
        if ($return_var) {
            $this->resetCredentials(
                $protocol,
                $host,
                $username
            );
            throw new \Exception('Failed to clone: ' . $lastline);
        }

        if ($cache && !$cachedPassword) {
            $this->putCredentials(
                $protocol,
                $host,
                $username,
                $password
            );
        }

        $result = [
            'return_var' => $return_var,
            'message' => $lastline,
            'success' => ($return_var === 0)
        ];
        return $result;
    }

    #[ArrayShape(['return_var' => "", 'message' => "false|string", 'output' => "", 'success' => "bool"])]
    public function execCommand(string $command, string $dir, ?string $password, bool $cache): array
    {
        $cfg = Config::getConfig();
        $cachedPassword = false;

        if (!in_array($command, self::VALID_COMMANDS)) {
            throw new \Exception('Invalid command ' . $command);
        }

        $username = $this->getUsernameByDir($dir);
        if (empty($username)) {
            throw new \Exception('Failed to determine username');
        }

        // If password has not been provided, try to get cached password.
        if (empty($password)) {
            $password = $this->getCredentials(
                self::OVERLEAF_PROTOCOL,
                self::OVERLEAF_HOST,
                $username
            );
            $cachedPassword = true;
        }

        if (empty($password)) {
            throw new \Exception('Failed to determine password, please try again');
        }

        //$this->configure();

        $script = $this->getScriptHeader($password)
            . 'cd ' . '"' . $dir . '"' . PHP_EOL
            . 'HOME=' . $cfg->server->homeDir . ' ' . $cfg->app->git . ' ' . $command . ' 2>&1'
            . '; rm -f $GIT_ASKPASS';

        $lastline = exec($script, $output, $return_var);
        if ($return_var) {
            $this->resetCredentials(
                self::OVERLEAF_PROTOCOL,
                self::OVERLEAF_HOST,
                $username
            );
            throw new \Exception('Failed to ' . $command . ': ' . $lastline);
        }

        if ($cache && !$cachedPassword) {
            $this->putCredentials(
                self::OVERLEAF_PROTOCOL,
                self::OVERLEAF_HOST,
                $username,
                $password
            );
        }

        $result = [
            'return_var' => $return_var,
            'message' => $lastline,
            'output' => $output,
            'success' => ($return_var === 0)
        ];
        return $result;
    }

    /**
     * @return null
     * @throws \Exception
     */
    #[ArrayShape(['return_var' => "", 'message' => "false|string", 'success' => "bool"])]
    public function cloneOverleaf(
        string $projectId,
        string $destDir,
        string $username,
        string $password,
        bool $cache = false
    ) :array
    {
        $path = '/' . $projectId;

        $result = $this->clone(
            self::OVERLEAF_PROTOCOL,
            self::OVERLEAF_HOST,
            $path,
            $username,
            $password,
            $cache,
            $destDir);

        return $result;
    }
}