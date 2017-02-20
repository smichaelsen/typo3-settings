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
     * @var FormDataCompiler
     */
    protected $formDataCompiler;

    /**
     * @var FormResultCompiler
     */
    protected $formResultCompiler;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @param FormResultCompiler $formResultCompiler
     */
    public function injectFormResultCompiler(FormResultCompiler $formResultCompiler)
    {
        $this->formResultCompiler = $formResultCompiler;
    }

    /**
     * @param PageRenderer $pageRenderer
     */
    public function injectPageRenderer(PageRenderer $pageRenderer)
    {
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * @param NodeFactory $nodeFactory
     */
    public function injectNodeFactory(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    /**
     *
     */
    public function showAction()
    {
        $this->pageRenderer->setCharSet('utf-8');
        $this->pageRenderer->setLanguage($GLOBALS['LANG']->lang);
        $this->view->assignMultiple([
            'header' => $this->pageRenderer->render(PageRenderer::PART_HEADER),
            'form' => $this->generateForm(),
            'footer' => $this->pageRenderer->render(PageRenderer::PART_FOOTER),
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
        $formDataCompilerInput = [
            'tableName' => 'tx_settings_form',
            'vanillaUid' => 0,
            'command' => 'new',
            'returnUrl' => '',
        ];
        $formData = $this->getFormDataCompiler()->compile($formDataCompilerInput);
        $formData['renderType'] = 'outerWrapContainer';
        $formData['databaseRow'] = array_merge($formData['databaseRow'], $this->getConfigurationService()->getAllConfiguration());
        $form = $this->nodeFactory->create($formData)->render()['html'];
        $form .= $this->formResultCompiler->printNeededJSFunctions();
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

    /**
     * @return FormDataCompiler
     */
    protected function getFormDataCompiler()
    {
        if (!$this->formDataCompiler instanceof FormDataCompiler) {
            $this->formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, GeneralUtility::makeInstance(TcaDatabaseRecord::class));
        }
        return $this->formDataCompiler;
    }

}
