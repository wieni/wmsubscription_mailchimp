<?php

namespace Drupal\wmsubscription_mailchimp\Plugin\SubscriptionTool;

use DrewM\MailChimp\MailChimp as Client;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wmsubscription\Annotation\SubscriptionTool;
use Drupal\wmsubscription\Exception\SubscriptionException;
use Drupal\wmsubscription\Exception\ValidationFailedException;
use Drupal\wmsubscription\ListInterface;
use Drupal\wmsubscription\PayloadInterface;
use Drupal\wmsubscription\SubscriptionToolBase;
use Drupal\wmsubscription_mailchimp\Entity\Audience;
use Drupal\wmsubscription_mailchimp\Subscriber;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @SubscriptionTool(
 *     id = "mailchimp",
 *     label = "Mailchimp",
 * )
 */
class Mailchimp extends SubscriptionToolBase implements ContainerFactoryPluginInterface
{
    /** @var Client */
    protected $client;

    public static function create(
        ContainerInterface $container,
        array $configuration,
        $pluginId,
        $pluginDefinition
    ) {
        $instance = new static($configuration, $pluginId, $pluginDefinition);
        $instance->client = $container->get('wmsubscription_mailchimp.client');

        return $instance;
    }

    public function addSubscriber(ListInterface $list, PayloadInterface $payload, string $operation = self::OPERATION_CREATE_OR_UPDATE): void
    {
        /** @var Audience $list */
        /** @var Subscriber $payload */
        $this->validateArguments($list, $payload);

        $hash = md5(strtolower($payload->getOriginalEmail() ?? $payload->getEmail()));
        $endpoint = sprintf('lists/%s/members/%s', $list->getId(), $hash);
        $data = [
            'email_address' => $payload->getEmail(),
            'status' => $payload->getContactStatus(),
            'merge_fields' => $payload->getMergeFields(),
            'marketing_permissions' => $payload->getMarketingPermissions(),
        ];

        if ($langcode = $payload->getLangcode()) {
            $data['language'] = $langcode;
        }

        if ($tags = $payload->getTags()) {
            $data['tags'] = $tags;
        }

        if ($interests = $payload->getInterests()) {
            $data['interests'] = $interests;
        }

        if ($operation === self::OPERATION_CREATE_OR_UPDATE) {
            $verb = 'put';
        } elseif ($operation === self::OPERATION_CREATE) {
            $verb = 'post';
        } elseif ($operation === self::OPERATION_UPDATE) {
            $verb = 'patch';
        }

        $this->client->{$verb}($endpoint, $data);

        if (!$this->client->success()) {
            $response = $this->client->getLastResponse();
            $body = \GuzzleHttp\json_decode($response['body'], true);

            if ($body['status'] === 400 && $body['title'] === 'Invalid Resource') {
                throw new ValidationFailedException($body['detail'], $body['errors'] ?? []);
            }

            $errorsToIgnore = [
                'Forgotten Email Not Subscribed',
                'Member In Compliance State',
            ];

            if ($body['status'] === 400 && in_array($body['title'], $errorsToIgnore, true)) {
                // Ignore this
                return;
            }

            throw new SubscriptionException($this->client->getLastError());
        }
    }

    public function getSubscriber(ListInterface $list, PayloadInterface $payload): ?PayloadInterface
    {
        /** @var Audience $list */
        /** @var Subscriber $payload */
        $this->validateArguments($list, $payload);

        $hash = md5(strtolower($payload->getEmail()));
        $endpoint = sprintf('lists/%s/members/%s', $list->getId(), $hash);
        $data = $this->client->get($endpoint);

        if ($this->client->success()) {
            return new Subscriber(
                $data['email_address'],
                $data['language'],
                $data['merge_fields'],
                $data['interests'],
                $data['marketing_permissions'],
                $data['status']
            );
        }

        return null;
    }

    public function isSubscribed(ListInterface $list, PayloadInterface $payload): bool
    {
        $status = $this->getSubscriberStatus($list, $payload);

        return $status !== null
            && in_array($status, ['subscribed', 'pending']);
    }

    public function isUpdatable(ListInterface $list, PayloadInterface $payload): bool
    {
        return $this->getSubscriberStatus($list, $payload) !== null;
    }

    protected function getSubscriberStatus(ListInterface $list, PayloadInterface $payload): ?string
    {
        /** @var Audience $list */
        /** @var Subscriber $payload */
        $this->validateArguments($list, $payload);

        $hash = md5(strtolower($payload->getEmail()));
        $endpoint = sprintf('lists/%s/members/%s', $list->getId(), $hash);
        $data = $this->client->get($endpoint, ['fields' => 'status']);

        if ($this->client->success()) {
            return $data['status'];
        }

        return null;
    }

    protected function validateArguments(ListInterface $list, PayloadInterface $payload)
    {
        if (!$list instanceof Audience) {
            throw new RuntimeException(
                sprintf('%s is not an instance of Drupal\wmsubscription_mailchimp\Mailchimp\Audience!', get_class($list))
            );
        }

        if (!$payload instanceof Subscriber) {
            throw new RuntimeException(
                sprintf('%s is not an instance of Drupal\wmsubscription_mailchimp\Mailchimp\Subscriber!', get_class($payload))
            );
        }
    }
}
