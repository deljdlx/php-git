<?php

namespace JDLX\PHPGit;

class Repository
{
    const GIT_NOTHING_TO_COMMIT = 'nothing to commit, working tree clean';
    const GIT_STATUS_BRANCH_REGEX = '`On branch (.*)`';
    private $url;
    private $path;

    /**
     * @var Branch[]
     */
    private $branches;

    private $currentBranch;

    public function __construct($path = null)
    {
        if ($path) {
            $this->path = $path;
        }
    }

    public function clone($url, $path = '')
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $this->path = $path;
        $this->url = $url;

        exec('git clone ' . $this->url . ' ' . $path);
    }

    /**
     * @return Branch
     */
    public function getCurrentBranch($noCache = false)
    {
        if ($this->currentBranch === null || $noCache) {
            $current = getcwd();
            chdir($this->getPath());
            exec('git status ', $lines);
            chdir($current);

            $branchName = preg_replace(static::GIT_STATUS_BRANCH_REGEX, '$1', $lines[0]);

            $this->currentBranch = $this->getBranchByName($branchName);
        }
        return $this->currentBranch;
    }


    /**
     * @param string $branch
     * @return static
     */
    public function selectBranch($branch)
    {
        $current = getcwd();
        chdir($this->getPath());
        exec('git checkout ', $branch);
        chdir($current);
        $this->getCurrentBranch(true);
        return $this;
    }

    /**
     * @return Branch
     */
    public function getBranchByName($name)
    {
        foreach($this->getBranches() as $branch) {
            if($branch->getName() == $name) {
                return $branch;
            }
        }
        return false;
    }

    public function getName()
    {
        //$url = $this->
        $lines = $this->getOrigins();
        $segments = explode('/', reset($lines));
        $last = end($segments);
        $name = str_replace('.git', '', $last);

        return $name;
    }

    /**
     * @return Branch[]
     */
    public function getBranches()
    {
        if ($this->branches === null) {
            $this->branches = [];
            $current = getcwd();
            chdir($this->getPath());
            exec('git branch -a ', $lines);
            chdir($current);

            foreach ($lines as $line) {
                if (preg_match('`^\*`', $line)) {
                    continue;
                }
                if (preg_match('`HEAD ->`', $line)) {
                    continue;
                }

                $branch = new Branch($this);
                $branch->setFullQualifiedName($line);

                $this->branches[] = $branch;
            }
        }

        return $this->branches;
    }

    public function getOrigins()
    {
        $current = getcwd();
        chdir($this->getPath());
        exec('git remote -v ', $lines);
        chdir($current);

        $urls = [];
        foreach ($lines as $index => $line) {
            if (preg_match('`^origin`', $line)) {
                $line = trim($line);
                $type = preg_replace('`.*?\((.*?)\)$`', '$1', $line);

                $url = preg_replace('` \(.*?\)`', '', $line);

                $urls[$type] = $url;
            }
        }

        return $urls;
    }

    public function setOrigin($origin, &$buffer = null)
    {
        $current = getcwd();
        chdir($this->getPath());
        exec('git remote set-url origin ' . $origin, $lines);
        $buffer = implode('', $lines);
        chdir($current);

        return $this;
    }

    public function push(&$buffer = null)
    {
        $current = getcwd();
        chdir($this->getPath());
        exec('git push', $lines);
        chdir($current);

        $buffer = implode('', $lines);

        return $this;
    }

    public function pullAll(&$buffer = null)
    {
        $current = getcwd();
        chdir($this->getPath());
        exec('git fetch --all', $lines);
        exec('git pull --all', $lines);
        chdir($current);
        $buffer = implode('', $lines);

        return $this;
    }

    public function commitRequired()
    {
        $current = getcwd();
        chdir($this->getPath());
        exec('git status', $lines);
        $buffer = implode('', $lines);
        chdir($current);
        if (preg_match('`' . static::GIT_NOTHING_TO_COMMIT . '`', $buffer)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the value of path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
