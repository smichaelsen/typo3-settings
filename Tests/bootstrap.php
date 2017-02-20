<?php
namespace Smichaelsen\Settings\Tests\Unit;

use TYPO3\CMS\Core\Core\Bootstrap;

/**
 * This file is defined in UnitTests.xml and called by phpunit
 * before instantiating the test suites, it must also be included
 * with phpunit parameter --bootstrap if executing single test case classes.
 */
class UnitTestsBootstrap
{
    /**
     * Bootstraps the system for unit tests.
     *
     * @return void
     */
    public function bootstrapSystem()
    {
        $this->enableDisplayErrors()
            ->checkForCliDispatch()
            ->defineSitePath()
            ->setTypo3Context()
            ->createNecessaryDirectoriesInDocumentRoot()
            ->includeAndStartCoreBootstrap()
            ->initializeConfiguration()
            ->finishCoreBootstrap();
    }

    /**
     * Makes sure error messages during the tests get displayed no matter what is set in php.ini.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function enableDisplayErrors()
    {
        @ini_set('display_errors', 1);
        return $this;
    }

    /**
     * Checks whether the tests are run using the CLI dispatcher. If so, echos a helpful message and exits with
     * an error code 1.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function checkForCliDispatch()
    {
        if (!defined('TYPO3_MODE')) {
            return $this;
        }

        array_shift($_SERVER['argv']);
        $flatArguments = implode(' ', $_SERVER['argv']);
        echo 'Please run the unit tests using the following command:' . chr(10) .
            sprintf('typo3/../bin/phpunit %s', $flatArguments) . chr(10) .
            chr(10);

        exit(1);
    }

    /**
     * Defines the PATH_site and PATH_thisScript constant and sets $_SERVER['SCRIPT_NAME'].
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function defineSitePath()
    {
        /** @var string */
        define('PATH_site', $this->getWebRoot());

        /** @var string */
        define('PATH_thisScript', PATH_site . 'typo3/cli_dispatch.phpsh');
        $_SERVER['SCRIPT_NAME'] = PATH_thisScript;

        return $this;
    }

    /**
     * Returns the absolute path the TYPO3 document root.
     *
     * @return string the TYPO3 document root using Unix path separators
     */
    protected function getWebRoot()
    {
        return realpath(__DIR__ . '/../.Build/Web/') . '/';
    }

    /**
     * Defines TYPO3_MODE, TYPO3_cliMode and sets the environment variable TYPO3_CONTEXT.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function setTypo3Context()
    {
        /** @var string */
        define('TYPO3_MODE', 'BE');
        /** @var string */
        define('TYPO3_cliMode', true);
        putenv('TYPO3_CONTEXT=Testing');

        return $this;
    }

    /**
     * Creates the following directories in the TYPO3 document root:
     * - typo3conf
     * - typo3conf/ext
     * - typo3temp
     * - uploads
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function createNecessaryDirectoriesInDocumentRoot()
    {
        $this->createDirectory(PATH_site . 'uploads');
        $this->createDirectory(PATH_site . 'typo3temp');
        $this->createDirectory(PATH_site . 'typo3conf/ext');

        return $this;
    }

    /**
     * Creates the directory $directory (recursively if required).
     *
     * If $directory already exists, this method is a no-op.
     *
     * @param string $directory absolute path of the directory to be created
     * @return void
     * @throws \RuntimeException
     */
    protected function createDirectory($directory)
    {
        if (is_dir($directory)) {
            return;
        }
        @mkdir($directory, 0777, true);
        clearstatcache();
        if (!is_dir($directory)) {
            throw new \RuntimeException('Directory "' . $directory . '" could not be created', 1423043755);
        }
    }

    /**
     * Includes the Core Bootstrap class and calls its first few functions.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function includeAndStartCoreBootstrap()
    {
        $classLoaderFilepath = __DIR__ . '/../.Build/vendor/autoload.php';
        if (!file_exists($classLoaderFilepath)) {
            $this->exitWithMessage('ClassLoader can\'t be loaded:' . $classLoaderFilepath);
        }
        $classLoader = require $classLoaderFilepath;

        $bootstrap = Bootstrap::getInstance();
        $bootstrap->initializeClassLoader($classLoader);
        if (method_exists($bootstrap, 'setRequestType')) {
            $bootstrap->setRequestType(4);
        }
        $bootstrap->baseSetup();

        return $this;
    }

    /**
     * Provides the default configuration in $GLOBALS['TYPO3_CONF_VARS'].
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function initializeConfiguration()
    {
        $configurationManager = new \TYPO3\CMS\Core\Configuration\ConfigurationManager();
        $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

        // avoid failing tests that rely on HTTP_HOST retrieval
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';

        return $this;
    }

    /**
     * Finishes the last steps of the Core Bootstrap.
     *
     * @return UnitTestsBootstrap fluent interface
     */
    protected function finishCoreBootstrap()
    {
        Bootstrap::getInstance()
            ->disableCoreCache()
            ->initializeCachingFramework()
            ->initializePackageManagement(\TYPO3\CMS\Core\Package\UnitTestPackageManager::class)
            ->ensureClassLoadingInformationExists();

        return $this;
    }

    /**
     * Echo out a text message and exit with error code
     *
     * @param string $message
     */
    protected function exitWithMessage($message)
    {
        echo $message . "\n";
        exit(1);
    }
}

if (PHP_SAPI !== 'cli') {
    die('This script supports command line usage only. Please check your command.');
}
$bootstrap = new UnitTestsBootstrap();
$bootstrap->bootstrapSystem();
unset($bootstrap);
