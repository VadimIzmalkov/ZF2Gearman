<?php
namespace Zf2Gearman\Service;

use Zf2Gearman\Service\Worker;
use Zf2Gearman\Entity\WorkloadInterface;
use Zf2Gearman\Entity\WorkerState;
use Zf2Gearman\Exception\GearmanException;

class Manager implements ManagerInterface
{
	const HOST = '127.0.0.1';
	const PORT = 4730;

    private $jobs = [];

	private $workerWrapper;
	private $gearmanClient;
    private $moduleOptions;
    private $entityManager;

    private $workloadRepository;
    private $workerStateRepository;

	public function __construct($entityManager, WorkerWrapperInterface $workerWrapper, $moduleOptions)
	{
        $this->gearmanClient            = new \GearmanClient();
		$this->workerWrapper            = $workerWrapper;
        $this->moduleOptions            = $moduleOptions;
        $this->entityManager            = $entityManager;

        $this->jobs                     = $this->moduleOptions->getJobs();

        $this->workloadRepository       = $this->entityManager->getRepository(WorkloadInterface::class);
        $this->workerStateRepository    = $this->entityManager->getRepository(WorkerState::class);
	}

    public function prepareWorkloadAndGet(array $data)
    {
        $workload = $this->entityManager->getRepository(WorkloadInterface::class)->createAndGet($data);

        $this->entityManager->getRepository(WorkerState::class)->create([
            'workload'  => $workload,
            'host'      => $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'],
        ]);

        return $workload;
    }

    public function addTask($workload, bool $force = false)
    {
        $workload   = $this->getWorkloadEntity($workload);
        $jobConfig  = $this->jobs[$workload->getJobClass()]; // = $this->getWorkloadConfig();

        if($jobConfig['logEnable']) {
            $logWorkerFilePath = Logger::getAbsolutePath().'/'.$jobConfig['logFileName'];
            $errWorkerFilePath = Logger::getAbsolutePath().'/'.$jobConfig['errorFileName'];

            $shell = 'php public/index.php run-worker-console '.$workload->getId().' '.(int)$force.' >'.$logWorkerFilePath.' 2>'.$errWorkerFilePath.' &';
        } else {
            $shell = 'php public/index.php run-worker-console '.$workload->getId().' '.(int)$force.' >/dev/null 2>/dev/null &';
        }

        shell_exec($shell);

        return true;
    }

    public function runWorker($workload, bool $force = false)
    {
        $workload = $this->getWorkloadEntity($workload);
        $jobClass = $workload->getJobClass();

        $this->gearmanClient->addServer(self::HOST, self::PORT);
        $this->gearmanClient->doBackground($jobClass, $workload->getId());

        if(!$force) {
            if($this->isWorkerRun($jobClass)) {
                echo 'Worker already running';
                die('bye');
            }
        } 

        return $this->workerWrapper->runNewWorker($jobClass);
    }

    private function isWorkerRun($jobClass)
    {
        $gearmanStatus = $this->getGearmanStatus();
        foreach ($gearmanStatus['connections'] as $key => $connection) {
            if($connection['function'] === $jobClass) {
                return true;
            }
        }
        return false;
    }

    private function getWorkloadEntity($workload)
    {
        if(is_int($workload)) {
            $workload = $this->entityManager->find(WorkloadInterface::class, $workload);
        }

        if($workload instanceof \Zf2Gearman\Entity\WorkloadInterface) {
            return $workload;
        } else {
            throw new GearmanException("Invalid workload. Unable to find the entity", 1);
        }
    }

    private function getGearmanStatus() 
    {
        $status = null;
        $handle = fsockopen(self::HOST, self::PORT, $errorNumber, $errorString, 30);
        if($handle!=null) {
            fwrite($handle,"status\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line == ".\n") {
                    break;
                }
                if( preg_match("~^(.*)[ \t](\d+)[ \t](\d+)[ \t](\d+)~", $line, $matches) ) {
                    $function = $matches[1];
                    $status['operations'][$function] = array(
                        'function' => $function,
                        'total' => $matches[2],
                        'running' => $matches[3],
                        'connectedWorkers' => $matches[4],
                    );
                }
            }
            fwrite($handle,"workers\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line==".\n") {
                    break;
                }

                // FD IP-ADDRESS CLIENT-ID : FUNCTION
                if( preg_match("~^(\d+)[ \t](.*?)[ \t](.*?) : ?(.*)~",$line,$matches) ) {
                    $fd = $matches[1];
                    $status['connections'][$fd] = array(
                        'fd' => $fd,
                        'ip' => $matches[2],
                        'id' => $matches[3],
                        'function' => $matches[4],
                    );
                }
            }
            fclose($handle);
        }
        return $status;
    }
}