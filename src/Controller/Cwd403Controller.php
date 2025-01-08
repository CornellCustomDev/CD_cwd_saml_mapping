<?php
/**
 * @file
 * Contains \Drupal\cwd_saml_mapping\Controller\Cwd403Controller.
 */
namespace Drupal\cwd_saml_mapping\Controller;
class Cwd403Controller {
  public function content() {
    return array(
      '#type' => 'markup',
      '#markup' => $this->get_markup(),
    );
  }
  protected function get_markup() {
    $not_logged_in = \Drupal::currentUser()->isAnonymous();
    $config = \Drupal::config('cwd_saml_mapping.config_form');
    if($not_logged_in) {
      $form = \Drupal::formBuilder()->getForm(\Drupal\user\Form\UserLoginForm::class);
      if($config->getRawData()['403_custom_text']) {
        $markup = $config->getRawData()['403_custom_text'];
        $form['403_custom_text'] = [
          '#markup' => $markup,
          '#weight' => -1005,
        ];
      }
      return \Drupal::service('renderer')->renderRoot($form);
    }
    else {
      if($config->getRawData()['403_custom_logged_in_text']) {
        return $config->getRawData()['403_custom_logged_in_text'];
      } else {
        return '<p>You don\'t have access to this page.</p>';
      }
    }
  }
}
