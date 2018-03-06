<?php

/**
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\apigee_edge\Plugin\KeyType;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\key\Plugin\KeyTypeBase;
use Drupal\key\Plugin\KeyTypeMultivalueInterface;

/**
 * Key type for Apigee Edge basic auth credentials.
 *
 * @KeyType(
 *   id = "apigee_edge_basic_auth",
 *   label = @Translation("Apigee Edge Basic Auth"),
 *   description = @Translation("Key type to use for Apigee Edge basic auth credentials."),
 *   group = "apigee_edge",
 *   key_value = {
 *     "plugin" = "apigee_edge_basic_auth_input"
 *   },
 *   multivalue = {
 *     "enabled" = true,
 *     "fields" = {
 *       "endpoint" = @Translation("Apigee Edge endpoint"),
 *       "organization" = @Translation("Organization"),
 *       "username" = @Translation("Username"),
 *       "password" = @Translation("Password")
 *     }
 *   }
 * )
 */
class BasicAuthKeyType extends KeyTypeBase implements KeyTypeMultivalueInterface {

  /**
   * {@inheritdoc}
   */
  public static function generateKeyValue(array $configuration) {
    return '[]';
  }

  /**
   * {@inheritdoc}
   */
  public function validateKeyValue(array $form, FormStateInterface $form_state, $key_value) {
    if (empty($key_value)) {
      return;
    }

    $value = $this->unserialize($key_value);
    if ($value === NULL) {
      $form_state->setError($form, $this->t('The key value does not contain valid JSON.'));
      return;
    }

    foreach ($this->getPluginDefinition()['multivalue']['fields'] as $id => $field) {
      $error_element = $form['settings']['input_section']['key_input_settings'][$id] ?? $form;

      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $field */
      if (!isset($value[$id])) {
        $form_state->setError($error_element, $this->t('The key value is missing the field %field.', ['%field' => $field->render()]));
      }
      elseif (empty($value[$id])) {
        $form_state->setError($error_element, $this->t('The key value field %field is empty.', ['%field' => $field->render()]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function serialize(array $array) {
    return Json::encode($array);
  }

  /**
   * {@inheritdoc}
   */
  public function unserialize($value) {
    return Json::decode($value);
  }

}
