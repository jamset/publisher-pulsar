# Publisher Pulsar
ReactPHP and ZMQ based module allowing to coordinate set of independent processes. I.e. to not exceed API RPS limits 
(i.e. Google Analytics 10 RPS, where RPS size is actual for November 2015)

##Install

`composer require jamset/publisher-pulsar`

Additional needed libs installation guide could be found [here](https://github.com/jamset/gearman-conveyor/blob/master/docs/environment.md). Section "Install PECL" and "Optional".

Note: not tested on PHP7

##Description

The idea that PublisherPulsar is the daemon, which allow to make some action simultaneously (i.e. connection to API) 
for certain number of processes ('subscribers'). 

I.e. limit for Google Analytics is 10 request per second, and so you can include in code of such processes ('services',
that contain Performer class with relevant Pulsar integrated commands) connection to Pulsar, set in Pulsar settings 
limit for 10 subscribers per iteration, set iteration size 1 second, and start daemon and processes. 

All processes beginning after it will be connect to special stack (ReplyStack), which will notify Pulsar that subscribers are ready to make an action when all needed number of processes
will be active (executed and paused on point that need Pulsar permission to execute further).

After it Pulsar send allowing message to processes (subscribers), that allow them to continue their execution, i.e. 
make a request to API. And so the limitation of API wouldn't be exceeded.

And of course this module can be used for any purposes that need some simultaneous activity of processes.

###Features:

- If number of processes less than needed to make publishing (i.e. work only 5 processes in some period, or even when 
no process is running), Pulsar's module called PerformerImitator will imitate activity of missing processes and Pulsar 
will work as it should, without any long stops. 

- If error occur Pulsar can decrease number of subscribers or slow down - make usleep() for certain growing period 
(the default value can be changed during 
initialization of the daemon in PublisherPulsarDto object), to remove the error messages at incoming resulting Dto from 
processes. And when error is
 removed Pulsar start gradually return to normal state.
 
 It very useful when only part of processes that work with API is connected to Pulsar, and so it works flexibly, 
 adapting to the situation.

- If incoming error shows that Pulsar have to be stopped definitely for some period, it detect such signal and make usleep() for the 
specified period

##Schema

On the schema described structure and meaning of commands between elements of the module

![PublisherPulsar schema](https://github.com/jamset/publisher-pulsar/raw/master/images/publisher-pulsar-schema.jpg)


##Example

###Daemon settings

Example for Laravel could look like this one:

```php
    /**
     * Execute the console command.
     *
     * @return null
     */
    public function fire()
    {
        $pulsar = new \React\PublisherPulsar\Pulsar();
        
        $publisherPulsarDto = new \React\PublisherPulsar\Inventory\PublisherPulsarDto();

        $publisherPulsarDto->setPulsationIterationPeriod(1);
        $publisherPulsarDto->setSubscribersPerIteration(10);
        $publisherPulsarDto->setModuleName('react:pulsar-ga');
        $publisherPulsarDto->setReplyStackCommandName('php artisan react:pulsar-reply-stack');
        $publisherPulsarDto->setPerformerContainerActionMaxExecutionTime(7);

        $publisherPulsarDto->setMaxWaitReplyStackResult(7);

        $pulsarSocketsParams = new \React\PublisherPulsar\Inventory\PulsarSocketsParamsDto();

        $pulsarSocketsParams->setReplyToReplyStackSocketAddress('tcp://127.0.0.1:6261');
        $pulsarSocketsParams->setPushToReplyStackSocketAddress('tcp://127.0.0.1:6262');
        $pulsarSocketsParams->setPublishSocketAddress('tcp://127.0.0.1:6263');
        $pulsarSocketsParams->setPullSocketAddress('tcp://127.0.0.1:6264');
        $pulsarSocketsParams->setReplyStackSocketAddress('tcp://127.0.0.1:6265');

        $publisherPulsarDto->setPulsarSocketsParams($pulsarSocketsParams);

        $pulsar->setPublisherPulsarDto($publisherPulsarDto);

        $pulsar->manage();

        return null;
    }

```

And subsidiary ReplyStack daemon command's class have to contain

```php

    /**
     * Execute the console command.
     *
     * @return null
     */
    public function fire()
    {
        $replyStack = new  \React\PublisherPulsar\ReplyStack();
        $replyStack->startCommunication();

        return null;
    }

```

(and both of them have to be named in App\Console\Kernel file)

###Including in process

In process (in service) just above request to API (or other needed action) you should call:

```php

$this->zmqPerformer->connectToPulsarAndWaitPermissionToAct();
```

and when action will be done depends on its result you have to call method that push info to Pulsar with information 
about status of execution: does it have error that assume to slow down or pause Pulsar work or not.

For example:

```php

if (strpos($e->getMessage(), GaErrorResponsesConstants::USER_RATE_LIMIT_EXCEEDED) !== false) {

    $actionResultWithError = new ActionResultingPushDto();

    $actionResultWithError->setActionCompleteCorrectly(false);
    $actionResultWithError->setSlowDown(true);

    $actionResultWithError->setErrorMessage($e->getMessage());
    $actionResultWithError->setErrorReason(GaErrorResponsesConstants::USER_RATE_LIMIT_EXCEEDED);

    $this->zmqPerformer->pushActionResultInfo($actionResultWithError);

} elseif (strpos($e->getMessage(), GaErrorResponsesConstants::DAILY_LIMIT_EXCEEDED) !== false) {

    $actionResultWithError = new ActionResultingPushDto();

    $actionResultWithError->setActionCompleteCorrectly(false);

    $sleepForPeriod = new ErrorSleepForPeriod();
    $sleepForPeriod->setSleepPeriod((60 * 60 * 1000000));
    $actionResultWithError->setSleepForPeriod($sleepForPeriod);

    $actionResultWithError->setErrorMessage($e->getMessage());
    $actionResultWithError->setErrorReason(GaErrorResponsesConstants::DAILY_LIMIT_EXCEEDED);

    $this->zmqPerformer->pushActionResultInfo($actionResultWithError);

} else {

    $this->zmqPerformer->pushActionResultInfoWithoutPulsarCorrectionBehavior();

}


```
