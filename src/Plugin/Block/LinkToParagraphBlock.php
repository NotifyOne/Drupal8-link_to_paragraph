<?php

namespace Drupal\link_to_paragraph\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\page_manager\Entity\PageVariant;

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
   * Function return node.
   *
   * @return \Drupal\node\NodeInterface
   *   Return Node of False if not found
   *
   * @throws \Exception
   *   If not exist node.
   */
  protected function getCurrentNode() {
    $nid = NULL;
    // Try get node by routing (block in node page).
    if (($node = \Drupal::routeMatch()->getParameter('node'))
      instanceof NodeInterface) {
      $nid = $node->id();
    }

    // Try get node by page_manager_page_variant.
    if (!$nid && ($parameters = \Drupal::routeMatch()
      ->getParameter('page_manager_page_variant')) instanceof PageVariant) {
      $context = $parameters->getContexts();
      foreach ($context as $key => $item) {
        if ('current_user' === $key) {
          continue;
        }
        if ('language_interface' === $key) {
          continue;
        }

        /** @var $item \Drupal\page_manager\Context\EntityLazyLoadContext */
        if (!$item->hasContextValue('contextData')) {
          continue;
        }
        $nid = $item->getContextValue('contextData')->id();
      }
    }
    if ($nid) {
      return \Drupal::service('entity.manager')
        ->getStorage('node')->load($nid);
    }

    throw new \Exception('LinkToParagraphBlock: ' .
      __FILE__ . ':' . __CLASS__ . ':' . __METHOD__ . ': not found node');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    try {
      $node = $this->getCurrentNode();
    }
    catch (\Exception $e) {
      return [];
      // TODO: Change text if node not found.
    }

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($node->hasTranslation($language)) {
      $node = $node->getTranslation($language);
    }

    try {
      $paragraphs = $node->get(
        \Drupal::config('link_to_paragraph.config')->get('node_field')
      )->referencedEntities();
    }
    catch (\Exception $e) {
      return [];
      // TODO: Change text if not found node_field.
    }

    $titles = [];

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    foreach ($paragraphs as $paragraph) {

      // Get field from the paragraph.
      if ($paragraph->hasTranslation($language)) {
        $paragraph = $paragraph->getTranslation($language);
      }

      $titles[] = [
        'id' => $paragraph->id(),
        'value' => $paragraph->get(
          \Drupal::config('link_to_paragraph.config')->get('paragraph')
        )->getValue()[0]['value'],
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
