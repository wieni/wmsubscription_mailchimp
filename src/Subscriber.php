<?php

namespace Drupal\wmsubscription_mailchimp;

use Drupal\wmsubscription\PayloadBase;
use Drupal\wmsubscription_mailchimp\Common\ContactStatus;

class Subscriber extends PayloadBase
{
    /** @var string|null */
    protected $langcode;
    /** @var array */
    protected $mergeFields;
    /** @var array */
    protected $interests;
    /** @var array */
    protected $marketingPermissions;
    /** @var string|null */
    protected $contactStatus;
    /** @var string|null */
    protected $originalEmail;
    /** @var array */
    protected $tags;

    public function __construct(
        string $email,
        ?string $langcode = null,
        array $mergeFields = [],
        array $interests = [],
        array $marketingPermissions = [],
        ?string $contactStatus = null,
        ?string $originalEmail = null,
        array $tags = []
    ){
        parent::__construct($email);
        $this->langcode = $langcode;
        $this->mergeFields = $mergeFields;
        $this->interests = $interests;
        $this->marketingPermissions = $marketingPermissions;
        $this->contactStatus = $contactStatus;
        $this->originalEmail = $originalEmail;
        $this->tags = $tags;
    }

    public function getLangcode(): ?string
    {
        return $this->langcode;
    }

    public function setLangcode(?string $value): self
    {
        $this->langcode = $value;
        return $this;
    }

    public function getMergeFields(): array
    {
        return $this->mergeFields;
    }

    public function setMergeFields(array $value): self
    {
        $this->mergeFields = $value;
        return $this;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $value): self
    {
        $this->interests = $value;
        return $this;
    }

    public function addInterest(string $id): self
    {
        $this->interests[$id] = true;
        return $this;
    }

    public function removeInterest(string $id): self
    {
        $this->interests[$id] = false;
        return $this;
    }

    public function getMarketingPermissionById(string $id): ?array
    {
        $results = array_filter(
            $this->getMarketingPermissions(),
            static function (array $permission) use ($id) {
                return $permission['marketing_permission_id'] === $id;
            }
        );

        return reset($results) ?: null;
    }

    public function getMarketingPermissions(): array
    {
        return $this->marketingPermissions;
    }

    public function setMarketingPermissions(array $value): self
    {
        $this->marketingPermissions = $value;
        return $this;
    }

    public function getContactStatus(): string
    {
        return $this->contactStatus ?? ContactStatus::SUBSCRIBED;
    }

    public function setContactStatus(?string $value): self
    {
        $this->contactStatus = $value;
        return $this;
    }

    public function getOriginalEmail(): ?string
    {
        return $this->originalEmail;
    }

    public function setOriginalEmail(?string $originalEmail): self
    {
        $this->originalEmail = $originalEmail;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $value): self
    {
        $this->tags = $value;
        return $this;
    }
}
