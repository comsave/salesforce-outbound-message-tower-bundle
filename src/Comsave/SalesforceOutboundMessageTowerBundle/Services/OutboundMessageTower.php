<?php

namespace Comsave\SalesforceOutboundMessageTowerBundle\Services;

use GuzzleHttp\Client;
use SimpleXMLElement;

class OutboundMessageTower
{
    private $towerBaseUrl = 'http://localhost:8000';

    /** @var Client */
    private $httpClient;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->httpClient = new Client([
            // options
        ]);
    }

    public function rebroadcastLocally(string $outboundMessage): void
    {
        $this->httpClient->post('http://dev.webservice.comsave.com/salesforce/sync', [
            'body' => $outboundMessage,
        ]);
    }

    public function fetchCurrentBroadcast(): string
    {
        return $this->httpClient->get(sprintf('%s/broadcast', $this->towerBaseUrl))->getBody()->getContents();
    }

    public function markBroadcastProcessed(string $outboundMessage): string
    {
        $notificationId = $this->getSalesforceNotificationId($outboundMessage);

        $this->httpClient->get(sprintf('%s/broadcast/processed/%s', $this->towerBaseUrl, $notificationId))->getBody()->getContents();

        return $notificationId;
    }

    private function getSalesforceNotificationId(string $requestXml): ?string
    {
        $requestXml = str_ireplace(['soapenv:', 'soap:', 'sf:'], '', $requestXml);
        $simpleXml = new SimpleXMLElement($requestXml);

        return @$simpleXml->Body->notifications->ActionId ?? null;
    }
}