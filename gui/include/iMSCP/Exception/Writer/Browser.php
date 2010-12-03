<?php
/**
 * i-MSCP a internet Multi Server Control Panel
 *
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
 * The Original Code is "ispCP - ISP Control Panel".
 *
 * The Initial Developer of the Original Code is ispCP Team.
 * Portions created by Initial Developer are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 * Portions created by the i-MSCP Team are Copyright (C) 2010 by
 * i-MSCP a internet Multi Server Control Panel. All Rights Reserved.
 *
 * @category	i-MSCP
 * @package		iMSCP_Exception
 * @subpackage	Writer
 * @copyright 	2006-2010 by ispCP | http://isp-control.net
 * @copyright 	2010 by i-MSCP | http://i-mscp.net
 * @author		Laurent Declercq <laurent.declercq@i-mscp.net>
 * @version		SVN: $Id$
 * @link		http://i-mscp.net i-MSCP Home Site
 * @license		http://www.mozilla.org/MPL/ MPL 1.1
 */

/**
 * @see iMSCP_Exception_Writer
 */
require_once  INCLUDEPATH . '/iMSCP/Exception/Writer.php';

/**
 * Browser writer class
 *
 * This writer writes an exception messages to the client browser. This writer
 * acts also as a formatter that will use a specific template for the message
 * formatting. If no template path is given, or if the template file is not
 * reachable, a string that represent the message is write to the client
 * browser.
 *
 * The given template should be a template file that can be treated by a
 * pTemplate object.
 *
 * <b>Note:</b> Will be improved later.
 *
 * @category	i-MSCP
 * @package		iMSCP_Exception
 * @subpackage	Writer
 * @author		Laurent Declercq <laurent.declercq@i-mscp.net>
 * @since		1.0.7
 * @version		1.0.4
 * @todo		Display more information like trace on debug mode.
 */
class iMSCP_Exception_Writer_Browser extends iMSCP_Exception_Writer {

	/**
	 * pTemplate instance
	 *
	 * @var iMSCP_pTemplate
	 */
	protected $_pTemplate = null;

	/**
	 * Template file path
	 *
	 * @var string
	 */
	protected $_templateFile = null;

	/**
	 * Constructor
	 *
	 * @param string Template file path
	 */
	public function __construct($templateFile = '') {

		if($templateFile !='') {
			if(is_readable($templateFile = $templateFile) ||
				is_readable($templateFile = "../$templateFile")) {

				$this->_templateFile = $templateFile;
			}
		}
	}

	/**
	 * Writes the exception message to the client browser
	 *
	 * @return void
	 * @todo Add inline template for rescue
	 */
	protected function _write() {

		if($this->_pTemplate != null) {
			$this->_pTemplate->prnt();
		} else {
			echo $this->_message;
		}
	}

	/**
	 * This methods is called from the subject (i.e. when an event occur)
	 *
	 * @param iMSCP_Exception_Handler $exceptionHandler iMSCP_Exception_Handler
	 * @return void
	 */
	public function update(SplSubject $exceptionHandler) {

		// Always write the real exception message if we are the admin
		if(isset($_SESSION) && ((isset($_SESSION['logged_from']) && $_SESSION['logged_from'] == 'admin') ||
			isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin')) {

			$this->_message = $exceptionHandler->getException()->getMessage();

		} else {

			$productionException = $exceptionHandler->getProductionException();

			// An exception for production exists ? If it's not case, use the
			// real exception raised
			$this->_message = ($productionException !== false)
				? $productionException->getMessage()
				: $exceptionHandler->getException()->getMessage();
		}

		if($this->_templateFile != null) {
			$this->_prepareTemplate();
		}

		// Finally, we write the output
		$this->_write();
	}

	/**
	 * Prepares the template
	 *
	 * @return void
	 */
	protected function _prepareTemplate() {

		$this->_pTemplate = new iMSCP_pTemplate();
		$this->_pTemplate->define('page', $this->_templateFile);


		if(iMSCP_Registry::isRegistered('backButtonDestination')) {
			$backButtonDest = iMSCP_Registry::get('backButtonDestination');
		} else {
			$backButtonDest = 'javascript:history.go(-1)';
		}

		$this->_pTemplate->assign(
			array(
				'THEME_COLOR_PATH' => '/themes/' . 'default',
				'BACKBUTTONDESTINATION' => $backButtonDest,
				'MESSAGE' => $this->_message
			)
		);

		// i18n support is available ?
		if (function_exists('tr')) {
			$this->_pTemplate->assign(
				array(
					'TR_SYSTEM_MESSAGE_PAGE_TITLE' => tr('i-MSCP Error'),
					'THEME_CHARSET' => tr('encoding'),
					'TR_BACK' => tr('Back'),
					'TR_ERROR_MESSAGE' => tr('Error Message'),

				)
			);
		} else {
			$this->_pTemplate->assign(
				array(
					'TR_SYSTEM_MESSAGE_PAGE_TITLE' => 'iMSCP Error',
					'THEME_CHARSET' => 'UTF-8',
					'TR_BACK' => 'Back',
					'TR_ERROR_MESSAGE' => 'Error Message',
				)
			);
		}

		$this->_pTemplate->parse('PAGE', 'page');
	} // end prepareTemplate()
}