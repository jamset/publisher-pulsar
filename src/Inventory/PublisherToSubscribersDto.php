<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 04.10.15
 * Time: 3:46
 */
namespace React\PublisherPulsar\Inventory;

use React\PublisherPulsar\Interfaces\PulsarControlDto;

class PublisherToSubscribersDto implements PulsarControlDto
{
    /**
     * @var bool
     */
    protected $allowAction = true;

    /**
     * @return boolean
     */
    public function isAllowAction()
    {
        return $this->allowAction;
    }

    /**
     * @param boolean $allowAction
     */
    public function setAllowAction($allowAction)
    {
        $this->allowAction = $allowAction;
    }

}