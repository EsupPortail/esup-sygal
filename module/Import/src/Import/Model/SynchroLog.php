<?php

namespace Import\Model;

/**
 * SynchroLog
 */
class SynchroLog
{
    private $id;

    private $logDate;

    private $startDate;

    private $finishDate;

    private $status;

    private $sql;

    private $message;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getLogDate()
    {
        return $this->logDate;
    }

    /**
     * @param \DateTime $logDate
     * @return self
     */
    public function setLogDate(\DateTime $logDate)
    {
        $this->logDate = $logDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return self
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFinishDate()
    {
        return $this->finishDate;
    }

    /**
     * @param \DateTime $finishDate
     * @return self
     */
    public function setFinishDate(\DateTime $finishDate)
    {
        $this->finishDate = $finishDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
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
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     * @return self
     */
    public function setSql($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

}

