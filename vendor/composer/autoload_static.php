<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfeac204fe2608609e36a6d43d032ead3
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Sf\\Popup\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Sf\\Popup\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
            1 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfeac204fe2608609e36a6d43d032ead3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfeac204fe2608609e36a6d43d032ead3::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
