<?php

namespace BookBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ResizeImageExtention extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'resize_image',
                [$this, 'resizeImage'],
                ['is_safe' => array('html')]
            )
        ];
    }

    public function resizeImage($path, $width = null, $height = null)
    {
        $path = $this->container->getParameter('upload_directory') . '/' . $path;
        $width = $width ?: $this->container->getParameter('default_img_width');
        $height = $height ?: $this->container->getParameter('default_img_height');

        return '<img src="' . $path . '" width="' . $width .'" height="' . $height . '">';
    }
}
