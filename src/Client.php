<?php

declare(strict_types=1);

namespace Viciform;

use Viciform\Exceptions\ViciformException;

class Client
{
    /** @var Config */
    private $config;

    /**
     * @param Config|array $config
     */
    public function __construct($config)
    {
        $this->config = $config instanceof Config ? $config : new Config($config);
    }

    /**
     * @return Config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Add a lead to Vicidial.
     *
     * @param Lead|array $lead
     * @return Response
     *
     * @throws ViciformException
     */
    public function addLead($lead)
    {
        $lead = $lead instanceof Lead ? $lead : Lead::fromArray($lead);

        $params = array_merge(
            $this->config->authParams(),
            $lead->toApiParams($this->config)
        );

        return $this->request($params);
    }

    /**
     * Update an existing lead (Vicidial update_lead).
     *
     * @param array $data
     * @return Response
     *
     * @throws ViciformException
     */
    public function updateLead(array $data)
    {
        $params = array_merge(
            $this->config->authParams(),
            ['function' => 'update_lead'],
            $this->stringify($data)
        );

        return $this->request($params);
    }

    /**
     * Generic Non-Agent API call.
     *
     * @param string $function
     * @param array $params
     * @return Response
     *
     * @throws ViciformException
     */
    public function call($function, array $params = [])
    {
        $payload = array_merge(
            $this->config->authParams(),
            ['function' => (string) $function],
            $this->stringify($params)
        );

        return $this->request($payload);
    }

    /**
     * @param array $params
     * @return Response
     *
     * @throws ViciformException
     */
    protected function request(array $params)
    {
        $this->config->assertReady();

        $url = $this->config->baseUrl() . '?' . http_build_query($params);

        $ch = curl_init($url);

        if ($ch === false) {
            throw ViciformException::curlError('Unable to initialize cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config->timeout(),
            CURLOPT_CONNECTTIMEOUT => min(10, $this->config->timeout()),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => $this->config->verifySsl(),
            CURLOPT_SSL_VERIFYHOST => $this->config->verifySsl() ? 2 : 0,
            CURLOPT_HTTPGET => true,
            CURLOPT_USERAGENT => 'Viciform/1.0 (+https://github.com/imranniaz-st/viciform)',
        ]);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw ViciformException::curlError($error !== '' ? $error : 'errno ' . $errno);
        }

        if ($body === false) {
            throw ViciformException::curlError('Empty cURL body');
        }

        if ($status >= 400) {
            throw ViciformException::httpError($status, (string) $body);
        }

        return new Response((string) $body);
    }

    /**
     * @param array $data
     * @return array
     */
    private function stringify(array $data)
    {
        $out = [];

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_scalar($value)) {
                $out[(string) $key] = (string) $value;
                continue;
            }

            $encoded = json_encode($value);
            $out[(string) $key] = $encoded === false ? '' : $encoded;
        }

        return $out;
    }
}
