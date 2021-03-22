<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *
 */
namespace Dmake;


/**
 * This class should not depend on any config. It is used
 * in Server and Worker.
 *
 * For Api calls to Worker and from worker
 *
 * Class Api
 *
 */
class ApiWorkerRequest implements \JsonSerializable
{
    protected $worker = '';
    protected $command = '';
    protected $stage = '';
    protected $makeAction = '';
    protected $directory = '';
    protected $parameter = '';

    /**
     * @return mixed
     */
    public function getWorker()
    {
        return $this->worker;
    }

    public function setWorker($worker) : self
    {
        $this->worker = $worker;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     * @return ApiWorkerRequest
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * @param mixed $stage
     * @return ApiWorkerRequest
     */
    public function setStage($stage)
    {
        $this->stage = $stage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMakeAction()
    {
        return $this->makeAction;
    }

    /**
     * @param mixed $action
     * @return ApiWorkerRequest
     */
    public function setMakeAction($makeAction)
    {
        $this->makeAction = $makeAction;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param mixed $directory
     * @return ApiWorkerRequest
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param mixed $parameter
     * @return ApiWorkerRequest
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }

    public function __construct($json = null)
    {
        if ($json) {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $this->setByArray($data);
        }
    }

    public function setByArray($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sub = new self;
                $sub->setByArray($value);
                $value = $sub;
            }
            $this->{$key} = $value;
        }
    }

    public function sendRequest() : ApiResult
    {
        $cfg = Config::getConfig();

        if (empty($this->getWorker())) {
            error_log(__METHOD__  . ": Empty worker!");
        }
        if (empty($this->getCommand())) {
            error_log(__METHOD__ . ": Empty command!");
        }

        $url = sprintf('http://%s/api/%s', $this->getWorker(), $this->getCommand());
        $data = json_encode($this);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, $cfg->timeout->default);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                           'Content-Type: application/json',
                           'Accept: application/json',
                           'Content-Length: ' . strlen($data))
        );

        $json = curl_exec($ch);

        $result = json_decode($json, true);

        if ($result === null) {
            if (is_string($json)) {
                $json = [$json];
            }
            return new ApiResult(false, $json, 99);
        } else {
            return new ApiResult($result['success'], $result['output'], $result['shellReturnVar']);
        }
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
