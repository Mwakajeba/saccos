<?php

namespace App\Helpers;

class QrCodeHelper
{
    /**
     * Generate QR code SVG string
     * This is a simple implementation - you can replace with a proper QR library if needed
     */
    public static function generateSvg($data, $size = 200)
    {
        // For now, use an online QR code service API
        // In production, consider using a proper QR code library
        $encodedData = urlencode($data);
        $url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
        
        return $url;
    }
    
    /**
     * Generate QR code as base64 image
     */
    public static function generateBase64($data, $size = 200)
    {
        $url = self::generateSvg($data, $size);
        $imageData = @file_get_contents($url);
        
        if ($imageData) {
            return 'data:image/png;base64,' . base64_encode($imageData);
        }
        
        return null;
    }
}

