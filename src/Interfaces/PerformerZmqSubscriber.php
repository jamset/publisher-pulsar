<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 04.10.15
 * Time: 3:51
 */
namespace React\PublisherPulsar\Interfaces;

use React\PublisherPulsar\Inventory\ActionResultingPushDto;

interface PerformerZmqSubscriber
{

    /**Make a request and wait response in blocking mode. After response
     * start subscription.
     * @param null $dontWait
     * @return mixed
     */
    public function requestForActionPermission($dontWait = null);

    /**Wait subscription message before doing action
     * @return mixed
     */
    public function waitAllowingSubscriptionMessage();

    /**After action done worker-subscriber stop subscription and by push socket
     * send action's info
     * @param ActionResultingPushDto $resultingPushDto
     * @return mixed
     */
    public function pushActionResultInfo(ActionResultingPushDto $resultingPushDto);

    /**When performer process terminated unexpectedly
     * @return mixed
     */
    public function pushPerformerEarlyTerminated();

    /**When performer ready to get subscription message
     * @return mixed
     */
    public function pushReadyToGetSubscriptionMsg();

    /**When action result not influence on correction (e.x.,slowDown) Pulsar's behavior
     * @return mixed
     */
    public function pushActionResultInfoWithoutPulsarCorrectionBehavior();

}