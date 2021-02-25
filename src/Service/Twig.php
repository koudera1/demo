<?php
namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class Twig extends \Twig\Environment {

    public function __construct(KernelInterface $kernel) {
        $loader = new \Twig\Loader\FilesystemLoader($kernel->getProjectDir());

        parent::__construct($loader);
    }
}