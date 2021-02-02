<?php

namespace Drupal\country_top;

use Drupal\Core\Database\Database;
use Drupal\views\Entity\View;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines a service for managing RSVP list enabled for nodes.
 */
class EnablerService {

  /**
   * Interface to config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryInterface;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactoryInterface) {
    $this->configFactoryInterface = $configFactoryInterface;
  }

  /**
   * Sets a individual node to be RSVP enabled.
   *
   * @param \Drupal\views\Entity\View $view
   */
  public function setEnabled(View $view): void {
    if (!$this->isEnabled((string) $view->id())) {
      $connection = Database::getConnection();
      $viewId = $connection->insert('country_top_enabled')->fields([
        'vid' => $view->id(),
      ])->execute();
    }
  }

  /**
   * Checks if an individual node is RSVP enabled.
   *
   * @param string $viewId
   *
   * @return bool
   *   Whether the node is enabled for the RSVP functionality.
   */
  public function isEnabled(string $viewId) {
    $config = $this->configFactoryInterface->getEditable('country_top.settings');
    $allowed_types = $config->get('allowed_types');
    return in_array($viewId, $allowed_types, TRUE);
  }

  /**
   * Deletes enabled settings for an individual node.
   *
   * @param \Drupal\views\Entity\View $view
   */
  public function delEnabled(View $view): void {
    $delete = Database::getConnection()->delete('country_top_enabled');
    $delete->condition('vid', (string) $view->id());
    $delete->execute();
  }

}
