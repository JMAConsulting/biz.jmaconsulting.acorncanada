<?php

require_once 'acorncanada.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function acorncanada_civicrm_config(&$config) {
  _acorncanada_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function acorncanada_civicrm_xmlMenu(&$files) {
  _acorncanada_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function acorncanada_civicrm_install() {
  _acorncanada_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function acorncanada_civicrm_uninstall() {
  _acorncanada_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function acorncanada_civicrm_enable() {
  _acorncanada_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function acorncanada_civicrm_disable() {
  _acorncanada_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function acorncanada_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _acorncanada_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function acorncanada_civicrm_managed(&$entities) {
  _acorncanada_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function acorncanada_civicrm_caseTypes(&$caseTypes) {
  _acorncanada_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function acorncanada_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _acorncanada_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_buildForm
 *
 */
function acorncanada_civicrm_buildForm($formName, &$form) {
  if ($formName == "CRM_Contact_Form_Contact" || $formName == "CRM_Report_Form_Contribute_exportContact") {
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/Acorncanada/Contact.tpl',
    ));
    if ($formName == "CRM_Report_Form_Contribute_exportContact") {
      $form->assign('isReport', TRUE);
      return;
    }
    $form->addSelect('membership_type_id', array('options' => CRM_Member_PseudoConstant::membershipType()));
    if ($form->_contactId) {
      // Set defaults.
      $result = civicrm_api3('Membership', 'get', array(
        'sequential' => 1,
        'return' => array("membership_type_id"),
        'contact_id' => $form->_contactId,
        'status_id' => array('IN' => array("New", "Current")),
        'active_only' => 1,
      ));
      if ($result['count'] > 0) {
        $type = reset($result['values']);
        $form->setDefaults(array('membership_type_id' => $type['membership_type_id']));
      }
    }
  }
  if ($formName == 'CRM_Contribute_Form_ContributionView' && ($contributionID = CRM_Utils_Array::value('id', $_GET))) {
    CRM_Core_Resources::singleton()->addScript(
       "CRM.$(function($) {
         $('.crm-contribution-view-form-block table > tbody > tr').eq(5).after('<tr><td class=\"label\">Amount Label</td><td class=\"bold\">" . civicrm_api3('Contribution', 'getvalue', ['id' => $contributionID, 'return' => 'amount_level']) . "</td></tr>');
       });"
     );
  }
}

/**
 * Implementation of hook_civicrm_postProcess
 *
 */
function acorncanada_civicrm_postProcess($formName, &$form) {
  if ($formName == "CRM_Contact_Form_Contact") {
    $memType = CRM_Utils_Array::value('membership_type_id', $form->_submitValues, NULL);
    if ($memType && $form->_contactId) {
      try{
        civicrm_api3('Membership', 'create', array(
          'sequential' => 1,
          'membership_type_id' => (int)$memType,
          'contact_id' => (int)$form->_contactId,
          'status_id' => "Current",
        ));
      }
      catch (CiviCRM_API3_Exception $e) {
        // Handle error here.
        $errorMessage = $e->getMessage();
        $errorCode = $e->getErrorCode();
        $errorData = $e->getExtraParams();
        return array(
          'error' => $errorMessage,
          'error_code' => $errorCode,
          'error_data' => $errorData,
        );
      }
    }
  }
}

function acorncanada_civicrm_searchColumns($contextName, &$columnHeaders, &$rows, $form) {
  if ($contextName == 'contribution') {
    foreach ($columnHeaders as $index => &$column) {
      if (!empty($column['field_name']) && $column['field_name'] == 'product_name') {
        $columnHeaders[$index] = array_merge($column, array(
          'name' => ts('Amount label'),
          'field_name' => 'amount_level',
        ));

        foreach ($rows as $key => $row) {
          $rows[$key]['amount_level'] = civicrm_api3('Contribution', 'getvalue', ['id' => $row['contribution_id'], 'return' => 'amount_level']);
        }
        break;
      }
    }
  }
}
