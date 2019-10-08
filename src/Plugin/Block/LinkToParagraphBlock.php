<?php

namespace Drupal\link_to_paragraph\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "link_to_paragraph",
 *   admin_label = @Translation("Link to paragraph block"),
 * )
 */
class LinkToParagraphBlock extends BlockBase {

  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!($node instanceof \Drupal\node\NodeInterface)) {
      // You can get nid and anything else you need from the node object.
      return []; // TODO set default message if node empty
    }
    if (!($paragraph_field_items = $node->get('field_content')->getValue())) {
      return []; // TODO set default message if node empty
    }

    unset($node);

    // Get storage. It very useful for loading a small number of objects.
    try {
      $paragraph_storage = \Drupal::entityTypeManager()
        ->getStorage('paragraph');
    } catch (\Exception $exception) {
      return []; // TODO set default message if node empty
    }
    // Collect paragraph field's ids.
    $ids = array_column($paragraph_field_items, 'target_id');
    // Load all paragraph objects.
    $paragraphs_objects = $paragraph_storage->loadMultiple($ids);
    $titles = [];
    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    foreach ($paragraphs_objects as $paragraph) {
      // Get field from the paragraph.
      $titles[] = [
        'id' => $paragraph->id(),
        'value' => $paragraph->get('field_title')->value,
      ];
      // Do something with $text...
    }

    return [
      '#theme' => 'link_to_paragraphs',
//      '#title' => 'NOPE',
      '#paragraphs' => $titles,
    ];
  }

  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  public function blockForm($form, FormStateInterface $form_state) {
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['link_to_paragraph_settings'] = $form_state->getValue('link_to_paragraph_settings');
  }


}
