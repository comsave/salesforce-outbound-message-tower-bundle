<?php

namespace Comsave\SalesforceOutboundMessageTowerBundle\Command;

use Comsave\SalesforceOutboundMessageTowerBundle\Exception\OutboundMessageTowerException;
use Comsave\SalesforceOutboundMessageTowerBundle\Services\OutboundMessageTower;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setDescription('Continually listens for OutboundMessage tower broadcasts to process.')
            ->addArgument('channelName', InputArgument::REQUIRED,
                'Name of the channel you\'re broadcasting to in the OutboundMessageTower.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channelName = $input->getArgument('channelName');

        if (!preg_match("/^[a-z0-9]$/i", $channelName)) {
            throw new OutboundMessageTowerException('Invalid channel name. Only numbers and letters are allowed');
        }

        $output->writeln('Listening for Outbound Message Tower broadcasts...');

        while (true) {
            $notificationId = $this->processBroadcastedOutboundMessages($channelName);

            if ($notificationId) {
                $output->writeln(sprintf('Processed notification `%s`', $notificationId));
            }

            sleep(2);
        }
    }

    public function processBroadcastedOutboundMessages(string $channelName): ?string
    {
        $outboundMessage = $this->outboundMessageTower->fetchCurrentBroadcast($channelName);

        if (!$outboundMessage) {
            return null;
        }

        $this->outboundMessageTower->rebroadcastLocally($outboundMessage);

        return $this->outboundMessageTower->markBroadcastProcessed($channelName, $outboundMessage);
    }
}