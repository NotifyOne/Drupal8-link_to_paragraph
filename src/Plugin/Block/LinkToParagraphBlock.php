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
      return []; // TODO set default message if node empty.
    }

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $paragraphs = $node->get('field_content')->referencedEntities();

    $titles = [];

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    foreach ($paragraphs as $paragraph) {

//       Get field from the paragraph.
      if ($paragraph->hasTranslation($language)) {
        $paragraph = $paragraph->getTranslation($language);
      }

      $titles[] = [
        'id' => $paragraph->id(),
        'value' => $paragraph->get('field_title')->getValue()[0]['value'],
      ];
      unset($translate);
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
