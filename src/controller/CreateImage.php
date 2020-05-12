<?php

namespace src\controller;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateImage extends Command
{
    protected static $defaultName = 'create:image';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fonts = [
            'n' => __DIR__ . '/../font/Nicholia.ttf',
            's' => __DIR__ . '/../font/sweet_purple.ttf',
            'a' => __DIR__ . '/../font/The_Austin.ttf',
        ];
        $fileMask = __DIR__ . '/../res/{number}.{size}.{font}.{shadow}.png';
        mkdir(__DIR__ . '/../res', 0755);
        for ($i = 0; $i < 10; $i++) {
            foreach ($fonts as $alias => $font) {
                foreach ([30, 28] as $size) {
                    foreach ([true, false] as $shadow) {
                        $file = str_replace('{number}', $i, $fileMask);
                        $file = str_replace('{size}', $size, $file);
                        $file = str_replace('{font}', $alias, $file);
                        $file = str_replace('{shadow}', ($shadow) ? 'y' : 'n', $file);
                        $this->createImg($file, $i, $size, $font, $shadow);
                    }
                }
            }
        }
        return 0;
    }

    private function createImg(string $file, string $number, int $size, string $font, bool $shadow)
    {
        $img = imagecreate(32, 32);

        $white = imagecolorallocate($img, 255, 255, 255);
        $grey = imagecolorallocate($img, 128, 128, 128);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, 399, 29, $white);

        $px = (imagesx($img) - 7.5 * strlen($number)) / 2;

        if ($shadow) {
            imagettftext($img, $size, 0, $px, 31, $grey, $font, $number);
        }
        imagettftext($img, $size, 0, $px - 1, 30, $black, $font, $number);
//        imagestring($img, $size, $px, 9, $number, $text_color);
        imagepng($img, $file);
        imagedestroy($img);
    }
}