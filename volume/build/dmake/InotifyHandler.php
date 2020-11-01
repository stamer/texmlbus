<?php
/**
 * MIT License
 * (c) 2018 - 2020 Heinrich Stamerjohanns
 *
 * Inotify is an extension to php, therefore it is necessary
 * to check the availability of the extension.
 *
 * If the extension is active, dmake will wait
 * for a trigger, otherwise it will just poll every 60 seconds.
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
    const wqTriggerFilePrefix = '/opt/run/wq_trigger';

    // write done trigger to inform about finished jobs
    const doneTriggerFilePrefix = '/opt/run/done_trigger';

    private $debug = false;

    private $active = false;

    private $trigger = [self::wqTrigger, self::doneTrigger];

    private $triggerFile = [];

    private $fd = [];

    private $watchDescriptor = [];

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

        $hostGroups = UtilStage::getHostGroups();
        foreach ($hostGroups as $hostGroupName) {
            $filename = self::wqTriggerFilePrefix . '_' . $hostGroupName;
            $this->triggerFile[$hostGroupName][self::wqTrigger] = $filename;
            if (!is_file($this->triggerFile[$hostGroupName][self::wqTrigger])) {
                touch($this->triggerFile[$hostGroupName][self::wqTrigger]);
            }
            $perms = substr(sprintf('%o', fileperms($this->triggerFile[$hostGroupName][self::wqTrigger])), -3);
            if ($perms != '666') {
                chmod($this->triggerFile[$hostGroupName][self::wqTrigger], 0666);
            }
            $filename = self::doneTriggerFilePrefix . '_' . $hostGroupName;
            $this->triggerFile[$hostGroupName][self::doneTrigger] = $filename;
            if (!is_file($this->triggerFile[$hostGroupName][self::doneTrigger])) {
                touch($this->triggerFile[$hostGroupName][self::doneTrigger]);
            }
            $perms = substr(sprintf('%o', fileperms($this->triggerFile[$hostGroupName][self::doneTrigger])), -3);
            if ($perms != '666') {
                chmod($this->triggerFile[$hostGroupName][self::doneTrigger], 0666);
            }
        }
    }

    /**
     * Sets up the watcher for any host group.
     * Use a non-blocking read, stream_select() will wait, so we can still receive signals.
     */
    public function setupWatcherAnyHostGroup(string $triggerName): void
    {
        $hostGroups = UtilStage::getHostGroups();
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Unknown watcher $triggerName");
            return;
        }
        if ($this->active) {
            foreach ($hostGroups as $hostGroupName) {
                $this->fd[$hostGroupName][$triggerName] = inotify_init();
                if (!$this->fd[$hostGroupName][$triggerName]) {
                    error_log(__METHOD__ . ": Failed to inotify_init!");
                    $this->active = false;
                } else {
                    error_log("Adding wq watch for $hostGroupName...");
                    $this->watchDescriptor[$hostGroupName][$triggerName] =
                        inotify_add_watch(
                            $this->fd[$hostGroupName][$triggerName],
                            $this->triggerFile[$hostGroupName][$triggerName],
                            IN_ATTRIB
                        );
                    stream_set_blocking($this->fd[$hostGroupName][$triggerName], false);
                }
            }
        }
    }

    /**
     * Sets up the watcher.
     * Use a non-blocking read, stream_select() will wait, so we can still receive signals.
     */
	public function setupWatcher(string $hostGroupName, string $triggerName): void
	{
	    if (!in_array($triggerName, $this->trigger)) {
	        error_log("Unknown watcher $triggerName");
	        return;
        }
        if ($this->active) {
            $this->fd[$hostGroupName][$triggerName] = inotify_init();
            if (!$this->fd[$hostGroupName][$triggerName]) {
                error_log(__METHOD__ . ": Failed to inotify_init!");
                $this->active = false;
            } else {
                error_log("Adding wq watch for $hostGroupName...");
                $this->watchDescriptor[$hostGroupName][$triggerName] =
                    inotify_add_watch($this->fd[$hostGroupName][$triggerName], $this->triggerFile[$hostGroupName][$triggerName], IN_ATTRIB);
                stream_set_blocking($this->fd[$hostGroupName][$triggerName], false);
            }
        }
	}

    /**
     * Waits for some action.
     */
	public function waitAnyHostGroup(string $triggerName)
    {
        $hostGroups = UtilStage::getHostGroups();
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Cannot wait on unknown trigger  $triggerName");
            return;
        }
        if ($this->active) {
            // php does not catch signals while inside a blocking read.
            // instead use not blocking reads and use stream_select with timeout
            // wait on all fds of triggerName
            foreach ($hostGroups as $hostGroupName) {
                stream_set_blocking($this->fd[$hostGroupName][$triggerName], false);
            }
            while (1) {
                // if we work with declare ticks, the interrupted system call might just
                // restart, call pcntl_signal_dispatch to handle signals
                if (version_compare(PHP_VERSION, '7.1.0', '<')) {
                    pcntl_signal_dispatch();
                }
                $readFds = [];
                foreach ($hostGroups as $hostGroupName) {
                    $readFds[] = $this->fd[$hostGroupName][$triggerName];
                }
                $timeout = 600;
                $w = [];
                $e = [];
                if ($this->debug) {
                    error_log("stream_select...");
                }

                // stream select spits a warning when interrupted by a signal, which is not really wanted behaviour.
                // make it quiet, but actually check the error val, in case something goes really wrong.
                $numChangedResources = @stream_select($readFds, $w, $e, $timeout);

                // $numChangedResources === 0, timeout happened _==> ok and expected
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
                        error_log("inotify_read...");
                    }
                    foreach ($hostGroups as $hostGroupName) {
                        $events = inotify_read($this->fd[$hostGroupName][$triggerName]);
                        if ($events) {
                            break 2;
                        }
                    }
                }
            }
        }
	}

    /**
     * Waits for some action.
     */
    public function wait(string $hostGroupName, string $triggerName)
    {
        error_log(__METHOD__ . ": HostGroupName: $hostGroupName, Trigger: $triggerName");
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Cannot wait on unknown trigger  $triggerName");
            return;
        }
        if ($this->active) {
            // php does not catch signals while inside a blocking read.
            // instead use not blocking reads and use stream_select with timeout
            // wait on all fds of triggerName
            stream_set_blocking($this->fd[$hostGroupName][$triggerName], false);
            while (1) {
                // if we work with declare ticks, the interrupted system call might just
                // restart, call pcntl_signal_dispatch to handle signals
                if (version_compare(PHP_VERSION, '7.1.0', '<')) {
                    pcntl_signal_dispatch();
                }
                $readFds = [$this->fd[$hostGroupName][$triggerName]];
                $w = [];
                $e = [];
                $timeout = 600;
                if ($this->debug) {
                    error_log("stream_select...");
                }

                // stream_select spits a warning when interrupted by a signal, which is not really wanted behaviour.
                // make it quiet, but actually check the error val, in case something goes really wrong.
                $numChangedResources = @stream_select($readFds, $w, $e, $timeout);

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
                if ($numChangedResources !== 0) {
                    if ($this->debug) {
                        error_log("inotify_read...");
                    }
                    $events = inotify_read($this->fd[$hostGroupName][$triggerName]);
                    if ($events) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Returns whether the InotifyHandler is active.
     */
	public function isActive(): bool
	{
        return $this->active;
    }

    /**
     * Gets the corresponding trigger file by given name.
     * @return string
     */
    public function getTriggerFile(string $hostGroupName, string $triggerName)
	{
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Unknown trigger $triggerName");
            return '';
        }
        return $this->triggerFile[$hostGroupName][$triggerName];
    }

    /**
     * Triggers the given trigger by its name.
     * @return bool|string
     */
	public function trigger(string $hostGroupName, string $triggerName)
	{
        if (!in_array($triggerName, $this->trigger)) {
            error_log("Cannot trigger unknown trigger $triggerName");
            return '';
        }
        $result = false;
        if ($this->active) {
            $result = touch($this->triggerFile[$hostGroupName][$triggerName]);
        }
        return $result;
    }
}
