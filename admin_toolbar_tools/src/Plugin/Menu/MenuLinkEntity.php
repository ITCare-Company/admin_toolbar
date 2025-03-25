<?php

namespace Drupal\admin_toolbar_tools\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a menu link plugins for configuration entities.
 */
class MenuLinkEntity extends MenuLinkDefault {

  /**
   * The entity represented in the menu link.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entity = $container->get('entity_type.manager')
      ->getStorage($instance->pluginDefinition['metadata']['entity_type'])
      ->load($instance->pluginDefinition['metadata']['entity_id']);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    if ($this->entity) {
      return (string) $this->entity->label();
    }
    return $this->pluginDefinition['title'] ?: $this->t('Missing');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    if ($this->entity && method_exists($this->entity, 'getDescription')) {
      $description = $this->entity->getDescription();
    }
    return $description ?? parent::getDescription();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    if ($this->entity) {
      return $this->entity->getCacheContexts();
    }
    return parent::getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    if ($this->entity) {
      return $this->entity->getCacheTags();
    }
    return parent::getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    if ($this->entity) {
      return $this->entity->getCacheMaxAge();
    }
    return parent::getCacheMaxAge();
  }

}
