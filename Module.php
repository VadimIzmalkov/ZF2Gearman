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
                Controller\Gearman::class => Controller\GearmanControllerFactory::class,
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'invokables' => [
                Service\Logger::class               => Service\Logger::class,
            ],
            'factories' => [
                Service\Manager::class              => Service\ManagerFactory::class,
                Service\WorkerWrapper::class        => Service\WorkerWrapperFactory::class,
                Options\ModuleOptions::class        => Options\ModuleOptionsFactory::class,
            ],
        ];
    }
}