<?php

declare(strict_types=1);

namespace Viciform;

use Viciform\Exceptions\ViciformException;

final class Lead
{
    /** @var string */
    private $phoneNumber;

    /** @var string|null */
    private $phoneCode;

    /** @var string|null */
    private $listId;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var string|null */
    private $middleInitial;

    /** @var string|null */
    private $address1;

    /** @var string|null */
    private $address2;

    /** @var string|null */
    private $address3;

    /** @var string|null */
    private $city;

    /** @var string|null */
    private $state;

    /** @var string|null */
    private $province;

    /** @var string|null */
    private $postalCode;

    /** @var string|null */
    private $countryCode;

    /** @var string|null */
    private $gender;

    /** @var string|null */
    private $dateOfBirth;

    /** @var string|null */
    private $altPhone;

    /** @var string|null */
    private $email;

    /** @var string|null */
    private $comments;

    /** @var string|null */
    private $vendorLeadCode;

    /** @var string|null */
    private $sourceId;

    /** @var string|null */
    private $title;

    /** @var string|null */
    private $owner;

    /** @var string|null */
    private $securityPhrase;

    /** @var string|null */
    private $rank;

    /** @var string|null */
    private $campaign;

    /** @var string|null */
    private $dncCheck;

    /** @var string|null */
    private $campaignDncCheck;

    /** @var string|null */
    private $addToHopper;

    /** @var string|null */
    private $hopperPriority;

    /** @var string|null */
    private $duplicateCheck;

    /** @var string|null */
    private $customFields;

    /** @var array */
    private $extra;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $rawPhone = '';
        if (isset($data['phone_number'])) {
            $rawPhone = (string) $data['phone_number'];
        } elseif (isset($data['phone'])) {
            $rawPhone = (string) $data['phone'];
        }

        $phone = preg_replace('/\D+/', '', $rawPhone);
        if ($phone === null) {
            $phone = '';
        }

        if (strlen($phone) < 6 || strlen($phone) > 16) {
            throw ViciformException::invalidPhone($rawPhone);
        }

        $this->phoneNumber = $phone;
        $this->phoneCode = self::nullableString($data, ['phone_code']);
        $this->listId = self::nullableString($data, ['list_id']);
        $this->firstName = self::nullableString($data, ['first_name', 'firstname', 'fname']);
        $this->lastName = self::nullableString($data, ['last_name', 'lastname', 'lname']);
        $this->middleInitial = self::nullableString($data, ['middle_initial', 'middle_name']);
        $this->address1 = self::nullableString($data, ['address1', 'address']);
        $this->address2 = self::nullableString($data, ['address2']);
        $this->address3 = self::nullableString($data, ['address3']);
        $this->city = self::nullableString($data, ['city']);
        $this->state = self::nullableString($data, ['state']);
        $this->province = self::nullableString($data, ['province']);
        $this->postalCode = self::nullableString($data, ['postal_code', 'zip', 'zipcode']);
        $this->countryCode = self::nullableString($data, ['country_code', 'country']);
        $this->gender = self::nullableString($data, ['gender']);
        $this->dateOfBirth = self::nullableString($data, ['date_of_birth', 'dob']);
        $this->altPhone = self::nullableString($data, ['alt_phone', 'alt_phone_number']);
        $this->email = self::nullableString($data, ['email', 'email_address']);
        $this->comments = self::nullableString($data, ['comments', 'comment', 'notes']);
        $this->vendorLeadCode = self::nullableString($data, ['vendor_lead_code', 'vendor_id', 'external_id']);
        $this->sourceId = self::nullableString($data, ['source_id']);
        $this->title = self::nullableString($data, ['title']);
        $this->owner = self::nullableString($data, ['owner']);
        $this->securityPhrase = self::nullableString($data, ['security_phrase']);
        $this->rank = self::nullableString($data, ['rank']);
        $this->campaign = self::nullableString($data, ['campaign', 'campaign_id']);
        $this->dncCheck = self::nullableString($data, ['dnc_check']);
        $this->campaignDncCheck = self::nullableString($data, ['campaign_dnc_check']);
        $this->addToHopper = self::nullableString($data, ['add_to_hopper']);
        $this->hopperPriority = self::nullableString($data, ['hopper_priority']);
        $this->duplicateCheck = self::nullableString($data, ['duplicate_check']);
        $this->customFields = self::nullableString($data, ['custom_fields']);

        $known = [
            'phone_number', 'phone', 'phone_code', 'list_id',
            'first_name', 'firstname', 'fname', 'last_name', 'lastname', 'lname',
            'middle_initial', 'middle_name', 'address1', 'address', 'address2', 'address3',
            'city', 'state', 'province', 'postal_code', 'zip', 'zipcode',
            'country_code', 'country', 'gender', 'date_of_birth', 'dob',
            'alt_phone', 'alt_phone_number', 'email', 'email_address',
            'comments', 'comment', 'notes', 'vendor_lead_code', 'vendor_id', 'external_id',
            'source_id', 'title', 'owner', 'security_phrase', 'rank',
            'campaign', 'campaign_id', 'dnc_check', 'campaign_dnc_check',
            'add_to_hopper', 'hopper_priority', 'duplicate_check', 'custom_fields',
        ];

        $this->extra = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $known, true) && $value !== null && $value !== '') {
                $this->extra[(string) $key] = (string) $value;
            }
        }
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        return new self($data);
    }

    /**
     * Build Vicidial API query params (without auth).
     *
     * @param Config $config
     * @return array
     */
    public function toApiParams(Config $config)
    {
        $params = [
            'function' => 'add_lead',
            'phone_number' => $this->phoneNumber,
            'phone_code' => $this->phoneCode !== null ? $this->phoneCode : $config->phoneCode(),
            'list_id' => $this->listId !== null ? $this->listId : $config->listId(),
        ];

        $optional = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'middle_initial' => $this->middleInitial,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'address3' => $this->address3,
            'city' => $this->city,
            'state' => $this->state,
            'province' => $this->province,
            'postal_code' => $this->postalCode,
            'country_code' => $this->countryCode,
            'gender' => $this->gender,
            'date_of_birth' => $this->dateOfBirth,
            'alt_phone' => $this->altPhone,
            'email' => $this->email,
            'comments' => $this->comments,
            'vendor_lead_code' => $this->vendorLeadCode,
            'source_id' => $this->sourceId,
            'title' => $this->title,
            'owner' => $this->owner,
            'security_phrase' => $this->securityPhrase,
            'rank' => $this->rank,
            'campaign' => $this->campaign,
            'dnc_check' => $this->dncCheck,
            'campaign_dnc_check' => $this->campaignDncCheck,
            'add_to_hopper' => $this->addToHopper,
            'hopper_priority' => $this->hopperPriority,
            'custom_fields' => $this->customFields,
        ];

        foreach ($optional as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $duplicate = $this->duplicateCheck !== null ? $this->duplicateCheck : $config->duplicateCheck();
        if ($duplicate !== null && $duplicate !== '') {
            $params['duplicate_check'] = $duplicate;
        }

        foreach ($this->extra as $key => $value) {
            $params[$key] = $value;
        }

        return $params;
    }

    /**
     * @return string
     */
    public function phoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param array $data
     * @param array $keys
     * @return string|null
     */
    private static function nullableString(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return (string) $data[$key];
            }
        }

        return null;
    }
}
