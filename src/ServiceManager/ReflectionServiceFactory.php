<?php

namespace DocBlockTags\ServiceManager;

use DocBlockTags\Reflection\ReflectionService;
use DocBlockTags\Reflection\ReflectionServiceInterface;
use TYPO3\Flow\Cache\Backend\FileBackend;
use TYPO3\Flow\Cache\Backend\NullBackend;
use TYPO3\Flow\Cache\Frontend\VariableFrontend;
use TYPO3\Flow\Core\ApplicationContext;
use TYPO3\Flow\Core\ClassLoader;
use TYPO3\Flow\Log\Logger;
use TYPO3\Flow\Package\PackageManager;
use TYPO3\Flow\Utility\Environment;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factors Typo3's ReflectionService in the absence of Typo's Object Framework.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 * 
 * @todo Make (configurable) services for all those different Typo3 components.
 */
class ReflectionServiceFactory implements FactoryInterface
{

    /**
     * Finds out the application environment.
     * @return string
     */
    public static function getAppEnv()
    {
        $possibleNames = array(
            'APP_ENV',
            'APPLICATION_ENV',
            'APP_ENVIRONMENT',
            'APPLICATION_ENVIRONMENT',
            'ENVIRONMENT',
        );
        
        $env = '';

        // Try all possible names for the application environment as constant and
        // environment variable.
        foreach ($possibleNames as $name) {
            if (defined($name)) {
                $env = constant($name);
            } elseif ($value = getenv($name)) {
                $env = $value;
            }
            if (isset($env)) {
                break;
            }
        }
        
        switch (strtolower(substr($env, 0, 3))) {
            case 'dev':
                $env = 'Development';
                break;
            
            case 'tes':
            case 'tst':
            case 'acc':
                $env = 'Testing';
                break;
            
            default:
            case 'pro':
                $env = 'Production';
                break;
        }

        // Default to production.
        return $env;
    }

    /**
     * Finds the application's root path according to Typo.
     * @return string
     */
    public static function findFlowRootPath()
    {
        // Try typo constant.
        if (defined('FLOW_PATH_ROOT')) {
            return FLOW_PATH_ROOT;
        }

        // Try Zend constant.
        if (defined('ROOT_PATH')) {
            return ROOT_PATH;
        }

        // Assume composer.json is in the app (or our) root.
        if ($composerJsonFile = static::findComposerJson()) {
            return dirname($composerJsonFile);
        }

        // Default to current working directory.
        return getcwd();
    }

    /**
     * Find the path where Typo3 Flow packages are stored.
     */
    public static function findFlowPkgPath()
    {
        // Try typo constant.
        if (defined('FLOW_PATH_PACKAGES')) {
            return FLOW_PATH_PACKAGES;
        }

        // Check composer for installer-paths.
        if ($composerJsonFile = static::findComposerJson()) {
            $composerJson = json_decode(file_get_contents($composerJsonFile));
            $composerDir = dirname($composerJsonFile);
            if (property_exists($composerJson, 'extra') && property_exists($composerJson->extra, 'installer-paths')) {
                foreach ($composerJson->extra->{'installer-paths'} as $dir => $typeArray) {
                    // E.g. "vendor/typo3/Packages/Application/{$name}/": ["type:typo3-flow-package"]
                    if ($typeArray[0] == 'type:typo3-flow-package') {
                        $packageDir = dirname(dirname($dir));
                        return $composerDir . $packageDir;
                    }
                }
            }
        }

        // Default to 'Packages' directory in typo root path.
        return static::findFlowRootPath() . '/Packages';
    }

    /**
     * Finds the nearest composer.json file up the directory tree.
     * @return string Null if not found.
     */
    protected static function findComposerJson()
    {
        // Go up the directory tree to find composer.json.
        $dir = __DIR__;
        do {
            $possibleComposerJsonPath = $dir . DIRECTORY_SEPARATOR . 'composer.json';
            if (file_exists($possibleComposerJsonPath)) {
                return $possibleComposerJsonPath;
            }
        } while (($previous = $dir) != ($dir = dirname($dir)));

        // Default to null.
        return null;
    }

    /**
     * @return ReflectionServiceInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $typo3Refl = new ReflectionService();

        // Create and inject a bunch of dependencies.
        $typo3Context = new ApplicationContext($this->getAppEnv());
        $typo3Env = new Environment($typo3Context);
        $typo3Refl->injectEnvironment($typo3Env);
        //$typo3CacheBackend = new NullBackend($typo3Context);
        $typo3CacheBackend = new FileBackend($typo3Context);
        $typo3CacheBackend->setCacheDirectory($this->findFlowRootPath() . '/cache');
        $typo3CacheBackend->injectEnvironment($typo3Env);
        $typo3Cache = new VariableFrontend('reflection', $typo3CacheBackend);
        $typo3Refl->setClassSchemataRuntimeCache($typo3Cache);
        $typo3Refl->setReflectionDataCompiletimeCache($typo3Cache);
        $typo3Refl->setReflectionDataRuntimeCache($typo3Cache);
        defined('FLOW_PATH_ROOT') or define('FLOW_PATH_ROOT', $this->findFlowRootPath() . DIRECTORY_SEPARATOR);
        defined('FLOW_PATH_PACKAGES') or define('FLOW_PATH_PACKAGES', $this->findFlowPkgPath() . DIRECTORY_SEPARATOR);
        $typo3PackageManager = new PackageManager();
        $typo3Refl->injectPackageManager($typo3PackageManager);
        
        // TODO load our custom annotations classes.
        $typo3ClassLoader = new ClassLoader($typo3Context);
        $typo3Refl->injectClassLoader($typo3ClassLoader);
        $typo3Logger = new Logger();
        $typo3Refl->injectSystemLogger($typo3Logger);

        // Inject settings.
        // TODO get settings from config via service manager
        $typo3Settings = array(
            'reflection' => array(
                'ignoredTags' => array(
                    'api', 'package', 'subpackage', 'license', 'copyright', 'author', 'const', 'see', 'todo', 'scope', 'fixme', 'test', 'expectedException', 'expectedExceptionCode', 'depends', 'dataProvider', 'group', 'codeCoverageIgnore'
                ),
                'logIncorrectDocCommentHints' => true
            )
        );
        $typo3Refl->injectSettings($typo3Settings);
        
        return $typo3Refl;
    }

}
