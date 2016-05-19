<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 11.10.15
 * Time: 18:14
 */
namespace React\PublisherPulsar\Inventory;

class PerformerSocketsParamsDto
{
    /**
     * @var string
     */
    protected $requestPulsarRsSocketAddress;

    /**
     * @var string
     */
    protected $publisherPulsarSocketAddress;

    /**
     * @var string
     */
    protected $pushPulsarSocketAddress;

    /**Allow to pause/continue worker (performer) process due to communication between
     * PM/workers (performers) by PUBLISHER/SUBSCRIBER ZMQ connection
     * @var string
     */
    protected $publisherPmSocketAddress;

    /**
     * @return string
     */
    public function getPublisherPmSocketAddress()
    {
        return $this->publisherPmSocketAddress;
    }

    /**
     * @param string $publisherPmSocketAddress
     */
    public function setPublisherPmSocketAddress($publisherPmSocketAddress)
    {
        $this->publisherPmSocketAddress = $publisherPmSocketAddress;
    }

    /**
     * @return string
     */
    public function getRequestPulsarRsSocketAddress()
    {
        return $this->requestPulsarRsSocketAddress;
    }

    /**
     * @param string $requestPulsarRsSocketAddress
     */
    public function setRequestPulsarRsSocketAddress($requestPulsarRsSocketAddress)
    {
        $this->requestPulsarRsSocketAddress = $requestPulsarRsSocketAddress;
    }

    /**
     * @return string
     */
    public function getPublisherPulsarSocketAddress()
    {
        return $this->publisherPulsarSocketAddress;
    }

    /**
     * @param string $publisherPulsarSocketAddress
     */
    public function setPublisherPulsarSocketAddress($publisherPulsarSocketAddress)
    {
        $this->publisherPulsarSocketAddress = $publisherPulsarSocketAddress;
    }

    /**
     * @return string
     */
    public function getPushPulsarSocketAddress()
    {
        return $this->pushPulsarSocketAddress;
    }

    /**
     * @param string $pushPulsarSocketAddress
     */
    public function setPushPulsarSocketAddress($pushPulsarSocketAddress)
    {
        $this->pushPulsarSocketAddress = $pushPulsarSocketAddress;
    }


}