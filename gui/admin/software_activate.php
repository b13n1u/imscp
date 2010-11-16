<?php
/**
 * i-MSCP a internet Multi Server Control Panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2010 by ispCP | http://isp-control.net
 * @copyright 	2010 by i-MSCP | http://i-mscp.net
 * @version 	SVN: $Id: settings.php 3700 2010-11-16 09:34:48Z thecry $
 * @link 		http://i-mscp.net
 * @author 		ispCP Team
 * @author 		i-MSCP Team
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 * Portions created by the i-MSCP Team are Copyright (C) 2010 by
 * i-MSCP a internet Multi Server Control Panel. All Rights Reserved.
 */

require '../include/imscp-lib.php';

check_login(__FILE__);

if (isset($_GET['id']) AND is_numeric($_GET['id'])) {
	$query="
		SELECT
			`software_id`,
			`software_archive`,
			`reseller_id`
		FROM
			`web_software`
		WHERE
			`software_id` = ?
		AND
			`software_active` = 0
	";
	$rs = exec_query($sql, $query, $_GET['id']);
	if ($rs->recordCount() != 1) {
		set_page_message(tr('Wrong software id.'));
		header('Location: software_manage.php');
	} else {
		$update="
			UPDATE
				`web_software`
			SET
				`software_active` = 1
			WHERE
				`software_id` = ?
		";
		$res = exec_query($sql, $update, $_GET['id']);
		$query="
			SELECT
				`software_id`,
				`software_name`,
				`software_version`,
				`software_language`,
				`reseller_id`,
				`software_archive`
			FROM
				`web_software`
			WHERE
				`software_id` = ?
		";
		$res = exec_query($sql, $query, $_GET['id']);
		
		update_existing_client_installations_res_upload(
			$res->fields['software_id'], $res->fields['software_name'],
			$res->fields['software_version'], $res->fields['software_language'],
			$res->fields['reseller_id']
		);
		
		send_activated_sw (
			$res->fields['reseller_id'],
			$res->fields['software_archive'].".tar.gz",
			$res->fields['software_id']
		);
		
		set_page_message(tr('Software was activated.'));
		header('Location: software_manage.php');
	}
} else {
	set_page_message(tr('Wrong software id.'));
	header('Location: software_manage.php');
}
?>