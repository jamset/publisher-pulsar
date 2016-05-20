# Publisher Pulsar
ReactPHP and ZMQ based module allowing to coordinate set of independent processes. I.e. to not exceed API RPS limits 
(i.e. Google Analytics 10 RPS for November 2015)

##Install

`composer require jamset/publisher-pulsar`

##Description

The idea that PublisherPulsar is the daemon, that allow to make some action simultaneously (i.e. connection to API) 
for certain number of processes ('subscribers'). 

I.e. limit for GA is 10 request per second, and so you can include in code of such processes connection to Pulsar, 
set in Pulsar settings limit for 10 subscribers per iteration, set iteration size 1 second, and start daemon and processes. 

All processes beginning after it will be connect to special stack (ReplyStack, based on ZMQ and ReactPHP, part 
of PublisherPulsar), which will notify Pulsar that subscribers are ready to make an action when all needed number of processes
will be active (executed and paused on point that need Pulsar permission to execute further).

After it Pulsar send allowing message to processes (subscribers), that allow them to continue their execution, i.e. 
make a request to API. And so the limitation of API wouldn't be exceeded.

And of course this module can be used for any purposes that need some simultaneous activity of processes.

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

        $publisherPulsarDto->setLogger(\Log::getMonolog());
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