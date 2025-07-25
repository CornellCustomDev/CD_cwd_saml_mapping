<?php
namespace Drupal\cwd_saml_mapping;

use SimpleXMLElement;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\redirect\Entity\Redirect;
use Drupal\Core\File\FileSystemInterface;

class DataConversionHelper {

  // Function that takes a URL and creates the correct image file structure for image fields
  public static function createImageArrayFromUrl($url, $storage_path = 'public://') {
    $temp = explode('/', $url);
    $name_of_file = array_pop($temp);
    $url = implode("/", $temp) . "/" . rawurlencode($name_of_file);
    $url = trim(str_replace("https:", "http:", $url));
    $data = file_get_contents($url);
    $file = \Drupal::service('file.repository')->writeData($data, $storage_path . '/' . $name_of_file, FileSystemInterface::EXISTS_REPLACE);
    return [
      'target_id' => $file->id(),
      'alt' => '',
    ];
  }

  // Function that takes a URL and creates the correct file structure for file fields
  public static function createFileAndArrayFromUrl($url, $storage_path = 'public://') {
    $temp = explode('/', $url);
    $name_of_file = array_pop($temp);
    $url = implode("/", $temp) . "/" . rawurlencode($name_of_file);
    $url = str_replace("https:", "http:", $url);
    $data = file_get_contents($url);
    $file = \Drupal::service('file.repository')->writeData($data, $storage_path . '/' . $name_of_file, FileSystemInterface::EXISTS_REPLACE);
    return [
      'target_id' => $file->id(),
    ];

  }

  // Function that takes a URL and creates a media object for that specified media type
  public static function createMediaFromUrl($url, $media_type = 'image', $storage_path = 'public://') {
    $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $url, 'bundle' => $media_type]);
    if (empty($media) || is_null($media)) {
      switch ($media_type) {
        case "image":
          $image_array = self::createImageArrayFromUrl($url, $storage_path);
          $media = \Drupal\media\Entity\Media::create([
            'bundle' => $media_type,
            'uid' => 1,
            'name' => $url,
            'field_media_image' => $image_array,
          ]);
          $media->save();
          return $media->id();
          break;
        default:
          return NULL;
          break;
      }
    }
    return array_shift($media)->id();
  }

  // Function that finds or creates a taxonomy term in a given taxonomy
  public static function findOrCreateTaxonomyTerm($term_name, $tax_vid = "tags") {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name, 'vid' => $tax_vid]);
    if (empty($term) || is_null($term)) {
      $new_term = \Drupal\taxonomy\Entity\Term::create([
        'vid' => $tax_vid,
        'name' => $term_name,
      ]);
      $new_term->enforceIsNew();
      $new_term->save();
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name, 'vid' => $tax_vid]);
    }
    return array_shift($term)->id();
  }

  // Returns and entity id for an entity reference field
  public static function findEntityForReference($entity_type, $search_array) {
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->loadByProperties($search_array);
    if ($entity) {
      return array_shift($entity)->id();
    }
    return NULL;
  }

  public static function processDataIntoFieldArray($field_id, $field_type, $field_definition, $data) {
    $field_data = [];
     switch ($field_type) {
          case "text_with_summary":
          case "text_long":
            $field_data = [
              'value' => $data,
              'format' => 'basic_html',
              // 'format' => 'filtered_html',
            ];
            break;
          case "entity_reference":
            $target_entity_reference_type = $field_definition->getSetting('target_type');
            $target_entity_reference_type_bundle = array_values($field_definition->getSetting('handler_settings')['target_bundles'])[0];
            switch ($target_entity_reference_type) {
              case "taxonomy_term":
                $term_array = [];
                foreach ($data as $term_name) {
                  if ($term_name) {
                    $term_array[] = self::findOrCreateTaxonomyTerm($term_name, $target_entity_reference_type_bundle);
                  }
                }
                $field_data = $term_array;
                break;
              case "media":
                if ($data) {
                  $field_data = self::createMediaFromUrl(trim($data));
                }
                break;
              default:
                //Handle missing handler/error
                $error_msg = "Error with import data: we do not have a handler for entity reference type: " . $target_entity_reference_type . " import will be completed but you should adjust this module and re-import";
                \Drupal::messenger()->addMessage($error_msg, "error");
                \Drupal::logger('cwd_csv_import_tool')->error($error_msg);
                break;
            }
            break;
          case "link":
            //Links can be a single link string or and <a href="LINK">TITLE</a>
            if ($data) {
              $link_array = explode(",", $data);

              $drupal_links = [];
              foreach ($link_array as $link_to_parse) {
                if (filter_var($link_to_parse, FILTER_VALIDATE_URL)) {
                  $drupal_links[] = [
                    "uri" => $link_to_parse,
                  ];
                }
                else {
                  try {
                    $link = new SimpleXMLElement($link_to_parse);
                    $link_title = $link->__toString();
                    $link_href = strval($link->attributes()['href']);
                    if ($link_title && $link_href) {
                      if (strpos($link_href, "http") !== FALSE) {
                        $drupal_links[] = [
                          "uri" => $link_href,
                          "title" => $link_title,
                        ];
                      }
                      else {
                        $drupal_links[] = [
                          "uri" => "https://" . \Drupal::request()->getHost() . $link_href,
                          "title" => $link_title,
                        ];
                      }
                    }
                  }
                  catch (\Exception $e) {
                    \Drupal::logger('cwd_csv_import_tool')->error("Link content error: " . $content);
                  }
                }
              }
              $field_data = $drupal_links;
            }
            break;
          case "string":
          case "string_long":
          case "boolean":
          case "float":
          case "telephone":
          case "text":
            $field_data = $data;
            break;
          case "list_string":
  
            $allowed_values = $field_definition->getSetting('allowed_values');
            $option_values = [];
            if (is_array($data)) {
              foreach ($data as $value) {
                $option_id = array_search($value, $allowed_values);
                if ($option_id) {
                  $option_values[] = $option_id;
                } 
              }
              $field_data = $option_values;
            } else {
              $option_id = array_search($data, $allowed_values);
              if ($option_id) {
                $field_data = $option_id;
              }
            }

            $field_cardinality = $field_definition->getFieldStorageDefinition()->getCardinality();
            if ($field_cardinality == 1 && is_array($field_data)) {
              $field_data = array_shift($field_data);
            }
            break;
          // STILL WOULD NEED TO BE CONVERTED WFJ24
          // case "datetime":
          //   $format_string = 'Y-m-d\TH:i:s';
          //   if ($field_mapping[$column_id]['field_settings']['datetime_type'] == "date") {
          //     $format_string = 'Y-m-d';
          //   }
          //   if ((string) (int) $content === $content) {
          //     $field_data = date($format_string, $content);
          //   }
          //   else {
          //     $field_data = date($format_string, strtotime($content));
          //   }
          //   break;
          default:
            //Handle missing handler/error
            $error_msg = "Error with import data: we do not have a handler for field type: '" . $field_type . "' import will be completed but you should adjust this module and re-import";
            \Drupal::messenger()->addMessage($error_msg, "error");
            \Drupal::logger('cwd_csv_import_tool')->error($error_msg);
            break;
        }
        return $field_data;
  }
}