<?php

namespace Zf2Gearman\Options;

class ModuleOptions extends \Zend\Stdlib\AbstractOptions
{
    protected $jobs;

    public function getJobs() { return $this->jobs; }
    public function setJobs($jobs) { 
        $this->jobs = $jobs; 
        return $this;
    }
}
