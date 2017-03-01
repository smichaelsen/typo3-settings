<?php
namespace Smichaelsen\Settings\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Smichaelsen\Settings\Service\ConfigurationService;
use TYPO3\CMS\Backend\Controller\EditDocumentController;
use TYPO3\CMS\Backend\Form\Exception\AccessDeniedException;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\Utility\FormEngineUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

class FormController extends EditDocumentController
{

    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->preInit();
        $this->init();
        $currentPage = BackendUtility::getRecord('pages', GeneralUtility::_GP('id'));
        if ($currentPage['is_siteroot']) {
            $this->showForm();
        } else {
            $this->showRootPageSelector();
        }
        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
    }

    protected function showForm()
    {
        $forms = GeneralUtility::_POST('data')['tx_settings_form'];
        if (is_array($forms)) {
            $submittedFormData = $forms[array_keys($forms)[0]];
            $pid = $submittedFormData['pid'];
            unset($submittedFormData['pid']);
            foreach ($submittedFormData as $fieldName => $fieldValue) {
                $this->getConfigurationService()->set($pid, $fieldName, $fieldValue);
            }
        }
        $this->main();
    }

    public function main()
    {
        $this->editconf = [
            'tx_settings_form' => [
                0 => 'new',
            ],
        ];
        parent::main();
    }

    /**
     * Copied over from parent class with slight modification (-> see ###MODIFICATION###)
     * Unfortunately there is no hook or little method to replace
     *
     * @return string HTML form elements wrapped in tables
     */
    public function makeEditForm()
    {
        // Initialize variables:
        $this->elementsData = [];
        $this->errorC = 0;
        $this->newC = 0;
        $editForm = '';
        $trData = null;
        $beUser = $this->getBackendUser();
        // Traverse the GPvar edit array
        // Tables:
        foreach ($this->editconf as $table => $conf) {
            if (is_array($conf) && $GLOBALS['TCA'][$table] && $beUser->check('tables_modify', $table)) {
                // Traverse the keys/comments of each table (keys can be a commalist of uids)
                foreach ($conf as $cKey => $command) {
                    if ($command == 'edit' || $command == 'new') {
                        // Get the ids:
                        $ids = GeneralUtility::trimExplode(',', $cKey, true);
                        // Traverse the ids:
                        foreach ($ids as $theUid) {
                            // Don't save this document title in the document selector if the document is new.
                            if ($command === 'new') {
                                $this->dontStoreDocumentRef = 1;
                            }

                            /** @var TcaDatabaseRecord $formDataGroup */
                            $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
                            /** @var FormDataCompiler $formDataCompiler */
                            $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);
                            /** @var NodeFactory $nodeFactory */
                            $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);

                            try {
                                // Reset viewId - it should hold data of last entry only
                                $this->viewId = 0;
                                $this->viewId_addParams = '';

                                $formDataCompilerInput = [
                                    'tableName' => $table,
                                    'vanillaUid' => (int)$theUid,
                                    'command' => $command,
                                    'returnUrl' => $this->R_URI,
                                ];
                                if (is_array($this->overrideVals) && is_array($this->overrideVals[$table])) {
                                    $formDataCompilerInput['overrideValues'] = $this->overrideVals[$table];
                                }

                                $formData = $formDataCompiler->compile($formDataCompilerInput);

                                // Set this->viewId if possible
                                if ($command === 'new'
                                    && $table !== 'pages'
                                    && !empty($formData['parentPageRow']['uid'])
                                ) {
                                    $this->viewId = $formData['parentPageRow']['uid'];
                                } else {
                                    if ($table == 'pages') {
                                        $this->viewId = $formData['databaseRow']['uid'];
                                    } elseif (!empty($formData['parentPageRow']['uid'])) {
                                        $this->viewId = $formData['parentPageRow']['uid'];
                                        // Adding "&L=xx" if the record being edited has a languageField with a value larger than zero!
                                        if (!empty($formData['processedTca']['ctrl']['languageField'])
                                            && is_array($formData['databaseRow'][$formData['processedTca']['ctrl']['languageField']])
                                            && $formData['databaseRow'][$formData['processedTca']['ctrl']['languageField']][0] > 0
                                        ) {
                                            $this->viewId_addParams = '&L=' . $formData['databaseRow'][$formData['processedTca']['ctrl']['languageField']][0];
                                        }
                                    }
                                }

                                // Determine if delete button can be shown
                                $deleteAccess = false;
                                if ($command === 'edit') {
                                    $permission = $formData['userPermissionOnPage'];
                                    if ($formData['tableName'] === 'pages') {
                                        $deleteAccess = $permission & Permission::PAGE_DELETE ? true : false;
                                    } else {
                                        $deleteAccess = $permission & Permission::CONTENT_EDIT ? true : false;
                                    }
                                }

                                // Display "is-locked" message:
                                if ($command === 'edit') {
                                    $lockInfo = BackendUtility::isRecordLocked($table, $formData['databaseRow']['uid']);
                                    if ($lockInfo) {
                                        /** @var $flashMessage \TYPO3\CMS\Core\Messaging\FlashMessage */
                                        $flashMessage = GeneralUtility::makeInstance(
                                            FlashMessage::class,
                                            $lockInfo['msg'],
                                            '',
                                            FlashMessage::WARNING
                                        );
                                        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
                                        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                                        /** @var $defaultFlashMessageQueue FlashMessageQueue */
                                        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
                                        $defaultFlashMessageQueue->enqueue($flashMessage);
                                    }
                                }

                                // Record title
                                if (!$this->storeTitle) {
                                    $this->storeTitle = $this->recTitle
                                        ? htmlspecialchars($this->recTitle)
                                        : BackendUtility::getRecordTitle($table, FormEngineUtility::databaseRowCompatibility($formData['databaseRow']), true);
                                }

                                $this->elementsData[] = [
                                    'table' => $table,
                                    'uid' => $formData['databaseRow']['uid'],
                                    'pid' => $formData['databaseRow']['pid'],
                                    'cmd' => $command,
                                    'deleteAccess' => $deleteAccess
                                ];

                                if ($command !== 'new') {
                                    BackendUtility::lockRecords($table, $formData['databaseRow']['uid'], $table === 'tt_content' ? $formData['databaseRow']['pid'] : 0);
                                }

                                // Set list if only specific fields should be rendered. This will trigger
                                // ListOfFieldsContainer instead of FullRecordContainer in OuterWrapContainer
                                if ($this->columnsOnly) {
                                    if (is_array($this->columnsOnly)) {
                                        $formData['fieldListToRender'] = $this->columnsOnly[$table];
                                    } else {
                                        $formData['fieldListToRender'] = $this->columnsOnly;
                                    }
                                }

                                ### MODIFICATION ###
                                ### Prefill with existing data ###
                                $formData['databaseRow'] = array_merge($formData['databaseRow'], $this->getConfigurationService()->getAllConfiguration(GeneralUtility::_GP('id')));
                                ### Set effective pid
                                $formData['effectivePid'] = GeneralUtility::_GP('id');
                                $formData['databaseRow']['pid'] = GeneralUtility::_GP('id');

                                $formData['renderType'] = 'outerWrapContainer';
                                $formResult = $nodeFactory->create($formData)->render();

                                $html = $formResult['html'];

                                $formResult['html'] = '';
                                $formResult['doSaveFieldName'] = 'doSave';

                                // @todo: Put all the stuff into FormEngine as final "compiler" class
                                // @todo: This is done here for now to not rewrite JStop()
                                // @todo: and printNeededJSFunctions() now
                                $this->formResultCompiler->mergeResult($formResult);

                                // Seems the pid is set as hidden field (again) at end?!
                                if ($command == 'new') {
                                    // @todo: looks ugly
                                    $html .= LF
                                        . '<input type="hidden"'
                                        . ' name="data[' . htmlspecialchars($table) . '][' . htmlspecialchars($formData['databaseRow']['uid']) . '][pid]"'
                                        . ' value="' . (int)$formData['databaseRow']['pid'] . '" />';
                                    $this->newC++;
                                }

                                $editForm .= $html;
                            } catch (AccessDeniedException $e) {
                                $this->errorC++;
                                // Try to fetch error message from "recordInternals" be user object
                                // @todo: This construct should be logged and localized and de-uglified
                                $message = $beUser->errorMsg;
                                if (empty($message)) {
                                    // Create message from exception.
                                    $message = $e->getMessage() . ' ' . $e->getCode();
                                }
                                $editForm .= $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:labels.noEditPermission', true)
                                    . '<br /><br />' . htmlspecialchars($message) . '<br /><br />';
                            }
                        } // End of for each uid
                    }
                }
            }
        }
        return $editForm;
    }

    /**
     * @return array
     */
    protected function getFormDataCompilerInput()
    {
        return [
            'tableName' => 'tx_settings_form',
            'vanillaUid' => 0,
            'command' => 'new',
            'returnUrl' => '',
        ];
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

    protected function showRootPageSelector()
    {
        $rootPages = $this->getDatabaseConnection()->exec_SELECTgetRows(
            '*', 'pages', 'is_siteroot = 1 AND deleted = 0'
        );
        if (count($rootPages) === 1) {
            $urlParameters = [
                'id' => $rootPages[0]['uid']
            ];
            $aHref = BackendUtility::getModuleUrl('web_settings', $urlParameters);
            HttpUtility::redirect($aHref);
        } else {
            if (count($rootPages) === 0) {
                $content = '<h2>No root pages</h2>';
                $content .= '<p>Website settings can only be applied to root pages. None have been found in this installation.</p>';
            } else {
                $content = '<h2>Choose root page</h2>';
                $content .= '<p>Website settings can only be applied to root pages. Choose one of the following:</p>';
                $content .= '<ul>';
                foreach ($rootPages as $rootPage) {
                    $urlParameters = [
                        'id' => $rootPage['uid']
                    ];
                    $aHref = BackendUtility::getModuleUrl('web_settings', $urlParameters);
                    $content .= '<li><a href="' . $aHref . '">' . $rootPage['title'] . '</a></li>';
                }
                $content .= '</ul>';
            }
            $this->moduleTemplate->setContent($content);
        }
    }

}
