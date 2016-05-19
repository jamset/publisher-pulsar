<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 11.10.15
 * Time: 18:19
 */
namespace React\PublisherPulsar\Inventory;

class PulsarSocketsParamsDto
{

    /**
     * @var string
     */
    protected $replyStackSocketAddress;

    /**
     * @var string
     */
    protected $replyToReplyStackSocketAddress;

    /**
     * @var string
     */
    protected $pushToReplyStackSocketAddress;

    /**
     * @var string
     */
    protected $publishSocketAddress;

    /**
     * @var string
     */
    protected $pullSocketAddress;

    /**
     * @return string
     */
    public function getPushToReplyStackSocketAddress()
    {
        return $this->pushToReplyStackSocketAddress;
    }

    /**
     * @param string $pushToReplyStackSocketAddress
     */
    public function setPushToReplyStackSocketAddress($pushToReplyStackSocketAddress)
    {
        $this->pushToReplyStackSocketAddress = $pushToReplyStackSocketAddress;
    }

    /**
     * @return string
     */
    public function getReplyStackSocketAddress()
    {
        return $this->replyStackSocketAddress;
    }

    /**
     * @param string $replyStackSocketAddress
     */
    public function setReplyStackSocketAddress($replyStackSocketAddress)
    {
        $this->replyStackSocketAddress = $replyStackSocketAddress;
    }

    /**
     * @return string
     */
    public function getReplyToReplyStackSocketAddress()
    {
        return $this->replyToReplyStackSocketAddress;
    }

    /**
     * @param string $replyToReplyStackSocketAddress
     */
    public function setReplyToReplyStackSocketAddress($replyToReplyStackSocketAddress)
    {
        $this->replyToReplyStackSocketAddress = $replyToReplyStackSocketAddress;
    }

    /**
     * @return string
     */
    public function getPublishSocketAddress()
    {
        return $this->publishSocketAddress;
    }

    /**
     * @param string $publishSocketAddress
     */
    public function setPublishSocketAddress($publishSocketAddress)
    {
        $this->publishSocketAddress = $publishSocketAddress;
    }

    /**
     * @return string
     */
    public function getPullSocketAddress()
    {
        return $this->pullSocketAddress;
    }

    /**
     * @param string $pullSocketAddress
     */
    public function setPullSocketAddress($pullSocketAddress)
    {
        $this->pullSocketAddress = $pullSocketAddress;
    }


}
