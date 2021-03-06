<?php

namespace BookBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $uploadDir;

    const SUB_DIR_MAX_COUNT = 64;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function upload(UploadedFile $file) : string
    {
        $subDir = rand(0, self::SUB_DIR_MAX_COUNT);
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        $file->move($this->getUploadDir() . $subDir, $fileName);

        return $subDir . '/' . $fileName;
    }

    public function getUploadDir() : string
    {
        return $this->uploadDir;
    }
}
