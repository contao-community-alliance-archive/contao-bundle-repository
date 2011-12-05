<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Stefan Lindecke 2011, MEN AT WORK 2011 
 * @package    bundle_client
 * @license    GNU/LGPL 
 * @filesource
 */
/**
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_bundle_client_overview'] = array(
    // Config
    'config' => array(
        'dataContainer' => 'Memory',
        'closed' => true,
        'disableSubmit' => true,
        'onload_callback' => array(
            array(
                'tl_bundle_client_overview',
                'onload_callback'
            ),
        ),
        'onsubmit_callback' => array(
            array(
                'tl_bundle_client_overview',
                'onsubmit_callback'
            ),
        ),
        'dcMemory_show_callback' => array(
            array(
                'tl_bundle_client_overview',
                'showAll'
            ),
        ),
        'dcMemory_showAll_callback' => array(
            array(
                'tl_bundle_client_overview',
                'showAll'
            ),
        ),
    ),
    // Palettes
    'palettes' => array(
        'default' => '{legend_manuell},bundle_install;{legend_bundlelist},bundles_list',
    ),
    'subpalettes' => array(),
    // Fields
    'fields' => array
        (
        'bundle_install' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_bundle_client_overview']['bundle_install'],
            'exclude' => true,
            'inputType' => 'text',
			'eval' => array('tl_class' => 'long'),
            'addSubmit' => true
        ),
        'bundles_list' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_bundle_client_overview']['bundles_list'],
            'exclude' => true,
            'inputType' => 'statictext',
        ),
    )
);

class tl_bundle_client_overview extends Backend
{

    public function __construct()
    {
        $this->import("Config");
        $this->import("Input");
        $this->import("Database");
        $this->import("BackendUser", "User");
    }

    public function showAll($dc, $strReturn)
    {
        return $strReturn . $dc->edit();
    }

    public function onsubmit_callback(DataContainer $dc)
    {
        if ($this->Input->post("submit_bundle_install"))
        {
            $this->installBundle($dc, $dc->getData("bundle_install"));
        }
    }

    /**
     * Build packages overview or start bundle installation
     * 
     * @param DataContainer $dc
     * @return void 
     */
    public function onload_callback(DataContainer $dc)
    {
        // Check if install button was clicked
        if (strlen($this->Input->post("FORM_SUBMIT")) != 0 && strlen($this->Input->post("submit_bundle_install")) == 0)
        {
            foreach ($_POST as $hashKey => $hashValue)
            {                
                if (strpos($hashKey, "bundle_hash_") !== false)
                {
                    $arrHash = array_reverse(explode("_", $hashKey));
                    $this->installBundle($dc, $arrHash[0]);
                }
            }
        }

        // Build Request with cache for 5 minute
        $objRequest = new RequestExtendedCached();
        $objRequest->cacheTime = 1;
        $objRequest->noCache = true;

        $strRequestUrl = $GLOBALS['TL_CONFIG']['bundle_server_requestUrl'] . 'bundles/bundle-list.xml';
        $objRequest->send($strRequestUrl);

        // Check if we have a error here 
        if ($objRequest->hasError())
        {
            $dc->setData("bundles_list", $GLOBALS['TL_LANG']['ERR']['no_bundle_server']);
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['no_bundle_server'];
            return;
        }

        // Get XML with pachages
        $strBundleList = $objRequest->response;

        // Read xml
        $objXml = simplexml_load_string($strBundleList);
        $arrPackages = array();

        foreach ($objXml->packages->package as $package)
        {
            $arrPackages[] = (array) $package;
        }

        // Pagination parameter
        $intPage = strlen($this->Input->get('page')) != 0 ? (int) $this->Input->get('page') : 1;
        $intMaxElementsPerPage = 15;
        $intTotal = count($arrPackages);

        // Build pagination
        $objPagination = new Pagination($intTotal, $intMaxElementsPerPage);

        // Slice array        
        $arrPackages = array_slice($arrPackages, ($intPage - 1) * $intMaxElementsPerPage, $intMaxElementsPerPage);

        foreach ($arrPackages as $key => $value)
        {
            $arrPackages[$key]["min_version_readable"] = Repository::formatCoreVersion($value["min_version"]);
            $arrPackages[$key]["max_version_readable"] = Repository::formatCoreVersion($value["max_version"]);
            $arrPackages[$key]["form_id"] = "bundleinstall_" . $value["name"];
        }

        // Build Template
        $objTemplate = new BackendTemplate("be_packages_entries");
        $objTemplate->packages = $arrPackages;
        $objTemplate->send = $GLOBALS['TL_LANG']['tl_bundle_client_overview']['bundles_list'][2];
        $objTemplate->pagination = $objPagination->generate(" ");

        // Set data
        $dc->setData("bundles_list", $objTemplate->parse());
    }

    protected function installBundle($dc, $strBundleHash)
    {         
        $objRequest = new Request();
        $strRequestUrl = $GLOBALS['TL_CONFIG']['bundle_server_requestUrl'] . 'bundles/' . $strBundleHash . '.xml';

        $objRequest->send($strRequestUrl);

        if ($objRequest->hasError())
        {
            $_SESSION["TL_ERROR"][] = $GLOBALS['TL_LANG']['ERR']['no_bundle_xml'];
            return;
        }

        $strBundleList = $objRequest->response;
        
        $objXml = simplexml_load_string($strBundleList);

        $arrIds = array();

        foreach ($objXml->extensions->extension as $extension)
        {

            $objExt = new libContaoConnector("tl_repository_installs", "extension", (string) $extension->name);
            $objExt->version = (string) $extension->version;
            $objExt->build = (string) $extension->build;
            $objExt->Sync();

            $arrIds[] = $objExt->id;
        }


        $strUrl = $this->Environment->base . 'main.php?do=repository_manager&upgrade=' . implode(",", $arrIds);
        $this->redirect($strUrl);
    }

}

?>