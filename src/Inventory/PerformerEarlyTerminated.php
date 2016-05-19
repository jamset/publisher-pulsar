<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 14.10.15
 * Time: 6:03
 */
namespace React\PublisherPulsar\Inventory;

use React\PublisherPulsar\Abstracts\PushDto;

class PerformerEarlyTerminated extends PushDto
{

    /**
     * @var bool
     */
    protected $standOnSubscription = false;

    /**
     * @return boolean
     */
    public function isStandOnSubscription()
    {
        return $this->standOnSubscription;
    }

    /**
     * @param boolean $standOnSubscription
     */
    public function setStandOnSubscription($standOnSubscription)
    {
        $this->standOnSubscription = $standOnSubscription;
    }


}