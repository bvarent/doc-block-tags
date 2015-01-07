<?php

namespace DocBlockTags\ClassFinder;

use Doctrine\Common\Reflection\ClassFinderInterface;

/**
 * Finds a class in a PSR-4 structure.
 */
class Psr4ClassFinder implements ClassFinderInterface
{

    /**
     * The PSR-4 prefixes.
     * @var array
     */
    protected $prefixes;

    /**
     * @param array $prefixes An array of prefixes. Each key is a PHP namespace
     *  and each value is a list of directories.
     */
    public function __construct($prefixes)
    {
        $this->prefixes = $prefixes;
    }

    /**
     * {@inheritDoc}
     */
    public function findFile($class)
    {
        $ds = DIRECTORY_SEPARATOR;
        $backslash = '\\';

        // Strip leading backslash
        if ($backslash == $class[0]) {
            $class = substr($class, 1);
        }

        foreach ($this->prefixes as $classNamePrefix => $dirs) {
            foreach ($dirs as $dir) {
                // Does the class use the namespace prefix?
                $len = strlen($classNamePrefix);
                if (strncmp($classNamePrefix, $class, $len) !== 0) {
                    // no, move to the next registered autoloader
                    continue;
                }

                // Get the relative class name.
                $relativeClass = substr($class, $len);

                // Replace the namespace prefix with the base directory, replace
                // namespace separators with directory separators in the relative
                // class name, append with .php.
                $file = $dir . str_replace($backslash, $ds, $relativeClass) . '.php';

                // If the file exists, return it.
                if (file_exists($file)) {
                    return $file;
                }
            }
        }

        return null;
    }

}
