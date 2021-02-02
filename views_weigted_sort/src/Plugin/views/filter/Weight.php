<?php

namespace Drupal\views_weigted_sort\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Country-aware weight filter.
 *
 * Provide the following parameters from the definition:
 * - weight_value_column: (string) Name of the column where weight weight_values are stored.
 *
 * @ViewsFilter("country_weight")
 */
class Weight extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // All the site-builder configurable options should be declared in this
    // method. In our case we need site-builder to specify value, which consists
    // of the absolute value and the units.

    $options['value'] = [
      'contains' => [
        'value' => ['default' => ''],
      ],
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function operatorOptions() {
    // Since filtering by its nature has the notion of operator, this method
    // from parent class allows filter handlers to specify which operators they
    // support. The parent class does the job of letting user choose one of
    // these operators on the Views UI and then the chosen operator becomes
    // available to us at $this->operator.
    return [
      '<' => $this->t('Is less than'),
      '<=' => $this->t('Is less than or equal to'),
      '=' => $this->t('Is equal to'),
      '!=' => $this->t('Is not equal to'),
      '>=' => $this->t('Is greater than or equal to'),
      '>' => $this->t('Is greater than'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    // Every option we have defined in ::defineOptions() should have a way to be
    // specified within ::valueForm(). So we throw in 2 elements into the $form:
    // the 'value' (which we make a nested array with 'value' and 'unit' keys)
    // and the 'precision'. Every option of our filter handler is available at
    // $this->options['name_of_the_option']. There is a shortcut for 'value'
    // option, which presumably holds the actual value we are filtering against,
    // $this->value basically equals to $this->options['value'] and it is
    // handled in the parent class for our convenience.

    $form['value']['#tree'] = TRUE;

    // The widget for entering value should be required either when it is not
    // exposed or when it is exposed and marked as required.
    $is_required = !$this->isExposed() || $this->options['expose']['required'];

    $form['value']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#size' => 30,
      '#default_value' => $this->value['value'],
      '#required' => $is_required,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    // The query here is just a tiny bit further complex than the one from
    // weight sort handler. We still employ the same CASE .. WHEN .. THEN SQL
    // expression.

    $weight_value_column = $this->definition['weight_value_column'];

    $sql_snippet = <<<EOF
  CASE $this->tableAlias.$weight_value_column
    WHEN 'CH THEN 1
    WHEN 'lb' THEN 2
  END
EOF;

    // If it was a simpler condition, we would have used
    // $this->query->addWhere() but because our SQL snippet involves expression,
    // we ought to use ::addWhereExpression() which gives us enough freedom to
    // inject our formula.
    $this->query->addWhereExpression($this->options['group'], $sql_snippet, []);
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    // This method is responsible for generating a one-line summary on the Views
    // UI. Basically we display 'grouped' or 'exposed' if that is the case,
    // otherwise we display the actual filter criterion currently specified in
    // this handler.
    // Hopefully you know the concepts of a 'grouped' and 'exposed' filter from
    // the Views UI.

    if ($this->isAGroup()) {
      return $this->t('grouped');
    }
    if (!empty($this->options['exposed'])) {
      return $this->t('exposed');
    }

    return $this->operator . ' ' . $this->value['value'] . $this->value['weight_value'];
  }

}
