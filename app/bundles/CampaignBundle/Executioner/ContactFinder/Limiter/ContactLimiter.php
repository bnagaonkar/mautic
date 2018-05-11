<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Executioner\ContactFinder\Limiter;

use Mautic\CampaignBundle\Executioner\Exception\NoContactsFoundException;

/**
 * Class ContactLimiter.
 */
class ContactLimiter
{
    /**
     * @var int|null
     */
    private $batchLimit;

    /**
     * @var int|null
     */
    private $contactId;

    /**
     * @var int|null
     */
    private $minContactId;

    /**
     * @var int|null
     */
    private $batchMinContactId;

    /**
     * @var int|null
     */
    private $maxContactId;

    /**
     * @var array
     */
    private $contactIdList;

    /**
     * ContactLimiter constructor.
     *
     * @param       $batchLimit
     * @param       $contactId
     * @param       $minContactId
     * @param       $maxContactId
     * @param array $contactIdList
     */
    public function __construct($batchLimit, $contactId, $minContactId, $maxContactId, array $contactIdList = [])
    {
        $this->batchLimit    = ($batchLimit) ? (int) $batchLimit : 100;
        $this->contactId     = ($contactId) ? (int) $contactId : null;
        $this->minContactId  = ($minContactId) ? (int) $minContactId : null;
        $this->maxContactId  = ($maxContactId) ? (int) $maxContactId : null;
        $this->contactIdList = $contactIdList;
    }

    /**
     * @return int
     */
    public function getBatchLimit()
    {
        return $this->batchLimit;
    }

    /**
     * @return int|null
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @return int|null
     */
    public function getMinContactId()
    {
        return ($this->batchMinContactId) ? $this->batchMinContactId : $this->minContactId;
    }

    /**
     * @return int|null
     */
    public function getMaxContactId()
    {
        return $this->maxContactId;
    }

    /**
     * @return array
     */
    public function getContactIdList()
    {
        return $this->contactIdList;
    }

    /**
     * @param int $id
     *
     * @throws NoContactsFoundException
     */
    public function setBatchMinContactId($id)
    {
        // Prevent a never ending loop if the contact ID never changes due to being the last batch of contacts
        if ($this->minContactId && $this->minContactId > (int) $id) {
            throw new NoContactsFoundException();
        }

        // We've surpasssed the max so bai
        if ($this->maxContactId && $this->maxContactId < (int) $id) {
            throw new NoContactsFoundException();
        }

        // The same batch of contacts were somehow processed so let's stop to prevent the loop
        if ($this->batchMinContactId && $this->batchMinContactId >= $id) {
            throw new NoContactsFoundException();
        }

        $this->batchMinContactId = (int) $id;
    }
}
