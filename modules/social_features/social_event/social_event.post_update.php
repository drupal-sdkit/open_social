<?php

/**
 * @file
 * Post update functions for the Social event module.
 */

/**
 * Empty post update hook.
 */
function social_event_post_update_update_events(&$sandbox) {
  // Moved to social_event_post_update_10301_enable_event_enrollment().
}

/**
 * Set event enrollment option to enabled by default for existing events.
 */
function social_event_post_update_10301_enable_event_enrollment(&$sandbox) {
  /** @var \Drupal\node\NodeStorageInterface $node_storage */
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  if (!isset($sandbox['total'])) {
    // Get all event ids.
    $sandbox['ids'] = $node_storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'event')
      ->execute();
    // Write total of entities need to be processed to $sandbox.
    $sandbox['total'] = count($sandbox['ids']);

    // Initiate default value for current processing № of element.
    $sandbox['current'] = 0;
  }

  // Do not continue if no entities are found.
  if (empty($sandbox['total']) || empty($sandbox['ids'])) {
    $sandbox['#finished'] = 1;
    return t('No events to be processed.');
  }

  // Try to update 25 events at a time.
  $ids = array_slice($sandbox['ids'], $sandbox['current'], 25);

  /** @var \Drupal\node\NodeInterface $event */
  foreach ($node_storage->loadMultiple($ids) as $event) {
    if ($event->hasField('field_event_enable_enrollment')) {
      $event->set('field_event_enable_enrollment', '1');
      $event->save();
    }
    $sandbox['current']++;
  }

  // Try to update the percentage but avoid division by zero.
  $sandbox['#finished'] = empty($sandbox['total']) ? 1 : ($sandbox['current'] / $sandbox['total']);
}
