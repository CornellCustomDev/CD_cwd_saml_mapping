<?php

declare(strict_types=1);

namespace Drupal\cwd_saml_mapping\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a single sign-on login block.
 */
#[Block(
  id: 'cwd_saml_mapping_single_sign_on_login_block',
  admin_label: new TranslatableMarkup('Single Sign-On Login Block'),
  category: new TranslatableMarkup('Custom'),
)]
final class SingleSignOnLoginBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'block_text' => $this->t('You must log in to view this content.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['block_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Block Text'),
      '#default_value' => $this->configuration['block_text'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['block_text'] = $form_state->getValue('block_text')['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $configs = \Drupal::config('saml_sp_drupal_login.config');
    $values = $configs->getRawData();
    $idps = array_filter($values['idp']);
    $current_path = $this->calculateCurrentPath();
    foreach ($idps as $idp_key => $idp_value) {
      $login_url = '/saml/drupal_login/' . $idp_key;
      $idp_config = \Drupal::config('saml_sp.idp.' . $idp_key);
      $stored_name = $idp_config->getRawData()['label'];
      $fancy_name = _get_fancy_cornell_names($stored_name);
      $link_markup = '<a href="' . $login_url . '?returnTo=' . $current_path . '"> ' . $fancy_name . ' </a>';
      $links[] = $link_markup;

    }
    $markup = '';

    $markup = '<ul class="login-links"><li>' . implode('</li><li>', $links) . '</li></ul>';
    if (!empty($this->configuration['block_text'])) {
      $markup = '<div class="block-text">' . $this->configuration['block_text'] . $markup . '</div>';
    }

    return [
      '#markup' => $markup,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

  }

  public function calculateCurrentPath() {
    $current_path = \Drupal::service('path.current')->getPath();
    $request = \Drupal::requestStack()->getCurrentRequest();
    $query_params = $request->query->all();
    if ($query_params['returnTo']) {
      $current_path = $query_params['returnTo'];
    }
    $final_path = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
    return $final_path;
  }
}
