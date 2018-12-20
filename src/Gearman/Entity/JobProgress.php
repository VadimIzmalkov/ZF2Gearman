<?php
namespace Zf2Gearman\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="gearman_job_progresses")
 */
class JobProgress
{
    const STATE_COMPLETED   = 'completed';
    const STATE_PENDING     = 'pending';
    const STATE_RUNNING     = 'running';
    const STATE_FAILED      = 'failed';
    
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
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    protected $state; 

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
     * @ORM\Column(name="finished_at", type="datetime", nullable=true)
     */
    protected $finishedAt;

    /**
     * @var string
     * @ORM\Column(name="error", type="string", length=255, nullable=true)
     */
    protected $error; 

    public function __construct()
    {
        $this->createdAt    = new \DateTime();
        $this->token        = md5(uniqid(mt_rand(), true));
        $this->state        = self::STATE_PENDING;
    }

    public function getId() { return $this->id; }
    public function getToken() { return $this->token; }
    public function getCreatedAt() { return $this->createdAt; }

    public function getInitialProgress() { return $this->initialProgress; }
    public function setInitialProgress($initialProgress) { $this->initialProgress = $initialProgress; }

    public function getJobParameters() { return $this->jobParameters; }
    public function setJobParameters($jobParameters) { $this->jobParameters = $jobParameters; }

    public function getCurrentProgress() { return $this->currentProgress; }
    public function setCurrentProgress($currentProgress) { $this->currentProgress = $currentProgress; }

    public function getState() { return $this->state; }
    public function setState($state) { $this->state = $state; }

    public function getFinalProgress() { return $this->finalProgress; }
    public function setFinalProgress($finalProgress) { $this->finalProgress = $finalProgress; }

    public function getStartedAt() { return $this->startedAt; }
    public function setStartedAt($startedAt) { $this->startedAt = $startedAt; }

    public function getFinishedAt() { return $this->finishedAt; }
    public function setFinishedAt($finishedAt) { $this->finishedAt = $finishedAt; }

    public function getError() { return $this->error; }
    public function setError($error) { $this->error = $error; }
}