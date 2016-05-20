# publisher-pulsar
ReactPHP based module allowing to coordinate set of independent processes. I.e. to not exceed API RPS limits 
(i.e. Google Analytics)

`composer require jamset/publisher-pulsar`

![PublisherPulsar schema](https://github.com/jamset/publisher-pulsar/raw/master/images/publisher-pulsar-schema.jpg)

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

        //TODO: and if Logger wasn't set?
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

