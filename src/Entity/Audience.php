<?php

namespace Drupal\wmsubscription_mailchimp\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * @ConfigEntityType(
 *   id = "mailchimp_audience",
 *   label = @Translation("Mailchimp audience"),
 *   label_collection = @Translation("Mailchimp audiences"),
 *   handlers = {
 *     "list_builder" = "Drupal\wmsubscription_mailchimp\ListBuilder\AudienceListBuilder",
 *     "route_provider" = {
 *         "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "form" = {
 *         "default" = "Drupal\wmsubscription_mailchimp\Form\AudienceForm",
 *         "add" = "Drupal\wmsubscription_mailchimp\Form\AudienceForm",
 *         "edit" = "Drupal\wmsubscription_mailchimp\Form\AudienceForm",
 *         "delete" : "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *   },
 *   config_prefix = "audience",
 *   admin_permission = "administer mailchimp audiences",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "collection" = "/admin/config/services/subscription/mailchimp/audience",
 *     "add-form" = "/admin/config/services/subscription/mailchimp/audience/add",
 *     "edit-form" = "/admin/config/services/subscription/mailchimp/audience/{mailchimp_audience}",
 *     "delete-form" = "/admin/config/services/subscription/mailchimp/audience/{mailchimp_audience}/delete",
 *   }
 * )
 */
class Audience extends ConfigEntityBase implements AudienceInterface
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $label;

    public function getId(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }
}
