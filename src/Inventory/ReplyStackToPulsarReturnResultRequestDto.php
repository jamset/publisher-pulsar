<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 16.10.15
 * Time: 3:04
 */
namespace React\PublisherPulsar\Inventory;

use React\PublisherPulsar\Interfaces\ReplyStackControlDto;

class ReplyStackToPulsarReturnResultRequestDto implements ReplyStackControlDto
{
    /**
     * @var int
     */
    protected $considerMeAsSubscriber = 0;

    /**
     * @return int
     */
    public function getConsiderMeAsSubscriber()
    {
        return $this->considerMeAsSubscriber;
    }

    /**
     * @param int $considerMeAsSubscriber
     */
    public function setConsiderMeAsSubscriber($considerMeAsSubscriber)
    {
        $this->considerMeAsSubscriber = $considerMeAsSubscriber;
    }


}