<?php

namespace App\Helpers;

class PathHelper
{
    /**
     * Build an asset URL using the configured public path.
     *
     * @param string $path
     * @return string
     */
    public static function asset(string $path): string
    {
        $publicPath = config('app.public_path', '/');
        
        // Clean path to remove leading slash
        $path = ltrim($path, '/');
        
        // Get the base app URL
        $baseUrl = rtrim(config('app.url'), '/');
        
        // Format the public path prefix
        if ($publicPath === '/' || empty($publicPath)) {
            $prefix = '';
        } else {
            $prefix = '/' . trim($publicPath, '/');
        }
        
        return $baseUrl . $prefix . '/' . $path;
    }
}
