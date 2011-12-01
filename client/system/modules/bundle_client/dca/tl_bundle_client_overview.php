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

    public function onload_callback(DataContainer $dc)
    {
        if (strlen($this->Input->post("submit_bundle_install")) == 0)
        {
            foreach ($_POST as $hashKey => $hashValue)
            {
                if (strpos($hashKey, "bundle_hash_") !== false)
                {
                    $strHash = array_reverse(explode("_", $hashKey));
                    $this->installBundle($dc, $strHash[0]);
                }
            }
        }


        $objRequest = new Request();
        $strRequestUrl = $GLOBALS['TL_CONFIG']['bundle_server_requestUrl'] . 'bundle-list.xml';

        $objRequest->send($strRequestUrl);

        if ($objRequest->hasError())
        {
            $dc->setData("bundles_list", $GLOBALS['TL_LANG']['ERR']['no_bundle_server']);
            return;
        }


        $strBundleList = $objRequest->response;

        $objXml = simplexml_load_string($strBundleList);
        $arrPackages = array();
        foreach ($objXml->packages->package as $package)
        {
            $arrPackages[] = $package;
        }

        $arrList = array();

        $perPage = 5;
        $total = count($arrPackages);
        $page = $this->Input->get('page') ? $this->Input->get('page') : 1;
        $offset = ($page - 1) * $perPage;
        $limit = min($perPage + $offset, $total);

        $objPagination = new Pagination($total, $perPage);
        $arrList[] = $objPagination->generate("\n  ");

        for ($i = $offset; $i < $limit; $i++)
        {
            $package = $arrPackages[$i];

            $formId = "bundleinstall_" . $package->name;

            $arrList[] = sprintf('
<li>
	<div class="install_info">
	%s
	<br>
	%s
	</div>
	<div class="install_button">
<input type="submit" class="tl_submit" value="%s" name="bundle_hash_%s" />

</li>', $package->extensions, Repository::formatCoreVersion($package->min_version) . ' ' . $GLOBALS['TL_LANG']['MSC']['version_to'] . ' ' . Repository::formatCoreVersion($package->max_version), $GLOBALS['TL_LANG']['tl_bundle_client_overview']['bundles_list'][2], $package->name
            );
        }

        $dc->setData("bundles_list", '<ul>' . implode($arrList) . '</ul>');
    }

    protected function installBundle($dc, $strBundleHash)
    {
        $objRequest = new Request();
        $strRequestUrl = $GLOBALS['TL_CONFIG']['bundle_server_requestUrl'] . 'bundles/' . $strBundleHash . '.xml';

        $objRequest->send($strRequestUrl);

        if ($objRequest->hasError())
        {
            $dc->setData("bundles_list", $GLOBALS['TL_LANG']['ERR']['no_bundle_server']);
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

        die();
    }

    public function onsubmit_callback(DataContainer $dc)
    {

        if ($this->Input->post("submit_bundle_install"))
        {
            $this->installBundle($dc, $dc->getData("bundle_install"));
        }
    }

    public function showAll($dc, $strReturn)
    {
        return $strReturn . $dc->edit();
    }

}

?>