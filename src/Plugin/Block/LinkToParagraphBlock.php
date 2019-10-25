<?php

namespace Drupal\link_to_paragraph\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "link_to_paragraph",
 *   admin_label = @Translation("Link to paragraph block"),
 * )
 */
class LinkToParagraphBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!($node instanceof NodeInterface)) {
      // You can get nid and anything else you need from the node object.
      // TODO set default message if node empty.
      return [];
    }

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $paragraphs = $node->get('field_content')->referencedEntities();

    $titles = [];

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    foreach ($paragraphs as $paragraph) {

      // Get field from the paragraph.
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
      '#paragraphs' => $titles,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['link_to_paragraph_settings'] = $form_state->getValue('link_to_paragraph_settings');
  }

}
