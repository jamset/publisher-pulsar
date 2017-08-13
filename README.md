# Publisher Pulsar
ReactPHP and ZMQ based module allowing to provide simultaneous, coordinated activity of independent processes and so manage their activity.

In particular case could be considered as the implementaion of dynamic [tocken bucket algorithm](https://en.wikipedia.org/wiki/Token_bucket).

I.e. to not exceed API RPS (QPS) limits in dynamic mode of activity. 

Note: for static implementation may take a look at [this one](https://github.com/bandwidth-throttle/token-bucket).

## Install

`composer require jamset/publisher-pulsar`

Additional needed libs installation guide could be found [here](https://github.com/jamset/gearman-conveyor/blob/master/docs/environment.md). 
Section "Install PECL" and "Optional".

## Description

The idea that PublisherPulsar is the daemon, which allow to make some action simultaneously for certain number of processes ('subscribers'). 

I.e. limit for Google Analytics is 10 requests (queries) per second, and so you can include in code of such processes ('services', that contain Performer class with relevant Pulsar integrated commands) connection to Pulsar, set in Pulsar settings limit for 10 subscribers per iteration, set iteration size 1 second, and start daemon and processes. 

All processes beginning after it will be connected to special stack (ReplyStack), which will notify Pulsar that subscribers are ready to make an action when all needed number of processes will be active.

After it Pulsar send allowing message to processes (subscribers), that allows them to continue their execution, i.e. 
make a request to API. And so the limitation of API wouldn't be exceeded.

And of course this module can be used for any purposes that need some simultaneous activity of processes.

### Features:

- If number of processes less than needed to make publishing (i.e. work only 5 processes in some period, or even when 
no process is running), Pulsar's module called PerformerImitator will imitate activity of missing processes and Pulsar 
will work as it should, without any long stops. 

- If error occur in services responses Pulsar can decrease number of subscribers or slow down - make usleep() for certain growing period (the default value can be changed during initialization of the daemon in PublisherPulsarDto object), to remove the error messages at incoming resulting Dto from processes (services). And when error is removed Pulsar start gradually return to normal state.
 
 It very useful when, for example, only part of processes working with API is connected to Pulsar, and so it works flexibly, adapting to the situation.

- If incoming error shows that Pulsar have to be stopped definitely for some period, it detect such signal and make usleep() for the specified period

- Allow to send arbitrary commands to subscribers by setting class extended from PublisherToSubscribersDto in PublisherPulsarDto during initialization. And so one subscriber can contain logic of handling commands from different types of customized Pulsars.

## Schema

On the schema described structure and meaning of commands between elements of the module

![PublisherPulsar schema](https://github.com/jamset/publisher-pulsar/raw/master/images/publisher-pulsar-schema.jpg)

## Example

### Daemon settings

Example for Laravel could look like this one. 

Out of the box (among other it's default socket params for one node for Pulsar and performers):

```php
    /**
     * Execute the console command.
     *
     * @return null
     */
    public function fire()
    {
    
        //You can launch it out of the box if Pulsar and subscribers launching on one node, with default properties 
        //(no less than 1 second for iteration, 10 subscribers)
    
        $pulsar = new \React\PublisherPulsar\Pulsar();
        
        $publisherPulsarDto = new \React\PublisherPulsar\Inventory\PublisherPulsarDto();              
        $publisherPulsarDto->setModuleName('react:pulsar'); //arbitrary name        
        $publisherPulsarDto->setReplyStackCommandName('php artisan react:pulsar-reply-stack'); // address of subsidiary command, its code is presented below
        $publisherPulsarDto->initDefaultPulsarSocketsParams();
        
        $pulsar->setPublisherPulsarDto($publisherPulsarDto);
        $pulsar->manage();      
        
        return null;
    }

```

And with additional options:

```php
        
        $publisherPulsarDto->setPulsationIterationPeriod(1); // it means that Pulsar's publishing would be no less than 1 second
        $publisherPulsarDto->setSubscribersPerIteration(10);         
        $publisherPulsarDto->setPerformerContainerActionMaxExecutionTime(7); // how many seconds Pulsar will wait resulting message from Service (Performer-Subscriber) from performers
        $publisherPulsarDto->setLogger(\Log::getMonolog()); //to use your StreamHandlers. If won't set will be used Logger with putting all logging to STDOUT
        $publisherPulsarDto->setMaxWaitReplyStackResult(7); // how many seconds Pulsar will wait connection of needed number of performers/subscribers
        
        //[For purposes of advanced processes management:
        $publisherToSubscribersDto = new YourNameExtendedByPublisherToSubscribersDto(); 
        $publisherToSubscribersDto->setYourProperty(); // any properties that can influence on performer execution logic
        
        $publisherPulsarDto->setPublisherToSubscribersDto($publisherToSubscribersDto);  
        //]      

        $pulsarSocketsParams = new \React\PublisherPulsar\Inventory\PulsarSocketsParamsDto();

        //it could be any free ports
        $pulsarSocketsParams->setReplyToReplyStackSocketAddress('tcp://127.0.0.1:6271');
        $pulsarSocketsParams->setPushToReplyStackSocketAddress('tcp://127.0.0.1:6272');
        $pulsarSocketsParams->setPublishSocketAddress('tcp://127.0.0.1:6273');
        $pulsarSocketsParams->setPullSocketAddress('tcp://127.0.0.1:6274');
        $pulsarSocketsParams->setReplyStackSocketAddress('tcp://127.0.0.1:6275');

        $publisherPulsarDto->setPulsarSocketsParams($pulsarSocketsParams);

        $pulsar->setPublisherPulsarDto($publisherPulsarDto);

        $pulsar->manage();

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

Note: **very important that daemon have to be started earlier than processes-performers would.**

### Including in process

Out of the box:
 
```php
  
$performer = new \React\PublisherPulsar\Performer();
 
$performerDto = new \React\PublisherPulsar\Inventory\PerformerDto();
$performerDto->setModuleName("YourServiceNameContainingPerformer");

$performer->setPerformerDto($performerDto);
$performer->initDefaultPerformerSocketsParams();
 
$this->zmqPerformer = $performer;  
```

And with options:
 
```php 

$performerDto->setLogger(\Log::getMonolog()); 
 
$performerSocketParams = new \React\PublisherPulsar\Inventory\PerformerSocketsParamsDto();
//this addresses is the same with addresses of relevant Pulsar's properties as ZMQ-pair (Publish/Subscribe, Push/Pull, Request/Reply)
$performerSocketParams->setPublisherPulsarSocketAddress('tcp://127.0.0.1:6273');
$performerSocketParams->setPushPulsarSocketAddress('tcp://127.0.0.1:6274');
$performerSocketParams->setRequestPulsarRsSocketAddress('tcp://127.0.0.1:6275');

$performerDto->setSocketsParams($performerSocketParams);

$performer->setPerformerDto($performerDto);

$this->zmqPerformer = $performer; 
 ```
  
 and then call in appropriate place (before action that have to be coordinated):

```php

$this->zmqPerformer->connectToPulsarAndWaitPermissionToAct();
```

and when action will be done depends on its result you have to call method that push info to Pulsar with information 
about status of execution: does it have error that assume to slow down or pause Pulsar work or not.

For example:

```php

if (isUserRateLimitExceeded()) {
    $errorResult = new ActionResultingPushDto();
    $errorResult->setActionCompleteCorrectly(false);
    $errorResult->setSlowDown(true);
    $errorResult->setErrorMessage($e->getMessage());
    $errorResult->setErrorReason(GaErrorResponsesConstants::USER_RATE_LIMIT_EXCEEDED);

    $this->zmqPerformer->pushActionResultInfo($errorResult);

} elseif (isDailyLimitExceeded()) {

    $errorResult = new ActionResultingPushDto();
    $errorResult->setActionCompleteCorrectly(false);
    
    $sleepForPeriod = new ErrorSleepForPeriod();
    $sleepForPeriod->setSleepPeriod((60 * 60 * 1000000));
    
    $errorResult->setSleepForPeriod($sleepForPeriod);
    $errorResult->setErrorMessage($e->getMessage());
    $errorResult->setErrorReason(GaErrorResponsesConstants::DAILY_LIMIT_EXCEEDED);

    $this->zmqPerformer->pushActionResultInfo($errorResult);

} else {
    $this->zmqPerformer->pushActionResultInfoWithoutPulsarCorrectionBehavior();
}


```
