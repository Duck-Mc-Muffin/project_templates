<?php

/**
 * Simple Autoloader.
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \App\Foo\Bar class
 * from /path/to/project/src/Foo/Bar.php:
 *
 *      new \App\Foo\Bar();
 *
 * @see https://github.com/php-fig/fig-standards/blob/920ded68fe99724c1a0c35894028f04b16afd3f3/accepted/PSR-4-autoloader-examples.md
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class)
{
    // Project-specific namespace prefix
    $prefix = 'App\\';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
    {
        // Move to the next registered autoloader
        return;
    }

    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base directory and
    // replace namespace separators with directory separators in the relative class name.
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    if (file_exists($file)) require $file;
});
