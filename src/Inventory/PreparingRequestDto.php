<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 04.10.15
 * Time: 3:50
 */
namespace React\PublisherPulsar\Inventory;

use React\PublisherPulsar\Interfaces\PulsarControlDto;

class PreparingRequestDto implements PulsarControlDto
{
    /**
     * @var bool
     */
    protected $readyToAct = true;

    /**
     * @var \Exception
     */
    protected $preparingErrorOccurred;

    /**
     * @return \Exception
     */
    public function getPreparingErrorOccurred()
    {
        return $this->preparingErrorOccurred;
    }

    /**
     * @param \Exception $preparingErrorOccurred
     */
    public function setPreparingErrorOccurred(\Exception $preparingErrorOccurred)
    {
        $this->preparingErrorOccurred = $preparingErrorOccurred;
    }

    /**
     * @return boolean
     */
    public function isReadyToAct()
    {
        return $this->readyToAct;
    }

    /**
     * @param boolean $readyToAct
     */
    public function setReadyToAct($readyToAct)
    {
        $this->readyToAct = $readyToAct;
    }


}