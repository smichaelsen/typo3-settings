<?php
namespace Smichaelsen\Settings\Controller;

use Smichaelsen\Settings\Service\ConfigurationService;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Form\FormResultCompiler;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FormController extends ActionController
{

    /**
     *
     */
    public function showAction()
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->setCharSet('utf-8');
        $pageRenderer->setLanguage($GLOBALS['LANG']->lang);
        $this->view->assignMultiple([
            'header' => $pageRenderer->render(PageRenderer::PART_HEADER),
            'form' => $this->generateForm(),
            'footer' => $pageRenderer->render(PageRenderer::PART_FOOTER),
        ]);
    }

    /**
     *
     */
    public function saveAction()
    {
        $forms = GeneralUtility::_POST('data')['tx_settings_form'];
        foreach ($forms[array_keys($forms)[0]] as $fieldName => $fieldValue) {
            $this->getConfigurationService()->set($fieldName, $fieldValue);
        }
        $this->redirect('show');
    }

    /**
     * @return string
     */
    protected function generateForm()
    {
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, GeneralUtility::makeInstance(TcaDatabaseRecord::class));
        $formDataCompilerInput = [
            'tableName' => 'tx_settings_form',
            'vanillaUid' => 0,
            'command' => 'new',
            'returnUrl' => '',
        ];
        $formData = $formDataCompiler->compile($formDataCompilerInput);
        $formData['renderType'] = 'outerWrapContainer';
        $formData['databaseRow'] = array_merge($formData['databaseRow'], $this->getConfigurationService()->getAllConfiguration());
        $form = GeneralUtility::makeInstance(NodeFactory::class)->create($formData)->render()['html'];
        $form .= GeneralUtility::makeInstance(FormResultCompiler::class)->printNeededJSFunctions();
        return $form;
    }

    /**
     * @return ConfigurationService
     */
    protected function getConfigurationService()
    {
        static $configurationService;
        if (!$configurationService instanceof ConfigurationService) {
            $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        }
        return $configurationService;
    }

}
