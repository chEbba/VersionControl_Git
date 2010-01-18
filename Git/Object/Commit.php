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

/**
 * The class represents Git commits
 *
 * @category  VersionControl
 * @package   VersionControl_Git
 * @author    Kousuke Ebihara <kousuke@co3k.org>
 * @copyright 2009 Kousuke Ebihara
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
class VersionControl_Git_Object_Commit extends VersionControl_Git_Object
{
    /**
     * The identifier of this tree
     *
     * @var string
     */
    protected $tree;

    /**
     * The identifier of this parent
     *
     * @var string
     */
    protected $parent;

    /**
     * The identifier of this author
     *
     * @var string
     */
    protected $author;

    /**
     * The identifier of this commiter
     *
     * @var string
     */
    protected $commiter;

    /**
     * The identifier of this message
     *
     * @var string
     */
    protected $message;

    /**
     * The identifier of this message
     *
     * @var string
     */
    protected $createdAt;

    /**
     * The identifier of this message
     *
     * @var string
     */
    protected $commitedAt;

    public static function createInstanceByArray($git, $array)
    {
        if (!isset($array['commit']) || !$array['commit'])
        {
            throw new Exception('The commit object must have id');
        }

        $parts = explode(' ', $array['commit'], 2);
        $id =  $parts[1];
        unset($array['commit']);

        $obj = new VersionControl_Git_Object_Commit($git, $id);

        foreach ($array as $k => $v) {
          $method = 'set'.ucfirst($k);

            if (is_callable(array($obj, $method))) {
                $obj->$method($v);
            }
        }

        return $obj;
    }

    public function setTree($tree)
    {
      $parts = explode(' ', $tree, 2);

      if (2 != count($parts) || 'tree' !== $parts[0]) {
          return false;
      }

      $this->tree = $parts[1];
    }

    public function getTree()
    {
      return $this->tree;
    }

    public function setParents($parent)
    {
      $parent = (array)$parent;

      $parts = explode(' ', array_shift($parent), 2);

      if (2 != count($parts) || 'parent' !== $parts[0]) {
          return false;
      }

      $this->parent = $parts[1];
    }

    public function hasParents()
    {
      return (bool)($this->parent);
    }

    public function getParents()
    {
      if (!$this->hasParents())
      {
        return false;
      }

      $revlist = $this->git->getRevListFetcher()
        ->target($this->parent)
        ->setOption('max-count', 1)
        ->fetch();

      if (!$revlist)
      {
        return false;
      }

      return $revlist[1];
    }

    public function setAuthor($author)
    {
      $parts = explode(' ', $author, 2);

      if (2 != count($parts) || 'author' !== $parts[0]) {
          return false;
      }

      list ($name, $date) = $this->parseUser($parts[1]);
      $this->author = $name;
      $this->createdAt = $date;
    }

    public function getAuthor()
    {
      return $this->author;
    }

    public function getCreatedAt()
    {
      return $this->createdAt;
    }

    public function setCommiter($commiter)
    {
      $parts = explode(' ', $commiter, 2);

      if (2 != count($parts) || 'commiter' !== $parts[0]) {
          return false;
      }

      list ($name, $date) = $this->parseUser($parts[1]);
      $this->commiter = $name;
      $this->commitedAt = $date;
    }

    public function getCommiter()
    {
      return $this->commiter;
    }

    public function getCommitedAt()
    {
      return $this->commitedAt;
    }

    public function setMessage($message)
    {
      $this->message = $message;
    }

    public function getMessage()
    {
      return $this->message;
    }

    protected function parseUser($userAndTimestamp)
    {
      $matches = array();
      if (preg_match('/^(.+) (\d+) .*$/', $userAndTimestamp, $matches)) {
        return array($matches[1], new DateTime('@'.$matches[2]));
      }

      return array(null, null);
    }

  public function fetch()
  {
    // TODO: If the class is not entirety, this method inserts values to some properties

    return $this;
  }
}