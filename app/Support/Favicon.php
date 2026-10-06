<?php

namespace App\Support;

class Favicon
{
    /**
     * Returns the path (relative to storage/app/public) of a small square PNG
     * made from the school logo, creating it on first use. Null if it can't be made.
     */
    public static function forLogo(string $logo, int $size = 128): ?string
    {
        try {
            $src = public_path('storage/' . $logo);
            if (! is_file($src) || ! function_exists('imagecreatefromstring')) {
                return null;
            }

            $rel  = trim(dirname($logo), '/.');
            $name = 'fav-' . pathinfo($logo, PATHINFO_FILENAME) . '.png';
            $relOut = ($rel !== '' ? $rel . '/' : '') . $name;
            $out = public_path('storage/' . $relOut);

            if (is_file($out)) {
                return $relOut;
            }

            $img = @imagecreatefromstring((string) file_get_contents($src));
            if (! $img) {
                return null;
            }

            $w = imagesx($img);
            $h = imagesy($img);
            $side = max($w, $h);
            $scale = $size / $side;
            $nw = max(1, (int) round($w * $scale));
            $nh = max(1, (int) round($h * $scale));

            $dst = imagecreatetruecolor($size, $size);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
            imagecopyresampled($dst, $img, (int) (($size - $nw) / 2), (int) (($size - $nh) / 2), 0, 0, $nw, $nh, $w, $h);

            $ok = imagepng($dst, $out, 9);
            imagedestroy($img);
            imagedestroy($dst);

            if ($ok) {
                @chmod($out, 0644);
                return $relOut;
            }
        } catch (\Throwable $e) {
            // fall through to the original logo
        }

        return null;
    }
}
