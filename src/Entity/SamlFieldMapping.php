<?php declare(strict_types=1);

namespace Drupal\cwd_saml_mapping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cwd_saml_mapping\SamlFieldMappingInterface;

/**
 * Defines the saml field mapping entity type.
 *
 * @ConfigEntityType(
 *   id = "saml_field_mapping",
 *   label = @Translation("SAML Field Mapping"),
 *   label_collection = @Translation("SAML Field Mappings"),
 *   label_singular = @Translation("saml field mapping"),
 *   label_plural = @Translation("saml field mappings"),
 *   label_count = @PluralTranslation(
 *     singular = "@count saml field mapping",
 *     plural = "@count saml field mappings",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cwd_saml_mapping\SamlFieldMappingListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cwd_saml_mapping\Form\SamlFieldMappingForm",
 *       "edit" = "Drupal\cwd_saml_mapping\Form\SamlFieldMappingForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *   },
 *   config_prefix = "saml_field_mapping",
 *   admin_permission = "administer saml_field_mapping",
 *   links = {
 *     "collection" = "/admin/structure/saml-field-mapping",
 *     "add-form" = "/admin/structure/saml-field-mapping/add",
 *     "edit-form" = "/admin/structure/saml-field-mapping/{saml_field_mapping}",
 *     "delete-form" = "/admin/structure/saml-field-mapping/{saml_field_mapping}/delete",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "samlprop",
 *     "samlother",
 *     "field",
 *   },
 * )
 */
final class SamlFieldMapping extends ConfigEntityBase implements SamlFieldMappingInterface {
  protected string $id;
  protected string $label;
  protected string $samlprop;
  protected string $samlother;
  protected string $field;
}
