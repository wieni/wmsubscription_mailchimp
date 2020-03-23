<?php

namespace Drupal\wmsubscription_mailchimp\Common;

/**
 * @see https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/#contact-status
 */
class ContactStatus
{
    /**
     * The contact is subscribed to the audience and can receive campaigns.
     * Note that campaigns can only be sent to ‘subscribed’ contacts.
     */
    public const SUBSCRIBED = 'subscribed';

    /**
     * Request the contact to be added to the audience with double-opt-in.
     */
    public const PENDING = 'pending';

    /**
     * Use unsubscribed or cleaned to archive unused contacts. When you add contacts
     * to the unsubscribed or cleaned status groups, you’ll have a record of
     * the contact, but future messages will not be sent.
     */
    public const UNSUBSCRIBED = 'unsubscribed';
    public const CLEANED = 'cleaned';

    public static function getLabels(): array
    {
        return [
            self::SUBSCRIBED => t('Subscribed'),
            self::PENDING => t('Pending'),
            self::UNSUBSCRIBED => t('Unsubscribed'),
            self::CLEANED => t('Cleaned'),
        ];
    }
}
