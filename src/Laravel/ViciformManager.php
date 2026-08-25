<?php

declare(strict_types=1);

namespace Viciform\Laravel;

use Viciform\Client;
use Viciform\Lead;
use Viciform\Response;
use Viciform\Viciform;
use Viciform\Webform\ScriptPayload;

/**
 * Laravel-bound API: outbound Non-Agent client + inbound webform parser.
 */
class ViciformManager
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @return \Viciform\Config
     */
    public function config()
    {
        return $this->client->config();
    }

    /**
     * @param Lead|array $lead
     * @return Response
     */
    public function addLead($lead)
    {
        return $this->client->addLead($lead);
    }

    /**
     * @param array $data
     * @return Response
     */
    public function updateLead(array $data)
    {
        return $this->client->updateLead($data);
    }

    /**
     * @param string $function
     * @param array $params
     * @return Response
     */
    public function call($function, array $params = [])
    {
        return $this->client->call($function, $params);
    }

    /**
     * Parse Vicidial Avatar-style webform / campaign script query params.
     *
     * @param mixed $request
     * @return ScriptPayload
     */
    public function webform($request = null)
    {
        return Viciform::webform($request);
    }
}
