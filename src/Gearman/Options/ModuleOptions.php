<?php

namespace Zf2Gearman\Options;

class ModuleOptions extends \Zend\Stdlib\AbstractOptions
{
    protected $jobs;

    protected $userInterface;

    public function getJobs() { return $this->jobs; }
    public function setJobs($jobs) { 
        $this->jobs = $jobs; 
        return $this;
    }

    public function getUserInterface() { return $this->userInterface; }
    public function setUserInterface($userInterface) { 
        $this->userInterface = $userInterface; 
        return $this;
    }
}
