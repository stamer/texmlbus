<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

class ChartJsDataset implements \JsonSerializable
{
    /**
     * @var array
     */
    public static $colors =
        array(
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
        );

    /**
     * @var int
     */
    public static $idx = 0;

    /**
     * @var array of y-values
     */
    protected $data = array();

    /**
     * @var string
     */
    protected $label = 'Set';

    /**
     * @var string
     */
    protected $backgroundColor = 'rgba(200, 200, 200, 0.75)';

    /**
     * @var string
     */
    protected $borderColor = 'rgba(200, 200, 200, 0.75)';

    /**
     * @var string
     */
    protected $hoverBackgroundColor = 'rgba(200, 200, 200, 1)';

    /**
     * @var string
     */
    protected $hoverBorderColor = 'rgba(200, 200, 200, 1)';

    /**
     * ChartJsDataset constructor.
     * @param $label
     */
    public function __construct($label)
    {
        $colorValues = array_values(self::$colors);
        $this->label = $label;
        $this->backgroundColor = $colorValues[self::$idx];
        $this->borderColor = $colorValues[self::$idx];
        self::$idx = (self::$idx + 1) % count($colorValues);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        return $this->borderColor;
    }

    /**
     * @return string
     */
    public function getHoverBackgroundColor()
    {
        return $this->hoverBackgroundColor;
    }

    /**
     * @return string
     */
    public function getHoverBorderColor()
    {
        return $this->hoverBorderColor;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setBackgroundColor($backgroundColor)
    {
        if (isset(self::$colors[$backgroundColor])) {
            $this->backgroundColor = self::$colors[$backgroundColor];
        } else {
            $this->backgroundColor = $backgroundColor;
        }
    }

    public function setBorderColor($borderColor)
    {
        if (isset(self::$colors[$borderColor])) {
            $this->borderColor = self::$colors[$borderColor];
        } else {
            $this->borderColor = $borderColor;
        }
    }

    public function setHoverBackgroundColor($hoverBackgroundColor)
    {
        $this->hoverBackgroundColor = $hoverBackgroundColor;
    }

    public function setHoverBorderColor($hoverBorderColor)
    {
        $this->hoverBorderColor = $hoverBorderColor;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
