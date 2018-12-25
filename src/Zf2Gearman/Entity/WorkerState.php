<?php
namespace Zf2Gearman\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Zf2Gearman\Entity\Repository\WorkerStateRepository")
 * @ORM\Table(name="zf2gearman_worker_states")
 */
class WorkerState
{
    const STATUS_PENDING     = 'pending';
    const STATUS_RUNNING     = 'running';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_FAILED      = 'failed';
    
	/**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    protected $token;

    /**
     * @var string
     * @ORM\Column(name="host", type="string", nullable=true)
     */
    protected $host;

    /**
     * @var \Zf2Gearman\Entity\WorkloadInterface
     * @ORM\OneToOne(targetEntity="Zf2Gearman\Entity\WorkloadInterface", inversedBy="workerState",cascade={"persist"})
     * @ORM\JoinColumn(name="workload_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $workload;

    /**
     * @var string
     * @ORM\Column(name="initial_progress", type="integer", nullable=true)
     */
    protected $initialProgress;

    /**
     * @var string
     * @ORM\Column(name="current_progress", type="integer", nullable=true)
     */
    protected $currentProgress;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status; 

    /**
     * @var string
     * @ORM\Column(name="final_progress", type="integer", nullable=true)
     */
    protected $finalProgress;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     */
    protected $startedAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="ended_at", type="datetime", nullable=true)
     */
    protected $endedAt;

    /**
     * @var string
     * @ORM\Column(name="error", type="string", length=255, nullable=true)
     */
    protected $error; 

    public function __construct()
    {
        $this->createdAt    = new \DateTime();
        $this->token        = md5(uniqid(mt_rand(), true));
        $this->status       = self::STATUS_PENDING;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWorkload()
    {
        return $this->workload;
    }

    /**
     * @param mixed $workload
     *
     * @return self
     */
    public function setWorkload($workload)
    {
        $this->workload = $workload;

        return $this;
    }

    /**
     * @return string
     */
    public function getInitialProgress()
    {
        return $this->initialProgress;
    }

    /**
     * @param string $initialProgress
     *
     * @return self
     */
    public function setInitialProgress($initialProgress)
    {
        $this->initialProgress = $initialProgress;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentProgress()
    {
        return $this->currentProgress;
    }

    /**
     * @param string $currentProgress
     *
     * @return self
     */
    public function setCurrentProgress($currentProgress)
    {
        $this->currentProgress = $currentProgress;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinalProgress()
    {
        return $this->finalProgress;
    }

    /**
     * @param string $finalProgress
     *
     * @return self
     */
    public function setFinalProgress($finalProgress)
    {
        $this->finalProgress = $finalProgress;

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

    /**
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime $startedAt
     *
     * @return self
     */
    public function setStartedAt(\DateTime $startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }

    /**
     * @param \DateTime $endedAt
     *
     * @return self
     */
    public function setEndedAt(\DateTime $endedAt)
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return self
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }
}