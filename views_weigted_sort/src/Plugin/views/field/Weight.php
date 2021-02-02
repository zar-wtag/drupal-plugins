<?php

namespace Drupal\views_weigted_sort\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Views field plugin to display 'measured weight'.
 *
 * Definition items:
 * - additional fields:
 *   - weight_value: Supply name of the DB column where weight_values are stored
 *
 * @ViewsField("country_weight")
 */
class Weight extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    parent::query();

    // We actually do not even have to introduce the additional 'weight_values' column
    // ourselves because 'additional fields' property of field definition, in
    // fact, is magical one - whatever addtional columns are defined there get
    // automatically into the SELECT query in FieldPluginBase::query() method.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Since our primary column is weight, we can get its value without
    // supplying the 2nd argument into the ::getValue() method.
    $defaultWeight = 1;

    // To retrieve a value of an additional field, just use the construction as
    // below. The 'weight_values' key of $this->additional_fields is the name of
    // additional field whose value we intend to retrieve from $values. In fact
    // $this->additional_fields['weight_values'] will get us alias of the additional
    // field 'weight_values' under which it was included into the SELECT query.
    $weight_values = $this->getValue($values, $this->additional_fields['weight_values']);

    // If the actual value is in lbs, convert it to kilograms.
    if ($weight_values != 'CH') {
      $defaultWeight = 2;
    }

    // Now it all reduces to just pretty-printing the kilogram amount. This is
    // the actual content Views will display for our field.
    return $this->t('@weight', [
      '@weight' => $defaultWeight
    ]);
  }

}
