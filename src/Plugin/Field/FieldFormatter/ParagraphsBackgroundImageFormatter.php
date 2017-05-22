<?php

/**
 * @file
 * Contains \Drupal\paragraphs_background_image\Plugin\Field\FieldFormatter\ParagraphsBackgroundImageFormatter.
 */

namespace Drupal\paragraphs_background_image\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Form\FormStateInterface;


/**
 * @FieldFormatter(
 *  id = "paragraphs_background_image",
 *  label = @Translation("Background image"),
 *  field_types = {"image"}
 * )
 */
class ParagraphsBackgroundImageFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'image_style' => ''
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = array();

    $element['image_style'] = parent::settingsForm($form, $form_state)['image_style'];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    $image_style_setting = $this->getSetting('image_style');

    // Collect cache tags to be added for each item in the field.
    $cache_tags = array();
    if (!empty($image_style_setting)) {
      $image_style = $this->imageStyleStorage->load($image_style_setting);
      $cache_tags = $image_style->getCacheTags();
    }

    $urls = [];
    foreach ($files as $delta => $file) {
      if (!empty($image_style)) {
        $image_url = $image_style->buildUrl($file->getFileUri());
      } else {
        $image_url = file_create_url($file->getFileUri());
      }
      $urls[] = 'url(' . $image_url . ')';
      $cache_tags = Cache::mergeTags($cache_tags, $file->getCacheTags());

      $elements[$delta] = array(
        '#cache' => array(
          'tags' => $cache_tags,
        ),
      );
    }

    /* @var $entity \Drupal\paragraphs\Entity\Paragraph */
    $entity = $items->getParent()->getValue();
    $entity->backgroundImages = $urls;

    return $elements;
  }
}


