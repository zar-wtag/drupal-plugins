<?php

namespace Drupal\views_weigted_sort\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * Country-aware sorting by weight.
 *
 * To expose you further to the definition items, in this sort handler let's
 * receive the following parameters from the definition:
 * - weight_value_column: (string) Name of the column where weight weight_values are stored.
 * - lb_to_kg: (float) A coefficient, multiplying by which we can convert lbs to
 *   kgs. Just for fun, let's pretend this coefficient might vary so we receive
 *   it from external environment instead of hard coding it internally in the
 *   handler.
 *
 * @ViewsSort("country_weight")
 */
class Weight extends SortPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This line makes sure our table (node_field_data) is properly JOINed within the
    // SQL. In our particular case it poses little benefit, because our table is
    // actually the base table, so there is nothing to JOIN, but remember that
    // your sort handler can be reused virtually anywhere in $views_data, and at
    // some point actually might be invoked on a non-base table.
    $this->ensureMyTable();

    // Do read the docblock comment of the
    // \Drupal\views\Plugin\views\query\Sql::addOrderBy() to understand better
    // what is happening here. We dynamically multiply value of 'weight' column
    // to appropriate coefficient so to have all the weights on the same scale
    // of KGs. Then we sort by the result of multiplication.

    $weight_value_column = $this->definition['weight_value_column'];

    $sql_snippet = <<<EOF
CASE $this->tableAlias.$weight_value_column
  WHEN 'CH' THEN 1
  ELSE 2
END
EOF;

    $this->query->addOrderBy(NULL, $sql_snippet, $this->options['order'], $this->realField);
  }

}
