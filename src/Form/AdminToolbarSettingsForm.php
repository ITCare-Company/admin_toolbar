<?php

namespace Drupal\admin_toolbar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminToolbarSettingsForm. The config form for the admin_toolbar module.
 *
 * @package Drupal\admin_toolbar\Form
 */
class AdminToolbarSettingsForm extends ConfigFormBase {

  /**
   * The cache menu instance.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheMenu;

  /**
   * The menu link manager instance.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * {@inheritdoc}
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->cacheMenu = $container->get('cache.menu');
    $instance->menuLinkManager = $container->get('plugin.manager.menu.link');
    return $instance;
  }

  /**
   * {@inheritDoc}
   *
   * @return array<string>
   *   An array of configuration names that this form is responsible for.
   */
  protected function getEditableConfigNames() {
    return [
      'admin_toolbar.settings',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'admin_toolbar_settings';
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array<string, mixed>
   *   The form array with the form elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('admin_toolbar.settings');
    $depth_values = range(1, 9);
    $form['menu_depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu depth'),
      '#description' => $this->t('Maximal depth of displayed menu.'),
      '#default_value' => $config->get('menu_depth'),
      '#options' => array_combine($depth_values, $depth_values),
    ];

    // Enable the HoverIntent plugin behavior.
    $form['enable_hoverintent'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable HoverIntent'),
      '#description' => $this->t("Provides a smoother user experience, where only menu items which are paused over are expanded, to avoid accidental activations.<br/>Disable to use module's default basic JavaScript behavior."),
      '#default_value' => $config->get('enable_hoverintent'),
    ];

    $form['sticky_options_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Toolbar sticky behavior'),
    ];

    $form['sticky_options_wrapper']['sticky_behavior'] = [
      '#type' => 'radios',
      '#prefix' => $this->t("By default, the Admin Toolbar sticky behavior is <em>enabled</em> so it stays at the top of the browser window when scrolling up or down.<br/>Select <em>Disabled</em> to disable Admin Toolbar's sticky behavior so it stays at the top of the page when scrolling."),
      '#options' => [
        'enabled' => $this->t('Enabled'),
        'disabled' => $this->t('Disabled'),
        'hide_on_scroll_down' => $this->t('Disabled: Hide on scroll-down, show on scroll-up'),
      ],
      '#default_value' => $config->get('sticky_behavior') ?: 'enabled',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @param array<mixed> $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return void
   *   Nothing to return.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('admin_toolbar.settings')
      ->set('menu_depth', $form_state->getValue('menu_depth'))
      ->set('enable_hoverintent', $form_state->getValue('enable_hoverintent'))
      ->set('sticky_behavior', $form_state->getValue('sticky_behavior'))
      ->save();
    parent::submitForm($form, $form_state);
    $this->cacheMenu->deleteAll();
    $this->menuLinkManager->rebuild();
  }

}
