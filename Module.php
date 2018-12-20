<?php
namespace Zf2Gearman;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getControllerConfig() 
    {
        return [
            'factories' => [
                Controller\Index::class => Controller\IndexControllerFactory::class,
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Service\GearmanService::class       => Service\GearmanServiceFactory::class,
                Options\ModuleOptions::class        => Options\ModuleOptionsFactory::class,
            ],
        ];
    }
}