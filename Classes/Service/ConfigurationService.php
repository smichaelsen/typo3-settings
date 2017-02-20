<?php
namespace Smichaelsen\Settings\Service;

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService implements SingletonInterface
{

    const REGISTRY_NAMESPACE = 'Smichaelsen\\Settings';

    /**
     * @return array
     */
    public function getAllConfiguration()
    {
        static $configuration;
        if (!is_array($configuration)) {
            $configuration = [];
            foreach ($GLOBALS['TCA']['tx_settings_form']['columns'] as $columnKey => $columnConfiguration) {
                $value = $this->getRegistry()->get(self::REGISTRY_NAMESPACE, $columnKey);
                $isMultiValueField = in_array($columnConfiguration['config']['type'], ['select', 'group']);
                if ($isMultiValueField && empty($value)) {
                    $value = [];
                }
                $configuration[$columnKey] = $value;
            }
        }
        return $configuration;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->getRegistry()->set(self::REGISTRY_NAMESPACE, $key, $value);
    }

    public function injectTypoScriptConstants()
    {
        static $isInjected = false;
        if (!$isInjected) {
            if (count($this->getAllConfiguration())) {
                $constants = 'plugin.tx_settings {' . LF;
                foreach ($this->getAllConfiguration() as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }
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
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
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
