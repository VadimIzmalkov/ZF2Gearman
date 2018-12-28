# TinyCRM
Customer Relationship Management Software

## Installation

```json
{
    "require": {
        "vadimizmalkov/zf2gearman": "dev-master",
    }
}
```

## Usage

In /config/autoload folder create zf2gearman.global.php

```php
<?php 
use TinyCRM\Service\GearmanJob;

return [
	'zf2gearman' => [
		'jobs' => [
			GearmanJob\ExampleJob::class => [
                'logEnable'  	=> true,
                'logFileName'   => 'example_job_' . date("Y-m-d_H-i-s") . '.log',
                'errorFileName' => 'example_job_' . date("Y-m-d_H-i-s") . '.err',
            ],
		],
	],
];
```

Create your Workload with any data

```php
<?php

namespace TinyCRM\Entity;

use Doctrine\ORM\Mapping as ORM;

use Zf2Gearman\Entity\WorkloadInterface;

/**
 * @ORM\Entity(repositoryClass="TinyCRM\Entity\Repository\WorkloadRepository")
 * @ORM\Table(name="example_gearman_workloads")
 */
class GearmanWorkload implements WorkloadInterface
{
	/**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="job_class", type="text", nullable=true)
     */
    protected $jobClass;

    /**
    * @var \Zf2Gearman\Entity\WorkerState
    * @ORM\OneToOne(targetEntity="Zf2Gearman\Entity\WorkerState", mappedBy="workload",cascade={"persist"})
    */
    protected $workerState;

    /**
     * @var string
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    protected $data;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getJobClass()
    {
        return $this->jobClass;
    }

    /**
     * @param string $jobClass
     *
     * @return self
     */
    public function setJobClass($jobClass)
    {
        $this->jobClass = $jobClass;

        return $this;
    }

    /**
     * @return \Zf2Gearman\Entity\WorkerState
     */
    public function getWorkerState()
    {
        return $this->workerState;
    }

    /**
     * @param \Zf2Gearman\Entity\WorkerState $workerState
     *
     * @return self
     */
    public function setWorkerState(\Zf2Gearman\Entity\WorkerState $workerState)
    {
        $this->workerState = $workerState;

        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     *
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
```

Resolve new workload in module.config.php

```php
return array(
	// ...
    'doctrine' => array(
    	// ...
        'entity_resolver' => array(
            'orm_default' => array(
                'resolvers' => array(
                    'Zf2Gearman\Entity\WorkloadInterface' => 'TinyCRM\Entity\GearmanWorkload',
                ),
            ),
        ),
    ),
);
```

Create Job
```php
<?php
namespace TinyCRM\Service\GearmanJob;

use Zf2Gearman\Service\Job as GearmanJob;
use Zf2Gearman\Service\LoggerInterface;

class ExampleJob extends GearmanJob
{
	public function __construct($entityManager, LoggerInterface $logger)
	{
		parent::__construct($entityManager, $logger);
	}

	protected function perform()
	{
		$data = $this->workload->getData();

		// do something with you data
		var_dump($data);
	}
}
```

In Controller:
1. Inject Zf2Gearman Manager dependency in Controller Factory:
```php
$gearmanManager 	= $parentLocator->get(\Zf2Gearman\Service\Manager::class);
```

2. Add new task in Action:

```php
class ExampleController extends ActionController
{
	protected $gearmanManager;

	public function exampleAction()
	{
		$workload = $this->gearmanManager->prepareWorkloadAndGet([
			'jobClass' => ExampleJob::class,
			'data' => 'Hello World'
		]);
		$this->gearmanManager->addTask($workload);
	}
}
```
