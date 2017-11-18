<?php

namespace BookBundle\Twig;

class ResizeImageExtention extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'resize_image',
                [$this, 'resizeImage'],
                ['is_safe' => ['html']]
            )
        ];
    }

    public function resizeImage(string $path, int $width, int $height) : string
    {
        return '<img src="' . $path . '" width="' . $width .'" height="' . $height . '">';
    }
}
