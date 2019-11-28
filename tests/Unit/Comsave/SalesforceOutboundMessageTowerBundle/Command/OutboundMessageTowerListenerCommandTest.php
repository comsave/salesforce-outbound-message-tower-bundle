<?php

namespace Tests\Unit\SalesforceOutboundMessageTowerBundle\OutboundMessageTowerListenerCommandTest;

use Comsave\SalesforceOutboundMessageTowerBundle\Command\OutboundMessageTowerListenerCommand;
use Comsave\SalesforceOutboundMessageTowerBundle\Services\OutboundMessageTower;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Comsave\SalesforceOutboundMessageTowerBundle\Command\OutboundMessageTowerListenerCommand
 */
class OutboundMessageTowerListenerCommandTest extends TestCase
{
    /** @var OutboundMessageTowerListenerCommand */
    private $outboundMessageTowerListenerCommand;

    /** @var OutboundMessageTower|MockObject */
    private $outboundMessageTowerMock;

    public function setUp(): void
    {
        $nameStub = 'some:name';
        $this->outboundMessageTowerMock = $this->createMock(OutboundMessageTower::class);
        $this->outboundMessageTowerListenerCommand = new OutboundMessageTowerListenerCommand($nameStub, $this->outboundMessageTowerMock, null);
    }

    /**
     * @covers ::processBroadcastedOutboundMessages()
     */
    public function testFetchesCurrentBroadcast()
    {
        $channelNameStub = 'someChannelName';

        $this->outboundMessageTowerMock->expects($this->once())
            ->with($channelNameStub)
            ->method('fetchCurrentBroadcast');

        $this->outboundMessageTowerListenerCommand->processBroadcastedOutboundMessages($channelNameStub);
    }
}