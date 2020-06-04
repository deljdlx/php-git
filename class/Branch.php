<?php

namespace JDLX\PHPGit;

use JDLX\GithubAPI\Repository;

class Branch
{
    /**
     * @param Repository
     */
    private $repository;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $fullQualifiedName;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setFullQualifiedName(string $name)
    {
        $this->fullQualifiedName = $name;
        $segments = explode('/', $name);
        $this->name = end($segments);

        return $this;
    }

    public function getFullQualifiedName()
    {
        return $this->fullQualifiedName;
    }
}
