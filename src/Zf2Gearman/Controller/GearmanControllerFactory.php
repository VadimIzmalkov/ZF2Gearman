<?php
namespace Zf2Gearman\Controller;

class GearmanControllerFactory implements \Zend\ServiceManager\FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator 	= $serviceLocator->getServiceLocator();

        $gearmanManager = $parentLocator->get(\Zf2Gearman\Service\Manager::class);

        $controller 	= new GearmanController($gearmanManager);

        return $controller;
    }
}