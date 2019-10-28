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
    $bundles = \Drupal::service('entity.manager')
      ->getBundleInfo('paragraph');
    $paragraph_types = [];

    foreach ($bundles as $key => $bundle) {
      $paragraph_types[$key] = $bundle['label'];
    }
    unset($bundles);

    $paragraph_fields = [];
    foreach ($paragraph_types as $key => $pt) {
      $paragraph_fields[] = \Drupal::service('entity_field.manager')
        ->getFieldDefinitions('paragraph', $key);
    }
    unset($paragraph_types);

    array_filter($paragraph_fields, function ($type) {
      return ($type instanceof FieldConfig);
    });

    $fields = [];

    foreach ($paragraph_fields as $pfield) {
      foreach ($pfield as $pkey => $pp) {
        if (strpos($pkey, 'field_') === 0) {
          $fields[$pkey] = $pp->get('label');
        }
      }
    }

    $form['paragraph'] = [
      '#type' => 'select',
      '#title' => $this->t('Field paragraphs'),
      '#default_value' => $this->config('link_to_paragraph.config')->get('paragraph'),
      '#options' => $fields,
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('link_to_paragraph.config')
      ->set('paragraph', $form_state->getValue('paragraph'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
