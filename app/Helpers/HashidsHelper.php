<?php

namespace App\Helpers;

use Vinkla\Hashids\Facades\Hashids;
use RuntimeException;
use Log;

class HashidsHelper
{
    /**
     * Check if required PHP extension is available
     */
    protected static function hasRequiredExtension()
    {
        return extension_loaded('bcmath') || extension_loaded('gmp');
    }

    /**
     * Safely encode an ID using Hashids
     */
    public static function encode($id)
    {
        // Check extension before trying to use Hashids
        if (!self::hasRequiredExtension()) {
            $error = 'Hashids requires bcmath or gmp PHP extension. Install: sudo apt-get install php8.3-bcmath && sudo systemctl restart php8.3-fpm';
            Log::error($error);
            throw new RuntimeException($error);
        }

        try {
            return Hashids::encode($id);
        } catch (RuntimeException $e) {
            Log::error('Hashids encode failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Safely decode a hash using Hashids
     */
    public static function decode($hash)
    {
        // Check extension before trying to use Hashids
        if (!self::hasRequiredExtension()) {
            $error = 'Hashids requires bcmath or gmp PHP extension. Install: sudo apt-get install php8.3-bcmath && sudo systemctl restart php8.3-fpm';
            Log::error($error);
            throw new RuntimeException($error);
        }

        try {
            return Hashids::decode($hash);
        } catch (RuntimeException $e) {
            Log::error('Hashids decode failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
