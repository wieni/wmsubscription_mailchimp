<?php

namespace Drupal\wmsubscription_mailchimp\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\wmsubscription\SubscriptionManagerInterface;
use Drupal\wmsubscription_mailchimp\Entity\Audience;
use Drupal\wmsubscription_mailchimp\Subscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController implements ContainerInjectionInterface
{
    /** @var LoggerChannelInterface */
    protected $logger;
    /** @var SubscriptionManagerInterface */
    protected $manager;

    public static function create(ContainerInterface $container)
    {
        $instance = new static;
        $instance->logger = $container->get('logger.channel.wmsubscription_mailchimp');
        $instance->manager = $container->get('wmsubscription.manager');

        return $instance;
    }

    public function show(Request $request)
    {
        if ($request->headers->get('user-agent') === 'MailChimp.com WebHook Validator') {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        if (
            !$request->request->has('type')
            || !$request->request->has('data')
        ) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $type = $request->request->get('type');
        $data = $request->request->get('data');

        $this->logger->debug("Incoming Mailchimp webhook of type %type: @data", [
            '%type' => $type,
            '@data' => json_encode($data),
        ]);

        $list = Audience::create(['id' => $data['list_id']]);
        $payload = new Subscriber($data['email']);
        $subscriber = $this->manager->getSubscriber($list, $payload);

        if ($type === 'unsubscribe') {
            $this->manager->onUnsubscribe($list, $subscriber);
        }

        if ($type === 'profile' || $type === 'upemail') {
            $this->manager->onSubscriberUpdate($list, $subscriber);
        }

        return new Response();
    }
}
