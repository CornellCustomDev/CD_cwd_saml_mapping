<?php declare(strict_types=1);

namespace Drupal\cwd_saml_mapping\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cwd_saml_mapping\ShibbolethHelper;
use Drupal\cwd_saml_mapping\Entity\SamlFieldMapping;

/**
 * SAML Field Mapping form.
 */
final class SamlFieldMappingForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {    
    $form = parent::form($form, $form_state);
    $form['#attached']['library'][] = 'cwd_saml_mapping/cwd_saml_mapping';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => [SamlFieldMapping::class, 'load'],
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $saml_property_mapping = ShibbolethHelper::getMappingArray();
    $form['orstatement'] = array(
      '#markup' => '<h2>Instructions</h2><ul><li>Please Note: there are a number of field processors to handle different types of fields but this is not all inclusive at this time.</li><li>Multi-valued fields in Shibboleth will be handled based on the field you are feeding into.</li></ul>',
    );

    $form['samlprop'] = [
      '#type' => 'select',
      '#options' => $saml_property_mapping,
      '#title' => $this->t('SAML Property'),
      '#description' => $this->t('The property from shibboleth that will be used to fill in the selected field.'),
      '#default_value' => $this->entity->get('samlprop'),
    ];
    
    $form['samlother'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SAML Property Other (if SAML Property Other)'),
      '#default_value' => $this->entity->get('samlother'),
    ];

    $form['field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User Field'),
      // '#description' => $this->t('The values that will allow this role to be added. One per line'),
      '#default_value' => $this->entity->get('field'),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $this->messenger()->addStatus(
      match ($result) {
        \SAVED_NEW => $this->t('Created new saml_field_mapping %label.', $message_args),
        \SAVED_UPDATED => $this->t('Updated saml_field_mapping %label.', $message_args),
      }
    );
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
