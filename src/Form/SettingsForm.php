<?php

namespace Drupal\wmsubscription_mailchimp\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingsForm implements FormInterface, ContainerInjectionInterface
{
    use StringTranslationTrait;

    /** @var ConfigFactoryInterface */
    protected $configFactory;
    /** @var MessengerInterface */
    protected $messenger;

    public static function create(ContainerInterface $container)
    {
        $instance = new static;
        $instance->configFactory = $container->get('config.factory');
        $instance->messenger = $container->get('messenger');

        return $instance;
    }

    public function getFormId()
    {
        return 'wmsubscription_mailchimp_settings';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->configFactory->get('wmsubscription_mailchimp.settings');

        $form['audience'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Audience ID', [], ['context' => 'Mailchimp']),
            '#description' => $this->t(
                'The Mailchimp audience to subscribe visitors to. For directions on how 
                you can find your audience ID, please refer to the <a href="@websiteUrl" target="_blank" 
                rel="noopener noreferer">Mailchimp website</a>.',
                ['@websiteUrl' => 'https://mailchimp.com/help/find-audience-id'],
            ),
            '#default_value' => $config->get('audience'),
            '#required' => 'true',
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $config = $this->configFactory->getEditable('wmsubscription_mailchimp.settings');
        $config->set('audience', $form_state->getValue('audience'));
        $config->save();

        $this->messenger->addStatus('Successfully updated settings.');
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }
}
