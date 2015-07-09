<?php
/**
 * @file
 * Contains \Drupal\migrate_custom\Plugin\migrate\source\User.
 */

namespace Drupal\migrate_custom\Plugin\migrate\source;

use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "custom_user"
 * )
 */
class User extends DrupalSqlBase implements SourceEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', array_keys($this->baseFields()))
      ->condition('uid', 1, '>');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['first_name'] = $this->t('First Name');
    $fields['last_name'] = $this->t('Last Name');
    $fields['biography'] = $this->t('Biography');
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $uid = $row->getSourceProperty('uid');

    // first_name
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_first_name_value
      FROM
        {field_data_field_first_name} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('first_name', $record->field_first_name_value );
    }

    // last_name
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_last_name_value
      FROM
        {field_data_field_last_name} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('last_name', $record->field_last_name_value );
    }

    // biography
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_biography_value,
        fld.field_biography_format
      FROM
        {field_data_field_biography} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('biography_value', $record->field_biography_value );
      $row->setSourceProperty('biography_format', $record->field_biography_format );
    }

    // Unserialize Data.
    $row->setSourceProperty('data', unserialize($row->getSourceProperty('data')));

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'uid' => array(
        'type' => 'integer',
        'alias' => 'u',
      ),
    );
  }

  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {
    $fields = array(
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'signature' => $this->t('Signature'),
      'signature_format' => $this->t('Signature format'),
      'created' => $this->t('Registered timestamp'),
      'access' => $this->t('Last access timestamp'),
      'login' => $this->t('Last login timestamp'),
      'status' => $this->t('Status'),
      'timezone' => $this->t('Timezone'),
      'language' => $this->t('Language'),
      'picture' => $this->t('Picture'),
      'init' => $this->t('Init'),
      'data' => $this->t('User data'),
    );
    return $fields;

}

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'user';
  }

}
?>
