<?php
/**
* @link     TODO
* @author   Vadim Izmalkov <399115@gmail.com> 15 Jan 2018
* @version  1.0
* @package  Gearman
*/
namespace Zf2Gearman\Service;

class GearmanService
{
    const DIR_LOG_RELATIVE = '/data/log/gearman';

    const WAITING_TIMEOUT = 5000; //5 sec

    private $serviceLocator;
    private $entityManager;

    private $jobParametersRepository;

    private $dirLogAbsolut;

    // all jobs configs
    private $config         = [];

    private $worker         = null;
    private $logger         = null;
    private $loggerEnable   = true;

    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        $this->entityManager            = $this->serviceLocator->get(\Doctrine\ORM\EntityManager::class);
        $this->jobParametersRepository  = $this->entityManager->getRepository('Zf2Gearman\Entity\JobParametersInterface');

        $this->dirLogAbsolut = getcwd().self::DIR_LOG_RELATIVE;
        \VisoftBaseModule\Controller\Plugin\AccessoryPlugin::checkDir($this->dirLogAbsolut);

        $config = $this->serviceLocator->get('Config');
        $this->config = $config['gearman'];

        $this->logger = new \Zend\Log\Logger;
    }

    public function addJob($jobParameters, $createOwnWorker = 0)
    {
        if($jobParameters instanceof \Zf2Gearman\Entity\JobParametersInterface)
        {
            $jobParametersId = $jobParameters->getId();
        }
        elseif(is_int($jobParameters))
        {
            $jobParametersId = $jobParameters;
            $jobParameters = $this->entityManager->find(\Zf2Gearman\Entity\JobParametersInterface::class, $jobParametersId);
        }
        else
        {
            throw new \Zf2Gearman\Exception\GearmanException("Error Processing Request", 1);
        }

        $jobConfig = $this->getJobConfig($jobParameters->getType());

        if($jobConfig['loggerEnable'])
        {
            $logWorkerFilePath = $this->getLogDir() . '/' . $jobConfig['logFileName'];
            $errWorkerFilePath = $this->getLogDir() . '/' . $jobConfig['errorFileName'];

            $shell = 'php public/index.php add-background-job-console ' . $jobParametersId . ' ' . (int)$createOwnWorker . ' >' . $logWorkerFilePath . ' 2>' . $errWorkerFilePath . ' &';
        }
        else
        {
            $shell = 'php public/index.php add-background-job-console ' . $jobParametersId . ' ' . (int)$createOwnWorker . ' >/dev/null 2>/dev/null &';
        }

        shell_exec($shell);

        return true;
    }

	public function addBackgroundJob(\Zf2Gearman\Entity\JobParametersInterface $jobParameters, $createOwnWorker = false)
	{   
		$client = new \GearmanClient();
		$client->addServer('127.0.0.1', 4730);

        $jobParametersId    = $jobParameters->getId();
        $functionName       = $jobParameters->getType();
        // $functionName       = $this->config['jobs'][$jobParameters->getType()]['service'];

		$result             = $client->doBackground($functionName, $jobParametersId);

		if(!$createOwnWorker) 
        {
			if($this->isWorkerExist($functionName)) 
            {
				echo 'Waiting for worker...';
				die('bye');
			}
		} 

		$this->worker = new \GearmanWorker();
		$this->worker->addServer('127.0.0.1', 4730);
		$this->worker->setTimeout(static::WAITING_TIMEOUT);

        $result = $this->work($functionName);
        return $result;
	}

	private function isWorkerExist($function)
	{
		$gearmanStatus = $this->getGearmanStatus();
        foreach ($gearmanStatus['connections'] as $key => $connection) 
        {
            if($connection['function'] === $function) 
            {
                return true;
            }
        }
        return false;
	}

	public function work($functionName)
	{
		$this->worker->addFunction($functionName, function (\GearmanJob $gearmanJob) {
            $jobParametersId    = $gearmanJob->workload();
            $jobParameters      = $this->jobParametersRepository->findOneById($jobParametersId);

            // without resetting bug occurs in ZF2 logger after next job launching
            $this->resetLogger($jobParameters);

            if(isset($this->config['jobs'][$jobParameters->getType()]))
            {
                // $jobServiceClass    = $this->config['jobs'][$jobParameters->getType()]['service'];
                $jobServiceClass    = $jobParameters->getType();
                $jobService         = $this->serviceLocator->get($jobServiceClass);

                $jobService->reset($jobParameters);

                $jobService->perform();
            }
            else
            {
                throw new \Zf2Gearman\Exception\GearmanException("Job config not defined", 1);
            }
		});
        
        while(true) 
        {
            echo "Waiting a job... \n";
            $this->worker->work();
            if ($this->worker->returnCode() != GEARMAN_SUCCESS) 
            {
                echo "return_code: " . $this->worker->returnCode() . "\n";
                break;
            }
        }
	}

    public function createJobParametersAndGet(array $data, $flush = true)
    {
        $urlHelper = $this->serviceLocator->get('ViewHelperManager')->get('url');
        $host = $urlHelper('home', [], ['force_canonical' => true]);

        // create progress
        $jobProgress = new \Zf2Gearman\Entity\JobProgress();
        $this->entityManager->persist($jobProgress);

        // create parameters
        $jobParametersEntityInfo = $this->entityManager->getClassMetadata(\Zf2Gearman\Entity\JobParametersInterface::class);
        $jobParameters = new $jobParametersEntityInfo->name($data['type']);
        $jobParameters->setHost($host);
        $jobParameters->setJobProgress($jobProgress);
        $this->entityManager->persist($jobParameters);
        
        if($flush)
        {
            $this->entityManager->flush();
        }

        return $jobParameters;
    }

    public function getLogDir() 
    { 
        return $this->dirLogAbsolut; 
    }

    public function log($message)
    {
        if($this->loggerEnable)
        {
            $this->logger->info($message);
        }
    }

    public function getJobConfig($jobType) 
    {
        if(isset($this->config['jobs'][$jobType])) 
        {
            return $this->config['jobs'][$jobType];
        }
        else
        {
            return false;
        } 
    }

    private function resetLogger($jobParameters)
    {
        $this->logger = new \Zend\Log\Logger;

        $jobConfig = $this->getJobConfig($jobParameters->getType());

        $this->loggerEnable = $jobConfig['loggerEnable'];

        $logFileNameCamelCaseArray  = explode("\\", $jobParameters->getType());
        $logFileNameCamelCase       = end($logFileNameCamelCaseArray);
        $logFileName                = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $logFileNameCamelCase));

        if($this->loggerEnable)
        {
            $logFilePath = $this->dirLogAbsolut . '/' . $logFileName . '_progress_' . date('d-m-Y_H-i-s') . '.log';
            $stream = @fopen($logFilePath, 'w', false);
            
            if(!$stream)
            {
                throw new \Zf2Gearman\Exception\GearmanException('Failed to open stream'); 
            }

            $writer = new \Zend\Log\Writer\Stream($stream);
            $this->logger->addWriter($writer);
        } 
        else
        {
            $this->logger = null;
        }
    }

	private function getGearmanStatus() 
    {
        $status = null;
        // $handle = fsockopen($this->host,$this->port,$errorNumber,$errorString,30);
        $handle = fsockopen('127.0.0.1', 4730, $errorNumber, $errorString, 30);
        if($handle!=null)
        {
            fwrite($handle,"status\n");
            while (!feof($handle)) 
            {
                $line = fgets($handle, 4096);
                if( $line == ".\n") 
                {
                    break;
                }
                if( preg_match("~^(.*)[ \t](\d+)[ \t](\d+)[ \t](\d+)~", $line, $matches) ) 
                {
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
            while (!feof($handle)) 
            {
                $line = fgets($handle, 4096);
                if( $line==".\n")
                {
                    break;
                }

                // FD IP-ADDRESS CLIENT-ID : FUNCTION
                if( preg_match("~^(\d+)[ \t](.*?)[ \t](.*?) : ?(.*)~",$line,$matches) )
                {
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