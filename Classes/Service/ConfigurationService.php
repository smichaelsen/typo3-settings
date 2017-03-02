<?php
namespace Smichaelsen\Settings\Service;

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService implements SingletonInterface
{

    const REGISTRY_NAMESPACE = 'Smichaelsen\\Settings';

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param int
     * @return array
     */
    public function getAllConfiguration($pid)
    {
        if (!is_array($this->configuration)) {
            $this->configuration = [];
            if (is_array($GLOBALS['TCA']['tx_settings_form']['columns'])) {
                foreach ($GLOBALS['TCA']['tx_settings_form']['columns'] as $columnKey => $columnConfiguration) {
                    $value = $this->getRegistry()->get(self::REGISTRY_NAMESPACE . '\\' . (int) $pid, $columnKey);
                    $isMultiValueField = in_array($columnConfiguration['config']['type'], ['select', 'group']);
                    if ($isMultiValueField && empty($value)) {
                        $value = [];
                    }
                    $this->configuration[$columnKey] = $value;
                }
            }
        }
        return $this->configuration;
    }

    /**
     * @param int $pid
     * @param string $key
     * @param mixed $value
     */
    public function set($pid, $key, $value)
    {
        $this->configuration[$key] = $value;
        $this->getRegistry()->set(self::REGISTRY_NAMESPACE . '\\' . $pid, $key, $value);
    }

    /**
     * @param array $parameters
     * @param TemplateService $templateService
     */
    public function injectTypoScriptConstants($parameters = null, $templateService = null)
    {
        static $isInjected = false;
        if (!$isInjected) {
            $rootPageUid = $templateService->absoluteRootLine[0]['uid'];
            if (count($this->getAllConfiguration($rootPageUid))) {
                $constants = 'plugin.tx_settings {' . LF;
                foreach ($this->getAllConfiguration($rootPageUid) as $key => $value) {
                    if (is_array($value)) {
                        $value = join(',', $value);
                    }
                    if(strstr($value, PHP_EOL)) {
                        $constants .= $key . ' ( ' . LF . $value . LF . ')' . LF;
                    } else {
                        $constants .= $key . ' = ' . $value . LF;
                    }
                }
                $constants .= '}' . LF;
                ExtensionManagementUtility::addTypoScript(
                    'settings',
                    'constants',
                    $constants
                );
            }
            $isInjected = true;
        }
    }

    /**
     * @return Registry
     */
    protected function getRegistry()
    {
        static $registry;
        if (!$registry instanceof Registry) {
            $registry = GeneralUtility::makeInstance(Registry::class);
        }
        return $registry;
    }

}
