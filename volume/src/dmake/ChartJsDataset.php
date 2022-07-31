<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

use JsonSerializable;

class ChartJsDataset implements JsonSerializable
{
    /**
     * @var array
     */
    public static array $colors =
        [
            'darkRed' => 'rgba(238,  53,  46, 0.75)', // dark red, 123
            'darkGreen' => 'rgba(  0, 147,  60, 0.75)', // dark green, 456
            'blue' => 'rgba(  0,  57, 166, 0.75)', // blue, ACE
            'yellow' => 'rgba(252, 204,  10, 0.75)', // yellow-orange, NQR
            'brown' => 'rgba(153, 102,  51, 0.75)', // brown, JZ
            'orange' => 'rgba(255,  99,  25, 0.75)', // orange, BDFM
            'lightGreen' => 'rgba(108, 190,  69, 0.75)', // light green, G
            'grey' => 'rgba(167, 169, 172, 0.75)', // grey, L
            'darkPink' => 'rgba(185,  51, 173, 0.75)', // dark pink, 7
            'green2' => 'rgba(  0, 105, 131, 0.75)', // green, Montauk Branch
            'lightBlue' => 'rgba(  0, 161, 222, 0.75)', // light blue, West Hempstead Branch
        ];

    public static int $idx = 0;

    protected array $data = [];

    protected string $label = 'Set';

    protected string $backgroundColor = 'rgba(200, 200, 200, 0.75)';

    protected string $borderColor = 'rgba(200, 200, 200, 0.75)';

    protected string $hoverBackgroundColor = 'rgba(200, 200, 200, 1)';

    protected string $hoverBorderColor = 'rgba(200, 200, 200, 1)';

    /**
     * ChartJsDataset constructor.
     */
    public function __construct(string $label)
    {
        $colorValues = array_values(self::$colors);
        $this->label = $label;
        $this->backgroundColor = $colorValues[self::$idx];
        $this->borderColor = $colorValues[self::$idx];
        self::$idx = (self::$idx + 1) % count($colorValues);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function getBorderColor(): string
    {
        return $this->borderColor;
    }

    public function getHoverBackgroundColor(): string
    {
        return $this->hoverBackgroundColor;
    }

    public function getHoverBorderColor(): string
    {
        return $this->hoverBorderColor;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    public function setLabel($label): void
    {
        $this->label = $label;
    }

    public function setBackgroundColor($backgroundColor): void
    {
        if (isset(self::$colors[$backgroundColor])) {
            $this->backgroundColor = self::$colors[$backgroundColor];
        } else {
            $this->backgroundColor = $backgroundColor;
        }
    }

    public function setBorderColor($borderColor): void
    {
        if (isset(self::$colors[$borderColor])) {
            $this->borderColor = self::$colors[$borderColor];
        } else {
            $this->borderColor = $borderColor;
        }
    }

    public function setHoverBackgroundColor($hoverBackgroundColor): void
    {
        $this->hoverBackgroundColor = $hoverBackgroundColor;
    }

    public function setHoverBorderColor($hoverBorderColor): void
    {
        $this->hoverBorderColor = $hoverBorderColor;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
