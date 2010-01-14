<?php

/**
 * Copyright 2009 Kousuke Ebihara
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <kousuke@co3k.org>
 * @copyright 2009 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

require_once 'PEAR/Exception.php';
require_once 'VersionControl/Git/Commit.php';
require_once 'VersionControl/Git/RevListHandler.php';

/**
 * The OO interface for Git
 *
 * An instance of this class can be handled as OO interface for a Git repository.
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <kousuke@co3k.org>
 * @copyright 2009 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class VersionControl_Git
{
    /**
     * The directory for this repository
     *
     * @var string
     */
    protected $directory;

    /**
     * Location to git binary
     *
     * @var string
     */
    protected $gitCommand = '/usr/bin/git';

    /**
     * Array of options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param string $reposDir  A directory path to a git repository
     */
    public function __construct($reposDir = './', array $options = array())
    {
        if (!is_dir($reposDir)) {
            throw new PEAR_Exception('You must specified readable directory as repository.');
        }

        $this->directory = $reposDir;
        $this->options = $options;
    }

    public function getRevListHandler()
    {
        return new VersionControl_Git_RevListHandler($this);
    }

    public function getCommits($name = 'master', $maxResults = 100, $offset = 0)
    {
        return $this->getRevListHandler()
            ->target($name)
            ->maxCount($maxResults - 1)
            ->skip($offset)
            ->execute();
    }

    public function executeGit($subCommand)
    {
      $currentDir = getcwd();
      chdir($this->directory);

      $outputFile = tempnam(sys_get_temp_dir(), 'VCG');

      $status = trim(shell_exec($this->gitCommand.' '.$subCommand.' > '.$outputFile.'; echo $?'));
      $result = file_get_contents($outputFile);
      unlink($outputFile);

      chdir($currentDir);

      if ($status)
      {
        throw new PEAR_Exception('Some errors in executing git command: '.$result);
      }

      return $result;
    }

    public function getOption($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }
}