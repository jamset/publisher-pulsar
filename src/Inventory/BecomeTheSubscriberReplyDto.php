<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 04.10.15
 * Time: 3:49
 */
namespace React\PublisherPulsar\Inventory;

use React\PublisherPulsar\Interfaces\PulsarControlDto;

class BecomeTheSubscriberReplyDto implements PulsarControlDto
{
    /**
     * @var bool
     */
    protected $allowToStartSubscription = true;

    /**
     * @return boolean
     */
    public function isAllowToStartSubscription()
    {
        return $this->allowToStartSubscription;
    }

    /**
     * @param boolean $allowToStartSubscription
     */
    public function setAllowToStartSubscription($allowToStartSubscription)
    {
        $this->allowToStartSubscription = $allowToStartSubscription;
    }


}
