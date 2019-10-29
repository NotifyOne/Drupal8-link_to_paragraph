<?php

namespace Drupal\link_to_paragraph\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\field\Entity\FieldConfig;

/**
 * Implements a simple form.
 */
class ConfigurateForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'link_to_paragraph.configurate';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['link_to_paragraph.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $field_paragraphs = [];
    try {
      $field_paragraphs = $this->getAllBundles('paragraph');
    }
    catch (\Exception $e) {

    }

    $form['paragraph'] = [
      '#type' => 'select',
      '#title' => $this->t('Field paragraphs'),
      '#default_value' => $this->config('link_to_paragraph.config')
        ->get('paragraph'),
      '#options' => $field_paragraphs,
    ];
    unset($field_paragraphs);

    $field_nodes = [];
    try {
      $field_nodes = $this->getAllBundles('node');
    }
    catch (\Exception $e) {

    }

    $form['node_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field paragraphs'),
      '#default_value' => $this->config('link_to_paragraph.config')
        ->get('node_field'),
      '#options' => $field_nodes,
    ];
    unset($field_nodes);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Function for get bundles on node or paragraphs.
   *
   * @param string $type
   *   Node or paragraph.
   *
   * @return array
   *   ['machine_name' => 'label']
   *
   * @throws \Exception
   */
  protected function getAllBundles($type) {
    if (!('node' === $type) && !('paragraph' === $type)) {
      throw new \Exception('LinkToParagraphConfigure: ' .
        __FILE__ . ':' . __CLASS__ . ':' . __METHOD__ . ': ' .
        $type . ' type is not allow');
    }

    $fields = [];
    foreach (\Drupal::service('entity_type.bundle.info')
      ->getBundleInfo($type) as $key => $content) {
      $fields[] = \Drupal::service('entity_field.manager')
        ->getFieldDefinitions($type, $key);
    }
    unset($content);

    foreach ($fields as $f) {
      array_filter($f, function ($type) {
        return ($type instanceof FieldConfig);
      });
    }

    $field = [];

    foreach ($fields as $f) {
      foreach ($f as $key => $nn) {
        if ($nn->getSettings()['handler'] !== 'default:paragraph') {
          continue;
        }
        if (strpos($key, 'field_') === 0) {
          $field[$key] = $nn->get('label');
        }
      }
    }

    // Setup value as 'name | machine_name'.
    array_walk($field, function (&$value, $key) {
      $value .= ' | ' . $key;
    });


    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('link_to_paragraph.config')
      ->set('paragraph', $form_state->getValue('paragraph'))
      ->set('node_field', $form_state->getValue('node_field'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
