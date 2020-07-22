<?php

namespace Drupal\wmsubscription_mailchimp;

use DrewM\MailChimp\MailChimp;
use Drupal\Core\Url;
use Drupal\wmsubscription_mailchimp\Entity\AudienceInterface;

/**
 * @see https://mailchimp.com/developer/guides/about-webhooks/
 */
class Webhook
{
    /** @var MailChimp */
    protected $mailchimp;

    public function __construct(
        Mailchimp $mailchimp
    ) {
        $this->mailchimp = $mailchimp;
    }

    public function ensureInstalled(AudienceInterface $audience, ?array $events = null, ?array $sources = null): void
    {
        if ($this->get($audience)) {
            return;
        }

        $this->install($audience, $events, $sources);
    }

    protected function install(AudienceInterface $audience, ?array $events = null, ?array $sources = null): void
    {
        $this->mailchimp->post(
            "/lists/{$audience->getId()}/webhooks",
            [
                'events' => $events ?? [
                    'unsubscribe' => true,
                    'profile' => true,
                ],
                'sources' => $sources ?? [
                    'user' => true,
                ],
                'url' => $this->getWebhookUrl(),
            ]
        );
    }

    protected function get(AudienceInterface $audience): ?array
    {
        $url = $this->getWebhookUrl();
        $data = $this->mailchimp->get(
            "/lists/{$audience->getId()}/webhooks"
        );

        foreach ($data['webhooks'] as $webhook) {
            if ($webhook['url'] === $url) {
                return $webhook;
            }
        }

        return null;
    }

    protected function getWebhookUrl(): string
    {
        if (isset($_ENV['MAILCHIMP_WEBHOOK'])) {
            return $_ENV['MAILCHIMP_WEBHOOK'];
        }

        return Url::fromRoute('wmsubscription_mailchimp.webhook')
            ->setAbsolute()
            ->toString();
    }
}
