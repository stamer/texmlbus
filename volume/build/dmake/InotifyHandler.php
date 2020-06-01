<?php
/**
 * MIT License
 * (c) 2018 - 2019 Heinrich Stamerjohanns
 *
 * Inotify is an extension to php, therefore it is necessary
 * to check the availability of the extension.
 *
 * If the extension is active, dmake will wait
 * for a trigger, otherwise it will just poll every 30 seconds.
 *
 */

namespace Dmake;

/**
 * Class InotifyHandler
 */
class InotifyHandler
{
    const wqTrigger = 'wq';
    const doneTrigger = 'done';

    // triggers need to be on docker-managed volume.
    // docker on windows wsl2 cannot handle inotify events on shared volumes

    // write queue trigger
    const wqTriggerFile = '/opt/run/wq_trigger';

    // write done trigger to inform about finished jobs
    const doneTriggerFile = '/opt/run/done_trigger';

    private $debug = false;

    private $active = false;

    private $trigger = [self::wqTrigger, self::doneTrigger];

    private $triggerFile = [self::wqTrigger => '', self::doneTrigger => ''];

    private $fd = [self::wqTrigger => null, self::doneTrigger => null];

    private $watchDescriptor = [self::wqTrigger => null, self::doneTrigger => null];

    /**
     * InotifyHandler constructor.
     */
	public function __construct()
    {
        if (function_exists('inotify_init')) {
            $this->active = true;
        }
        if ($this->debug) {
            error_log('BUILDDIR: ' . BUILDDIR);
        }
        $this->triggerFile[self::wqTrigger] = self::wqTriggerFile;
        if (!is_file($this->triggerFile[self::wqTrigger])) {
            touch($this->triggerFile[self::wqTrigger]);
        }
        $perms = substr(sprintf('%o', fileperms($this->triggerFile[self::wqTrigger])), -3);
        if ($perms != '666') {
            chmod($this->triggerFile[self::wqTrigger], 0666);
        }
        $this->triggerFile[self::doneTrigger] = self::doneTriggerFile;
        if (!is_file($this->triggerFile[self::doneTrigger])) {
            touch($this->triggerFile[self::doneTrigger]);
        }
        $perms = substr(sprintf('%o', fileperms($this->triggerFile[self::doneTrigger])), -3);
        if ($perms != '666') {
            chmod($this->triggerFile[self::doneTrigger], 0666);
        }
	}

    /**
     * Sets up the watcher.
     * Use a non-blocking read, stream_select() will wait, so we can still receive signals.
     *
     * @param $triggerName
     */
	public function setupWatcher($triggerName)
	{
	    if (!in_array($triggerName, $this->trigger)) {
	        error_log("Unknown watcher $triggerName");
	        return;
        }
        if ($this->active) {
            $this->fd[$triggerName] = inotify_init();
            if (!$this->fd[$triggerName]) {
                error_log(__METHOD__ . ": Failed to inotify_init!");
                $this->active = false;
            } else {
                error_log("Adding wq watch..");
                $this->watchDescriptor[$triggerName] =
                    inotify_add_watch($this->fd[$triggerName], $this->triggerFile[$triggerName], IN_ATTRIB);
                stream_set_blocking($this->fd[$triggerName], false);
            }
        }
	}

    /**
     * Waits for some action.
     * @param $triggerName
     */
	public function wait($triggerName)
    {
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Cannot wait on unknown trigger $triggerName");
            return;
        }
        if ($this->active) {
            // php does not catch signals while inside a blocking read.
            // instead use not blocking reads and use stream_select with timeout
            stream_set_blocking($this->fd[$triggerName], false);
            while (1) {
                // if we work with declare ticks, the interrupted system call might just
                // restart, call pcntl_signal_dispatch to handle signals
                if (version_compare(PHP_VERSION, '7.1.0', '<')) {
                    pcntl_signal_dispatch();
                }
                $r = array($this->fd[$triggerName]);
                $timeout = 600;
                $w = array();
                $e = array();
                if ($this->debug) {
                    echo "stream_select..." . PHP_EOL;
                }

                // stream select spits a warning when interrupted by a signal, which is not really wanted behaviour.
                // make it quiet, but actually check the error val, in case something goes really wrong.
                $numChangedResources = @stream_select($r, $w, $e, $timeout);

                // $numChangedResources === 0, timeout happened ==> ok and expected
                if ($numChangedResources === false) {
                    // we have been interrupted by a signal (possibly by child, ok as well)
                    $err = error_get_last();
                    if (!isset($err['message'])
                        || stripos($err['message'], 'interrupted system call') === false
                    ) {
                        throw new \RuntimeException(sprintf('Error waiting in execution loop: %s', $err['message']));
                    }
                }
                if ($numChangedResources != 0) {
                    if ($this->debug) {
                        echo "inotify_read..." . PHP_EOL;
                    }
                    $events = inotify_read($this->fd[$triggerName]);
                    if ($events) {
                        break;
                    }
                }
            }
        }
	}

    /**
     * Returns whether the InotifyHandler is active.
     * @return bool
     */
	public function isActive()
	{
        return $this->active;
    }

    /**
     * Gets the corresponding trigger file by given name.
     * @param $triggerName
     * @return string
     */
    public function getTriggerFile($triggerName)
	{
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Unknown trigger $triggerName");
            return '';
        }
        return $this->triggerFile[$triggerName];
    }

    /**
     * Triggers the given trigger by its name.
     * @param $triggerName
     * @return bool|string
     */
	public function trigger($triggerName)
	{
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Cannot trigger unknown trigger $triggerName");
            return '';
        }
        $result = false;
        if ($this->active) {
            $result = touch($this->triggerFile[$triggerName]);
        }
        return $result;
    }
}
