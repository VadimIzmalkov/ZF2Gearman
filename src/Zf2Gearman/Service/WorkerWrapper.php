<?php
namespace Zf2Gearman\Service;

use Zf2Gearman\Entity\WorkloadInterface;
use Zf2Gearman\Exception\GearmanException;

class WorkerWrapper implements WorkerWrapperInterface
{
	const WAITING_TIMEOUT = 5000; //5 sec

	private $workloadRepository;

	private $gearmanWorker;

	private $serviceLocator;

	private $jobs = [];

	public function __construct($entityManager, $serviceLocator, $moduleOptions)
	{
		$this->gearmanWorker = new \GearmanWorker();
		$this->gearmanWorker->setTimeout(self::WAITING_TIMEOUT);

		$this->workloadRepository = $entityManager->getRepository(WorkloadInterface::class);
		$this->serviceLocator = $serviceLocator;

		$this->jobs = $moduleOptions->getJobs();
	}

	public function runNewWorker(string $functionName)
	{
		$this->gearmanWorker->addServer(Manager::HOST, Manager::PORT);
		
		$this->addFunction($functionName);
	}

	private function addFunction($functionName)
	{
		$this->gearmanWorker->addFunction($functionName, function (\GearmanJob $job) {
            $workloadId    = $job->workload();
            $workload      = $this->workloadRepository->findOneById($workloadId);
            if(isset($this->jobs[$workload->getJobClass()])) {
                $jobClass = $workload->getJobClass();
                $job = $this->serviceLocator->get($jobClass);
                $job->run($workload);
            } else {
                throw new GearmanException("Job config not defined", 1);
            }
		});

		$this->work();
	}

	private function work()
	{
        while(true) 
        {
            echo "Waiting a job... \n";
            $this->gearmanWorker->work();
            if ($this->gearmanWorker->returnCode() != GEARMAN_SUCCESS) {
                echo "return_code: " . $this->gearmanWorker->returnCode() . "\n";
                break;
            }
        }
	}
}