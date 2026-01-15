<?php

namespace Drupal\cwd_saml_mapping\Form;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cwd_saml_mapping\ShibbolethHelper;

/**
 * Class AutoTranslationForm.
 */
class CWDSamlMappingConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cwd_saml_mapping.config_form',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cwd_saml_mapping_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cwd_saml_mapping.config_form');
    $saml_property_mapping = ShibbolethHelper::getAllowedUserNamePropertyArray();
    $form['username_saml_prop'] = [
      '#type' => 'select',
      '#options' => $saml_property_mapping,
      '#title' => $this->t('SAML Property for Username'),
      '#description' => $this->t('The property from saml_sp that will be used as the Username.'),
      '#default_value' => $config->get('username_saml_prop'),
    ];
    $form['customize_links'] = [
      '#type' => 'details',
      '#title' => $this->t('Customize the login links and form'),
      '#open' => TRUE,
    ];
    $form['customize_links']['use_prod_in_saml'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Production Shibboleth on the Live/Production site.'),
      '#default_value' => $config->get('use_prod_in_saml'),
    ];
    $form['customize_links']['show_all_idps'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show All IDPs that can be used.'),
      '#default_value' => $config->get('show_all_idps'),
    ];
    $form['customize_links']['hide_drupal_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide Drupal Login in all envs.'),
      '#default_value' => $config->get('hide_drupal_login'),
    ];
    $form['customize_links']['hide_drupal_login_prod'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide Drupal Login in Prod.'),
      '#default_value' => $config->get('hide_drupal_login_prod'),
    ];
    $form['customize_headings'] = [
      '#type' => 'details',
      '#title' => $this->t('Customize the login page heading'),
      '#open' => TRUE,
    ];
    $form['customize_headings']['sso_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for Single Sign On login message'),
      '#default_value' => $config->get('sso_text'),
    ];
    $form['customize_headings']['drupal_login_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for Drupal login message'),
      '#default_value' => $config->get('drupal_login_text'),
    ];
    $form['customize_403'] = [
      '#type' => 'details',
      '#title' => $this->t('Customize the 403 page'),
      '#open' => TRUE,
    ];
    $form['customize_403']['description'] = array(
      '#markup' => '<p><strong>Note:</strong> For this config to take effect, set the site 403 to /accessdenied (System > Basic site settings).</p>',
    );
    $form['customize_403']['403_custom_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text for 403 page'),
      '#description' => $this->t('HTML is permitted; shown under page title ("Access denied"). Default: "Restricted access - please login to continue."'),
      '#default_value' => $config->get('403_custom_text'),
      '#size' => 200,
      '#maxlength' => 2000,
      '#required' => false,
    ];
    $form['customize_403']['403_custom_logged_in_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text for 403 page when someone is logged in'),
      '#description' => $this->t('HTML is permitted; shown under page title ("Access denied"). Default: "Restricted access - please login to continue."'),
      '#default_value' => $config->get('403_custom_logged_in_text'),
      '#size' => 200,
      '#maxlength' => 2000,
      '#required' => false,
    ];
    $form['customize_403']['local_login_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for 403 local login button'),
      '#default_value' => $config->get('local_login_text'),
    ];

    $form['access_overrides'] = [
      '#type' => 'details',
      '#title' => $this->t('Restrict Access to this site'),
      '#open' => TRUE,
    ];
    $form['access_overrides']['description'] = array(
      '#markup' => '<p><strong>Note:</strong> this section will alter the site and force a user to a 403 page if not authenticated).</p>',
    );
    $form['access_overrides']['restrict_all_pages'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('By checking this box all pages on this site will be put behind authentication'),
      '#default_value' => $config->get('restrict_all_pages'),
    ];
    $form['access_overrides']['restrict_pages_url'] = [
      '#type' => 'select',
      '#options' => [
        "none" => "None",
        "login" => "Login Page",
        "direct" => "Direct to SSO",
        "403" => "403 Page",
      ],
      '#title' => $this->t('When restricting pages what page should the user be sent to?'),
      '#default_value' => $config->get('restrict_pages_url') ?? "none",
    ];

    //text area for all domains to remove role access
    $form['role_removal_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Role Removal Settings'),
      '#open' => TRUE,
    ];
    $form['role_removal_settings']['domains_to_remove_role_access'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List of domains that will have role access removed (one domain per line)'),
      '#description' => $this->t('Enter each domain on a new line. Example: example.edu'),
      '#default_value' => $config->get('domains_to_remove_role_access'),
      '#rows' => 5,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $ignore = ["submit", "form_build_id", "form_token", "form_id", "op"];
    $config = $this->config('cwd_saml_mapping.config_form');
    foreach($form_state->getValues() as $key => $value) {
      if(!in_array($key,$ignore)) {
        $config->set($key,$value);
      }
    }
    $config->save();
  }
}
