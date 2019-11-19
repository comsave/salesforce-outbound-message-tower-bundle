<?php

namespace Comsave\SalesforceOutboundMessageTowerBundle\Command;

use Comsave\SalesforceOutboundMessageTowerBundle\Services\OutboundMessageTower;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OutboundMessageTowerListenerCommand extends Command
{
    /** @var OutboundMessageTower */
    private $outboundMessageTower;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $name = null, OutboundMessageTower $outboundMessageTower)
    {
        parent::__construct($name);

        $this->outboundMessageTower = $outboundMessageTower;
    }

    protected function configure()
    {
        $this
            ->setName('salesforce:outbound-message:tower-listener')
            ->setDescription('Continually listens for OutboundMessage tower broadcasts to process.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Listening for Outbound Message Tower broadcasts...');

        while (true) {
            $notificationId = $this->processBroadcastedOutboundMessages();

            if ($notificationId) {
                $output->writeln(sprintf('Processed notification `%s`', $notificationId));
            }

            sleep(2);
        }
    }

    public function processBroadcastedOutboundMessages(): ?string
    {
        $outboundMessage = $this->outboundMessageTower->fetchCurrentBroadcast();

        if (!$outboundMessage) {
            return null;
        }

        $this->outboundMessageTower->rebroadcastLocally($outboundMessage);

        return $this->outboundMessageTower->markBroadcastProcessed($outboundMessage);
    }
}