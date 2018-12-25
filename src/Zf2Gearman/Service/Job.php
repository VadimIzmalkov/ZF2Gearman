<?php
namespace Zf2Gearman\Service;

use Zend\Log\Logger;

use Zf2Gearman\Entity\WorkerState;
use Zf2Gearman\Entity\WorkloadInterface;

abstract class Job
{
	protected $entityManager;
	protected $logger;
	protected $workload;

	public function __construct($entityManager, $logger)
	{
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	public function run(WorkloadInterface $workload)
	{
		$this->workload = $workload;

		$this->setupLogger($workload);

		$this->beforePerform();

		$workload->getWorkerState()->setStatus(WorkerState::STATUS_RUNNING);
		$workload->getWorkerState()->setStartedAt(new \DateTime);
		$this->entityManager->persist($workload);
		
		$this->perform();

		$workload->getWorkerState()->setStatus(WorkerState::STATUS_COMPLETED);
		$workload->getWorkerState()->setEndedAt(new \DateTime);
		$this->entityManager->persist($workload);

		$this->entityManager->flush();
		
		$this->afterPerform();
	}

	protected function perform()
	{
		for ($left = 10; $left > 1; $left--) { 
			$this->logger->log('Shutdown after '.$left.' sec...'.PHP_EOL);
			sleep(1);
		}
	}

	protected function beforePerform()
	{
		$this->logger->log('Job in progress');
	}

	protected function afterPerform()
	{
		$this->logger->log('Job completed');
	}

	private function setupLogger($workload)
	{
        $logFileNameCamelCaseArray  = explode("\\", $workload->getJobClass());
        $logFileNameCamelCase       = end($logFileNameCamelCaseArray);
        $logFileName                = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $logFileNameCamelCase));

		$this->logger->setLogFileName($logFileName);
	}
}