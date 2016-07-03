<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 24.10.15
 * Time: 14:19
 */
namespace React\PublisherPulsar\Inventory;

use FractalBasic\ZMQ\Abstracts\Inventory\BaseZMQDto;

class PerformerDto extends BaseZMQDto
{
    /**
     * @var PerformerSocketsParamsDto
     */
    protected $socketsParams;

    /**
     * @return PerformerSocketsParamsDto
     */
    public function getSocketsParams()
    {
        return $this->socketsParams;
    }

    /**
     * @param PerformerSocketsParamsDto $socketsParams
     */
    public function setSocketsParams(PerformerSocketsParamsDto $socketsParams)
    {
        $this->socketsParams = $socketsParams;
    }


}