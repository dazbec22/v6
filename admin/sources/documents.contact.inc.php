<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('documents', CC_PERM_EDIT, true);
$contact = $GLOBALS['config']->get('Contact_Form');
if (isset($_POST['contact']) && is_array($_POST['contact'])) {
    $data = $_POST['contact'];
    if (isset($_POST['department']) && is_array($_POST['department'])) {
        $i=0;
        foreach ($_POST['department']['name'] as $key => $value) {
            if (empty($value)) {
                continue;
            }
            ++$i;
            $data['department'][$i] = array(
                'name' => $value,
                'email' => $_POST['department']['email'][$key],
            );
        }
    }
    $data['description'] = base64_encode(stripslashes($GLOBALS['RAW']['POST']['contact']['description']));
    if ($GLOBALS['config']->set('Contact_Form', '', $data)) {
        $GLOBALS['main']->successMessage($lang['contact']['notify_contact_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['contact']['error_contact_update']);
    }
    httpredir(currentPage());
}

$GLOBALS['gui']->addBreadcrumb($lang['contact']['contact_form']);
$GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
$GLOBALS['main']->addTabControl($lang['documents']['tab_content'], 'pagecontent');
$GLOBALS['main']->addTabControl($lang['settings']['tab_seo'], 'seo');
if (isset($contact['department']) && is_array($contact['department'])) {
    $GLOBALS['smarty']->assign('DEPARTMENTS', $contact['department']);
}
$contact['description'] = (isset($contact['description'])) ? base64_decode($contact['description']) : '';
$GLOBALS['smarty']->assign('CONTACT', $contact);

$page_content = $GLOBALS['smarty']->fetch('templates/documents.contact.php');
