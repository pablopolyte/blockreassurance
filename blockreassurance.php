<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_.'/blockreassurance/classes/ReassuranceActivity.php';

class blockreassurance extends Module implements WidgetInterface
{
    public $name;
    public $version;
    public $author;
    public $need_instance;
    public $module_key;
    public $author_address;
    public $controller_name;
    public $bootstrap;
    public $displayName;
    public $description;
    public $js_path;
    public $css_path;
    public $img_path;
    public $img_path_perso;
    public $lib_path;
    public $docs_path;
    public $logo_path;
    public $module_path;
    public $confirmUninstall;
    public $ps_url;
    public $folder_file_upload;

    private $templateFile;

    public function __construct()
    {
        // Settings
        $this->name = 'blockreassurance';
        $this->tab = 'seo';
        $this->version = '4.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->module_key = '938b96386d4d79aa7cb891439cb0ef11';
        $this->author_address = '0x64aa3c1e4034d07015f639b0e171b0d7b27d01aa';
        // Controllers
        //$this->controller_name = '';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('blockreassurance', array(), 'Modules.Blockreassurance.Admin');
        $this->description = $this->trans('Connect with your customers and reassure them by highlighting your services: secure payment, free shipping, returns, etc.', array(), 'Modules.Blockreassurance.Admin');

        // Settings paths
        $this->js_path = $this->_path.'views/js/';
        $this->css_path = $this->_path.'views/css/';
        $this->img_path = $this->_path.'views/img/';
        $this->old_path_img = $this->_path.'img/';
        $this->img_path_perso = $this->img_path.'img_perso';
        $this->lib_path = $this->_path.'views/lib/';
        $this->docs_path = $this->_path.'docs/';
        $this->logo_path = $this->_path.'logo.png';
        $this->module_path = $this->_path;
        $this->folder_file_upload = _PS_MODULE_DIR_. $this->name. '/views/img/img_perso/';

        // Confirm uninstall
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', array(), 'Modules.Blockreassurance.Admin');
        $this->ps_url = Tools::getCurrentUrlProtocolPrefix().htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;

        $this->ps_versions_compliancy = array('min' => '1.7.5.0', 'max' => _PS_VERSION_);
        $this->templateFile = 'module:blockreassurance/views/templates/hook/blockreassurance.tpl';
    }

    /**
     * install()
     *
     * @param none
     * @return bool
     */
    public function install()
    {
        Configuration::updateValue('PSR_HOOK_HEADER', '0');
        Configuration::updateValue('PSR_HOOK_FOOTER', '0');
        Configuration::updateValue('PSR_HOOK_PRODUCT', '1');
        Configuration::updateValue('PSR_HOOK_CHECKOUT', '1');
        Configuration::updateValue('PSR_ICON_COLOR', '#F19D76');
        Configuration::updateValue('PSR_TEXT_COLOR', '#000000');

        $languages = Language::getLanguages(false);
        include(dirname(__FILE__).'/sql/install.php');

        // register hook used by the module
        if (parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayNavFullWidth') &&
            $this->registerHook('displayFooterAfter') &&
            $this->registerHook('displayFooterBefore') &&
            $this->registerHook('displayReassurance') &&
            $this->registerHook('actionFrontControllerSetMedia')
        ) {
            return true;
        }

        $this->_errors[] = $this->trans('There was an error during the installation. Please contact us through Addons website.', array(), 'Modules.Blockreassurance.Admin');
        return false;
    }

    /**
     * uninstall()
     *
     * @param none
     * @return bool
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        Configuration::deleteByName('PSR_HOOK_HEADER');
        Configuration::deleteByName('PSR_HOOK_FOOTER');
        Configuration::deleteByName('PSR_HOOK_PRODUCT');
        Configuration::deleteByName('PSR_HOOK_CHECKOUT');
        Configuration::deleteByName('PSR_ICON_COLOR');
        Configuration::deleteByName('PSR_TEXT_COLOR');

        if (parent::uninstall()) {
            return true;
        }

        $this->_errors[] = $this->trans('There was an error during the uninstallation. Please contact us through Addons website.', array(), 'Modules.Blockreassurance.Admin');
        return false;
    }

    /**
     * load dependencies
     */
    public function loadAsset()
    {
        $this->addJsDefList();

        $controller = $this->context->controller;

        $aCss = array(
            $this->lib_path.'pickr/css/pickr.min.css',
            $this->lib_path.'pickr/css/pickr-override.css',
            $this->css_path.'/templates/display.css',
            $this->css_path.'/templates/config.css',
            $this->css_path.'/templates/listing.css',
            $this->css_path.'/templates/reassurance_block.css',
            $this->css_path.'/templates/appearance.css',
            $this->css_path.'style.css',
            $this->css_path.'faq.css',
            $this->css_path.'menu.css',
        );

        $aJs = array(
            $this->lib_path.'/pickr/js/pickr.js',
            $this->js_path.'/appearance/colorpicker.js',
            $this->js_path.'back.js',
            $this->js_path.'menu.js',
            $this->js_path.'vue.min.js',
        );

        $aCss[] = '//fonts.googleapis.com/icon?family=Material+Icons';

        $this->context->controller->addCSS($aCss, 'all');
        $this->context->controller->addJS($aJs);
        $controller->addJqueryPlugin('colorpicker');
        $controller->addJqueryUI('ui.sortable');
    }

    /**
     * Check if folder img_perso is writable and executable
     *
     * @return void
     */
    private function folderUploadFilesHasGoodRights()
    {
        return is_writable($this->folder_file_upload)
            && is_executable($this->folder_file_upload);
    }

    /**
     * getContent
     *
     *
     * @return template
    */
    public function getContent()
    {
        $this->loadAsset();

        $id_lang = $this->context->language->id;
        $currentPage = Tools::getValue('page');

        // get current page
        if ($currentPage != 'global') {
             $currentPage = 'global';
        }

        $moduleAdminLink = Context::getContext()->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&module_name='.$this->name;

        $allCms = CMS::listCms($id_lang);

        $this->context->smarty->assign(array(
            'psr_hook_header' => Configuration::get('PSR_HOOK_HEADER'),
            'psr_hook_footer' => Configuration::get('PSR_HOOK_FOOTER'),
            'psr_hook_product' => Configuration::get('PSR_HOOK_PRODUCT'),
            'psr_hook_checkout' => Configuration::get('PSR_HOOK_CHECKOUT'),
            'psr_text_color' => Configuration::get('PSR_TEXT_COLOR'),
            'psr_icon_color' => Configuration::get('PSR_ICON_COLOR'),
            'logo_path' => $this->logo_path,
            'guide_link' => $this->ps_url.'modules/'.$this->name.'/docs/doc_psreassurance_'.$this->context->language->iso_code.'.pdf',
            'languages' => Language::getLanguages(),
            'allblock' => ReassuranceActivity::getAllBlockByLang($id_lang, $this->context->shop->id),
            'allblockByShop' => ReassuranceActivity::getAllBlockByShop(),
            'currentPage' => $currentPage,
            'moduleAdminLink' => $moduleAdminLink,
            'img_path' => $this->img_path,
            'allCms' => $allCms,
            'defaultFormLanguage' => (int) $this->context->employee->id_lang,
            'img_url' => $this->img_path,
            'old_img_url' => $this->old_path_img,
            'folderIsWritable' => $this->folderUploadFilesHasGoodRights(),
            'folderPath' => $this->img_path_perso,
        ));

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * addJsDefList
     *
     * @return void
     */
    protected function addJsDefList()
    {
        Media::addJsDef(array(
            'psr_icon_color' => Configuration::get('PSR_ICON_COLOR'),
            'psr_text_color' => Configuration::get('PSR_TEXT_COLOR'),
            'psr_controller_block_url' => $this->context->link->getAdminLink('AdminBlockListing'),
            'psr_controller_block' => 'AdminBlockListing',

            'block_updated' => $this->trans('Block updated', array(), 'Modules.Blockreassurance.Admin'),
            'active_error' => $this->trans('Oops... looks like an error occurred', array(), 'Modules.Blockreassurance.Admin'),
            'psre_success' => $this->trans('Configuration updated successfully!', array(), 'Modules.Blockreassurance.Admin'),
            'successPosition' => $this->trans('Position changed successfully!', array(), 'Modules.Blockreassurance.Admin'),
            'errorPosition' => $this->trans('An error occurred when switching position', array(), 'Modules.Blockreassurance.Admin'),
        ));
    }

    public function hookdisplayHeader($params)
    {
        $actif = Configuration::get('PSR_HOOK_HEADER');

        // If position not equals to 2 (Above header)
        if ($actif != 2) {
            return;
        }

        $this->renderTemplateInHook('displayBlock.tpl');
    }

    public function hookdisplayNavFullWidth($params)
    {
        $actif = Configuration::get('PSR_HOOK_HEADER');

        // If position not equals to 2 (Below header)
        if ($actif != 1) {
            return;
        }

        $this->renderTemplateInHook('displayBlock.tpl');
    }

    public function hookdisplayFooterAfter($params)
    {
        $actif = Configuration::get('PSR_HOOK_FOOTER');

        // If position not equals to 2 (Below header)
        if ($actif != 1) {
            return;
        }

        $this->renderTemplateInHook('displayBlockWhite.tpl');
    }

    public function hookdisplayFooterBefore($params)
    {
        $actif = Configuration::get('PSR_HOOK_FOOTER');

        // If position not equals to 2 (Above header)
        if ($actif != 2) {
            return;
        }

        $this->renderTemplateInHook('displayBlockWhite.tpl');
    }

    public function hookdisplayReassurance($params)
    {
        $actifCheckout = Configuration::get('PSR_HOOK_CHECKOUT');
        $actifProduct = Configuration::get('PSR_HOOK_PRODUCT');
        $controller = Tools::getValue('controller');

        if (!$this->weDisplayOnBlockProduct($actifCheckout, $actifProduct, $controller)) {
            return false;
        }

        $this->renderTemplateInHook('displayBlockProduct.tpl');
    }

    /**
     * Check if we can display the hook on product page or cart page.
     * The HOOK must be active
     *
     * @param  int $actifCheckout
     * @param  int $actifProduct
     * @param  string $controller
     *
     * @return bool
     */
    private function weDisplayOnBlockProduct($actifCheckout, $actifProduct, $controller)
    {
        if ($actifProduct == 1 && $controller == 'product') {
            return true;
        }

        if ($actifCheckout == 1 && $controller == 'cart') {
            return true;
        }

        return false;
    }

    /**
     * Assign smarty variables and display the hook
     *
     * @param  string $template
     *
     * @return void
     */
    private function renderTemplateInHook($template)
    {
        $id_lang = $this->context->language->id;

        $this->context->smarty->assign(array(
            'blocks' => ReassuranceActivity::getAllBlockByStatus($id_lang, $this->context->shop->id),
            'iconeColor' => Configuration::get('PSR_ICON_COLOR'),
            'textColor' => Configuration::get('PSR_TEXT_COLOR'),
        ));

        echo $this->display(__FILE__, 'views/templates/hook/'.$template);
    }

    public function hookActionFrontControllerSetMedia()
    {
        // $current_page = $this->context->controller->php_self;
        Media::addJsDef(array(
            'psr_icon_color' => Configuration::get('PSR_ICON_COLOR'),
        ));
        // if ($current_page != 'index') {
        //     return false;
        // }

        $this->context->controller->registerJavascript(
            'front',
            'modules/'.$this->name.'/views/js/front.js'
        );
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('blockreassurance'))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('blockreassurance'));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return array(
            'blocks' => ReassuranceActivity::getAllBlockByStatus($this->context->language->id, $this->context->shop->id),
        );
    }
}
