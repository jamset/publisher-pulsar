<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 15.10.15
 * Time: 3:54
 */
namespace React\PublisherPulsar\Inventory;

use React\FractalBasic\Abstracts\Inventory\BaseReactDto;
use Monolog\Logger;

class ReplyStackDto extends BaseReactDto
{
    /**
     * @var string
     */
    protected $replyStackVsPulsarSocketAddress;

    /**
     * @var string
     */
    protected $replyStackVsPerformersSocketAddress;

    /**
     * @return mixed
     */
    public function getReplyStackVsPulsarSocketAddress()
    {
        return $this->replyStackVsPulsarSocketAddress;
    }

    /**
     * @param mixed $replyStackVsPulsarSocketAddress
     */
    public function setReplyStackVsPulsarSocketAddress($replyStackVsPulsarSocketAddress)
    {
        $this->replyStackVsPulsarSocketAddress = $replyStackVsPulsarSocketAddress;
    }

    /**
     * @return mixed
     */
    public function getReplyStackVsPerformersSocketAddress()
    {
        return $this->replyStackVsPerformersSocketAddress;
    }

    /**
     * @param mixed $replyStackVsPerformersSocketAddress
     */
    public function setReplyStackVsPerformersSocketAddress($replyStackVsPerformersSocketAddress)
    {
        $this->replyStackVsPerformersSocketAddress = $replyStackVsPerformersSocketAddress;
    }


}