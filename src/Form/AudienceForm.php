<?php

namespace Drupal\wmsubscription_mailchimp\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\wmsubscription_mailchimp\Entity\Audience;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @property Audience $entity
 */
class AudienceForm extends EntityForm
{
    /** @var MessengerInterface */
    protected $messenger;

    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        $instance->messenger = $container->get('messenger');

        return $instance;
    }

    public function form(array $form, FormStateInterface $form_state)
    {
        $form['label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#default_value' => $this->entity->get('label'),
            '#required' => true,
        ];

        $form['id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Audience ID', [], ['context' => 'Mailchimp']),
            '#description' => $this->t(
                'The Mailchimp audience to subscribe visitors to. For directions on how 
                you can find your audience ID, please refer to the <a href="@websiteUrl" target="_blank" 
                rel="noopener noreferer">Mailchimp website</a>.',
                ['@websiteUrl' => 'https://mailchimp.com/help/find-audience-id']
            ),
            '#default_value' => $this->entity->id(),
            '#disabled' => !$this->entity->isNew(),
            '#required' => 'true',
        ];

        return parent::form($form, $form_state);
    }

    public function save(array $form, FormStateInterface $formState)
    {
        $this->entity->set('label', trim($this->entity->label()));

        $status = $this->entity->save();
        $action = $status === SAVED_UPDATED ? 'updated' : 'added';

        $this->messenger->addStatus($this->t(
            'Audience %label has been %action.',
            [
                '%label' => $this->entity->label(),
                '%action' => $action,
            ]
        ));
    }

    public function validateForm(array &$form, FormStateInterface $formState)
    {
        parent::validateForm($form, $formState);

        $id = $formState->getValue('id');
        $existing = $this->entityTypeManager
            ->getStorage('mailchimp_audience')
            ->load($id);

        if ($existing) {
            $formState->setErrorByName('id', $this->t('A @entity_type with @field_name %value already exists.', [
                '@entity_type' => $this->entity->getEntityType()->getSingularLabel(),
                '@field_name' => 'id',
                '%value' => $id,
            ]));
        }
    }

    public function submitForm(array &$form, FormStateInterface $formState)
    {
        parent::submitForm($form, $formState);

        $formState->setRedirect('entity.mailchimp_audience.collection');
    }
}
