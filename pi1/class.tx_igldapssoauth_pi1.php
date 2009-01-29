<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Micha�l Gagnon <mgagnon@infoglobe.ca>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Session status' for the 'ig_ldap_sso_auth' extension.
 *
 * @author	Michael Gagnon <mgagnon@infoglobe.ca>
 * @package	TYPO3
 * @subpackage	tx_igldapssoauth
 */
class tx_igldapssoauth_pi1 extends tslib_pibase {

	var $prefixId      = 'tx_igldapssoauth_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_igldapssoauth_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ig_ldap_sso_auth';	// The extension key.
	var $pi_checkCHash = true;
	var $template;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content : The PlugIn content
	 * @param	array		$conf : The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main ($content, $conf)	{

	//	$test = t3lib_div::_GP('tx_igldapssoauth_pi1');

		//iglib_debug::print_this($test);

		//tx_igldapssoauth_config::get_values('ldap');

		tx_igldapssoauth_config::init(TYPO3_MODE, 0);

		$this->conf = $conf;

		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		//$this->pi_initPIflexForm(); //Load flexform
		$this->template = $this->cObj->fileResource($this->conf["templateFile"]); //Load template

		$marker['###LOGIN_FORM_ACTION###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);
		$marker['###LOGIN_SUBMIT_VALUE###'] = htmlspecialchars($this->pi_getLL('login'));
		$marker['###LOGOUT_FORM_ACTION###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);
		$marker['###LOGOUT_SUBMIT_VALUE###'] = htmlspecialchars($this->pi_getLL('logout'));
		$marker['###DISABLE###'] = htmlspecialchars($this->pi_getLL('disable'));

		if (tx_igldapssoauth_config::is_enable('CASAuthentication') && tx_igldapssoauth_config::is_enable('LDAPAuthentication')) {

			$cas_config = tx_igldapssoauth_config::get_values('cas');

			//iglib_debug::print_this($cas_config, 'CAS config');

			phpCAS::client(CAS_VERSION_2_0, (string)$cas_config['host'], (integer)$cas_config['port'], '');

			if (phpCAS::isAuthenticated()) {

				$tmpl_cas_auth = $this->cObj->getSubpart($this->template,'###CAS_AUTHENTICATION_LOGOUT###');

			} else {

				$tmpl_cas_auth = $this->cObj->getSubpart($this->template,'###CAS_AUTHENTICATION_LOGIN###');

			}

		} else {

			$tmpl_cas_auth = $this->cObj->getSubpart($this->template,'###CAS_AUTHENTICATION_DISABLE###');

		}

		$tmpl_cas_auth = $this->cObj->substituteMarkerArrayCached($tmpl_cas_auth, $marker);

		//iglib_debug::print_this($tmpl_cas_auth);

		return $this->pi_wrapInBaseClass($tmpl_cas_auth);
	}

	function pi_loadLL() {

		if (!$this->LOCAL_LANG_loaded && $this->scriptRelPath)  {

			$basePath = t3lib_extMgm::extPath($this->extKey).'res/locallang_pi1.xml';
			$this->LOCAL_LANG = t3lib_div::readLLfile($basePath,$this->LLkey);

			if ($this->altLLkey) {

				$tempLOCAL_LANG = t3lib_div::readLLfile($basePath,$this->altLLkey);
				$this->LOCAL_LANG = array_merge(is_array($this->LOCAL_LANG) ? $this->LOCAL_LANG : array(),$tempLOCAL_LANG);

			}

			// Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):
			if (is_array($this->conf['_LOCAL_LANG.'])) {

				reset($this->conf['_LOCAL_LANG.']);

				while(list($k,$lA)=each($this->conf['_LOCAL_LANG.'])) {

					if (is_array($lA)) {

						$k = substr($k,0,-1);

						foreach($lA as $llK => $llV) {

							if (!is_array($llV)) {

								$this->LOCAL_LANG[$k][$llK] = $llV;

								if ($k != 'default')    {
									$this->LOCAL_LANG_charset[$k][$llK] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];        // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
								}

							}

						}

					}

				}

			}

		}

		$this->LOCAL_LANG_loaded = 1;

	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ig_ldap_sso_auth/pi1/class.tx_igldapssoauth_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ig_ldap_sso_auth/pi1/class.tx_igldapssoauth_pi1.php']);
}

?>