<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Interface to ChartJs
 *
 */

namespace Dmake;

class ChartJs implements \JsonSerializable
{
    /**
     *
     * @var string[]
     */
    protected $labels = array();

    /**
     *
     * @var ChartJsDataset[]
     */
    protected $datasets = array();

    protected $canvasId = 1;

    public function getLabels()
    {
        return $this->labels;
    }

    public function getDatasets()
    {
        return $this->datasets;
    }

    public function getCanvasId()
    {
        return $this->canvasId;
    }

    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    public function setDatasets($datasets)
    {
        $this->datasets = $datasets;
    }

    /**
     * @param $label
     * @param $yVals
     * @param string $color
     */
    public function addDataset($label, $yVals, $color = '')
    {
        $dataset = new ChartJsDataset($label);

        $dataset->setData($yVals);
        $this->datasets[] = $dataset;
        if ($color != '') {
            $dataset->setBorderColor($color);
            $dataset->setBackgroundColor($color);
        }
    }

    public function setCanvasId($canvasId)
    {
        $this->canvasId = $canvasId;
    }

    public function jsonSerialize()
    {
        $data['labels'] = $this->labels;
        $data['datasets'] = $this->datasets;
        $data['canvasid'] = $this->canvasId;
        return $data;
    }
}
