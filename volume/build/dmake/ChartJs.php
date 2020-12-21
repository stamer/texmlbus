<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Interface to ChartJs
 *
 */

namespace Dmake;

use JsonSerializable;

class ChartJs implements JsonSerializable
{
    /**
     *
     * @var string[]
     */
    protected $labels = [];

    /**
     *
     * @var ChartJsDataset[]
     */
    protected $datasets = [];

    protected $canvasId = 1;

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getDatasets(): array
    {
        return $this->datasets;
    }

    public function getCanvasId(): int
    {
        return $this->canvasId;
    }

    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    public function setDatasets(array $datasets): void
    {
        $this->datasets = $datasets;
    }

    public function addDataset(string $label, array $yVals, string $color = ''): void
    {
        $dataset = new ChartJsDataset($label);

        $dataset->setData($yVals);
        $this->datasets[] = $dataset;
        if ($color != '') {
            $dataset->setBorderColor($color);
            $dataset->setBackgroundColor($color);
        }
    }

    public function setCanvasId(int $canvasId): void
    {
        $this->canvasId = $canvasId;
    }

    public function jsonSerialize(): array
    {
        $data['labels'] = $this->labels;
        $data['datasets'] = $this->datasets;
        $data['canvasid'] = $this->canvasId;
        return $data;
    }
}
