<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 15.10.15
 * Time: 3:57
 */
namespace React\PublisherPulsar\Inventory;

use React\PublisherPulsar\Interfaces\PulsarControlDto;
use React\PublisherPulsar\Interfaces\ReplyStackControlDto;

class PulsarToReplyStackReplyDto implements ReplyStackControlDto
{
    /**
     * @var int
     */
    protected $subscribersNumber;

    /**
     * @var PulsarControlDto
     */
    protected $dtoToTransfer;

    /**
     * @return int
     */
    public function getSubscribersNumber()
    {
        return $this->subscribersNumber;
    }

    /**
     * @param int $subscribersNumber
     */
    public function setSubscribersNumber($subscribersNumber)
    {
        $this->subscribersNumber = $subscribersNumber;
    }

    /**
     * @return PulsarControlDto
     */
    public function getDtoToTransfer()
    {
        return $this->dtoToTransfer;
    }

    /**
     * @param PulsarControlDto $dtoToTransfer
     */
    public function setDtoToTransfer($dtoToTransfer)
    {
        $this->dtoToTransfer = $dtoToTransfer;
    }


}