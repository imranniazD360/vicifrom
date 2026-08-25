<?php

declare(strict_types=1);

namespace Viciform;

use Viciform\Exceptions\ViciformException;

final class Config
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $user;

    /** @var string */
    private $pass;

    /** @var string */
    private $source;

    /** @var string */
    private $listId;

    /** @var string */
    private $phoneCode;

    /** @var int */
    private $timeout;

    /** @var bool */
    private $verifySsl;

    /** @var string|null */
    private $duplicateCheck;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->baseUrl = rtrim((string) (isset($options['base_url']) ? $options['base_url'] : ''), '/');
        $this->user = (string) (isset($options['user']) ? $options['user'] : '');
        $this->pass = (string) (isset($options['pass']) ? $options['pass'] : '');
        $this->source = (string) (isset($options['source']) ? $options['source'] : 'webform');
        $this->listId = (string) (isset($options['list_id']) ? $options['list_id'] : '999');
        $this->phoneCode = (string) (isset($options['phone_code']) ? $options['phone_code'] : '1');
        $this->timeout = (int) (isset($options['timeout']) ? $options['timeout'] : 15);
        $this->verifySsl = array_key_exists('verify_ssl', $options)
            ? (bool) $options['verify_ssl']
            : true;
        $this->duplicateCheck = isset($options['duplicate_check']) && $options['duplicate_check'] !== ''
            ? (string) $options['duplicate_check']
            : null;

        if ($this->baseUrl === '') {
            throw ViciformException::missingConfig('base_url');
        }

        if (strlen($this->source) > 20) {
            $this->source = substr($this->source, 0, 20);
        }
    }

    /**
     * Ensure API credentials are present before calling Vicidial.
     *
     * @return void
     */
    public function assertReady()
    {
        if ($this->user === '') {
            throw ViciformException::missingConfig('user (set VICIFORM_USER in .env)');
        }

        if ($this->pass === '') {
            throw ViciformException::missingConfig('pass (set VICIFORM_PASS in .env)');
        }
    }

    /**
     * @return string
     */
    public function baseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function pass()
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function listId()
    {
        return $this->listId;
    }

    /**
     * @return string
     */
    public function phoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * @return int
     */
    public function timeout()
    {
        return $this->timeout;
    }

    /**
     * @return bool
     */
    public function verifySsl()
    {
        return $this->verifySsl;
    }

    /**
     * @return string|null
     */
    public function duplicateCheck()
    {
        return $this->duplicateCheck;
    }

    /**
     * @return array
     */
    public function authParams()
    {
        return [
            'source' => $this->source,
            'user' => $this->user,
            'pass' => $this->pass,
        ];
    }
}
