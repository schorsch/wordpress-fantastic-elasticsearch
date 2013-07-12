<?php

namespace Elastica;
use Elastica\Index\Status as IndexStatus;

/**
 * Elastica general status
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 */
class Status
{
    /**
     * Contains all status infos
     *
     * @var \Elastica\Response Response object
     */
    protected $_response = null;

    /**
     * Data
     *
     * @var array Data
     */
    protected $_data = array();

    /**
     * Client object
     *
     * @var \Elastica\Client Client object
     */
    protected $_client = null;

    /**
     * Constructs Status object
     *
     * @param \Elastica\Client $client Client object
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Returns status data
     *
     * @return array Status data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Returns status objects of all indices
     *
     * @return array|\Elastica\Index\Status[] List of Elastica\Client\Index objects
     */
    public function getIndexStatuses()
    {
        $statuses = array();
        foreach ($this->getIndexNames() as $name) {
            $index = new Index($this->_client, $name);
            $statuses[] = new IndexStatus($index);
        }

        return $statuses;
    }

    /**
     * Returns a list of the existing index names
     *
     * @return array Index names list
     */
    public function getIndexNames()
    {
        $names = array();
        foreach ($this->_data['indices'] as $name => $data) {
            $names[] = $name;
        }

        return $names;
    }

    /**
     * Checks if the given index exists
     *
     * @param  string $name Index name to check
     * @return bool   True if index exists
     */
    public function indexExists($name)
    {
        return in_array($name, $this->getIndexNames());
    }

    /**
     * Checks if the given alias exists
     *
     * @param  string $name Alias name
     * @return bool   True if alias exists
     */
    public function aliasExists($name)
    {
        foreach ($this->getIndexStatuses() as $status) {
            if ($status->hasAlias($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an array with all indices that the given alias name points to
     *
     * @param  string                 $name Alias name
     * @return array|\Elastica\Index[] List of Elastica\Index
     */
    public function getIndicesWithAlias($name)
    {
        $indices = array();
        foreach ($this->getIndexStatuses() as $status) {
            if ($status->hasAlias($name)) {
                $indices[] = $status->getIndex();
            }
        }

        return $indices;
    }

    /**
     * Returns response object
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Return shards info
     *
     * @return array Shards info
     */
    public function getShards()
    {
        return $this->_data['shards'];
    }

    /**
     * Refresh status object
     */
    public function refresh()
    {
        $path = '_status';
        $this->_response = $this->_client->request($path, Request::GET);
        $this->_data = $this->getResponse()->getData();
    }

    /**
     * Refresh serverStatus object
     */
    public function getServerStatus()
    {
        $path = '';
        $response = $this->_client->request($path, Request::GET);

        return  $response->getData();
    }
}