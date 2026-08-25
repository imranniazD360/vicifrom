<?php

declare(strict_types=1);

namespace Viciform;

final class Response
{
    /** @var string */
    private $raw;

    /** @var bool */
    private $success;

    /** @var bool */
    private $duplicate;

    /** @var string|null */
    private $leadId;

    /** @var string|null */
    private $message;

    /**
     * @param string $raw
     */
    public function __construct($raw)
    {
        $this->raw = trim((string) $raw);
        $this->success = false;
        $this->duplicate = false;
        $this->leadId = null;
        $this->message = null;

        $this->parse();
    }

    /**
     * @return void
     */
    private function parse()
    {
        if ($this->raw === '') {
            $this->message = 'Empty response from Vicidial';

            return;
        }

        $upper = strtoupper($this->raw);

        if (Str::startsWith($upper, 'SUCCESS')) {
            $this->success = true;
            $this->message = $this->raw;
            $this->leadId = $this->extractLeadId($this->raw);

            return;
        }

        if (Str::contains($upper, 'DUPLICATE')) {
            $this->duplicate = true;
            $this->message = $this->raw;
            $this->leadId = $this->extractLeadId($this->raw);

            return;
        }

        $this->message = $this->raw;
    }

    /**
     * @param string $raw
     * @return string|null
     */
    private function extractLeadId($raw)
    {
        $parts = explode('|', $raw);
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $part = trim($parts[$i]);
            if ($part !== '' && ctype_digit($part)) {
                return $part;
            }
        }

        if (preg_match('/lead[_\s-]?id[:\s#-]*(\d+)/i', $raw, $matches)) {
            return $matches[1];
        }

        if (preg_match('/\b(\d{4,})\b/', $raw, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return string
     */
    public function raw()
    {
        return $this->raw;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return bool
     */
    public function isDuplicate()
    {
        return $this->duplicate;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return !$this->success && !$this->duplicate;
    }

    /**
     * @return string|null
     */
    public function leadId()
    {
        return $this->leadId;
    }

    /**
     * @return string|null
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'success' => $this->success,
            'duplicate' => $this->duplicate,
            'lead_id' => $this->leadId,
            'message' => $this->message,
            'raw' => $this->raw,
        ];
    }
}
