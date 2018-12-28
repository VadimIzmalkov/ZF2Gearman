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

    // setters and getters
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

In /config/autoload folder by creating zf2gearman.global.php and register job: 

```php
<?php 
use TinyCRM\Service\GearmanJob;

return [
    'zf2gearman' => [
        'jobs' => [
            GearmanJob\ExampleJob::class => [
                'logEnable'     => true,
                'logFileName'   => 'example_job_' . date("Y-m-d_H-i-s") . '.log',
                'errorFileName' => 'example_job_' . date("Y-m-d_H-i-s") . '.err',
            ],
        ],
    ],
];
```

In Controller:
1. In controller factory inject Zf2Gearman Manager dependency:
```php
$gearmanManager = $parentLocator->get(\Zf2Gearman\Service\Manager::class);
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
