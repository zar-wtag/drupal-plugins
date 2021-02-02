<?php

namespace Drupal\country_top\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines a form to configure Wondrous set Country Top module settings.
 */
class CountryTopSettingsForm extends ConfigFormBase {

  /**
   * Interface to entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entityTypeManager
    ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): CountryTopSettingsForm {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Return the form id using getFormID() method.
   */
  public function getFormId(): string {
    return 'country_top_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'country_top.settings'
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Build the form using buildForm() method.
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $types = $this->getViewNames();
    $config = $this->config('country_top.settings');
    $form['country_top_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable the views to be altered.'),
      '#default_value' => $config->get('allowed_types'),
      '#options' => $types,
      '#description' => t('On the specified node types, an RSVP option will be available and can be enabled while that node is being edited.'),
    ];
    $form['array_filter'] = ['#type' => 'value', '#value' => TRUE];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $allowed_types = array_filter($form_state->getValue('country_top_types'));
    sort($allowed_types);
    $this->config('country_top.settings')
      ->set('allowed_types', $allowed_types)
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   */
  private function getViewNames(): array {
    $query = $this->entityTypeManager->getStorage('view')->getQuery();
    $entity_ids = (array) $query->condition('status', TRUE)
      ->execute();
    $result = [];
    foreach ($entity_ids as $id) {
      $view = $this->entityTypeManager->getStorage('view')->load($id);
      if ($view != NULL) {
        $result[$id] = $view->toArray()['label'];
      }
    }
    return $result;
  }

}
