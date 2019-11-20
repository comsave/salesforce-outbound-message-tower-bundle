<?php

namespace Comsave\SalesforceOutboundMessageTowerBundle\Services;

use Comsave\SalesforceOutboundMessageTowerBundle\Exception\OutboundMessageTowerException;
use GuzzleHttp\Client;

class OutboundMessageTower
{
    /** @var string */
    private $towerBaseUrl;

    /** @var Client */
    private $httpClient;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $towerBaseUrl)
    {
        $this->towerBaseUrl = $towerBaseUrl;

        if (!$this->towerBaseUrl) {
            throw new OutboundMessageTowerException('Environment variable `SALESFORCE_OUTBOUND_MESSAGE_TOWER_URL` not defined.');
        }

        $this->httpClient = new Client();
    }

    public function rebroadcastLocally(string $outboundMessage): void
    {
        $this->httpClient->post('/salesforce/sync', [
            'body' => $outboundMessage,
        ]);
    }

    public function fetchCurrentBroadcast(string $channelName): string
    {
        return $this->httpClient->get(sprintf('/%s/%s/broadcast', $this->towerBaseUrl, $channelName))->getBody()->getContents();
    }

    public function markBroadcastProcessed(string $channelName, string $outboundMessage): string
    {
        $notificationId = $this->getSalesforceNotificationId($outboundMessage);

        $this->httpClient->get(sprintf('/%s/%s/broadcast/processed/%s', $this->towerBaseUrl, $channelName, $notificationId))->getBody()->getContents();

        return $notificationId;
    }

    private function getSalesforceNotificationId(string $requestXml): ?string
    {
        $requestXml = str_ireplace(['soapenv:', 'soap:', 'sf:'], '', $requestXml);
        $simpleXml = new \SimpleXMLElement($requestXml);

        return @$simpleXml->Body->notifications->ActionId ?? null;
    }
}