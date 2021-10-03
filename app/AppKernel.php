<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {

    const websiteTitle = "Seats";
    const fromEmail = 'no-reply@seats.com';
    const adminEmail = 'info@seats.com';
//    const adminEmail = 'peter.nassef@gmail.com';


    public function __construct($environment, $debug) {
        date_default_timezone_set('Africa/Cairo');
        parent::__construct($environment, $debug);
    }

    public function registerBundles() {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new VM5\EntityTranslationsBundle\VM5EntityTranslationsBundle(),
            new PN\SeoBundle\PNSeoBundle(),
            new PN\LocaleBundle\PNLocaleBundle(),
            new PN\ServiceBundle\PNServiceBundle(),
            new PN\MediaBundle\PNMediaBundle(),
            new PN\ContentBundle\PNContentBundle(),
            new PN\Bundle\ContentBundle\ContentBundle(),
            new PN\Bundle\CMSBundle\CMSBundle(),
            new PN\Bundle\SeoBundle\SeoBundle(),
            new PN\Bundle\MediaBundle\MediaBundle(),
            new PN\Bundle\ProductBundle\ProductBundle(),
            new PN\Bundle\UserBundle\UserBundle(),
            new PN\Bundle\HomeBundle\HomeBundle(),
            new PN\Bundle\BaseBundle\BaseBundle(),
            new PN\Bundle\CurrencyBundle\CurrencyBundle(),
            new Qipsius\TCPDFBundle\QipsiusTCPDFBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Symfony\Bundle\MakerBundle\MakerBundle();
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            }
        }

        return $bundles;
    }

    public function getRootDir() {
        return __DIR__;
    }

    public function getCacheDir() {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir() {
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load(function (ContainerBuilder $container) {
            $container->setParameter('container.autowiring.strict_mode', true);
            $container->setParameter('container.dumper.inline_class_loader', true);

            $container->addObjectResource($this);
        });
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

}
