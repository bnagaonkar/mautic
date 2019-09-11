<?php

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\Helper;


use Mautic\ChannelBundle\Helper\ChannelListHelper;
use Mautic\LeadBundle\Model\FieldModel;
use MauticPlugin\IntegrationsBundle\Event\MauticSyncFieldsLoadEvent;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\IntegrationsBundle\Sync\VariableExpresser\VariableExpresserHelperInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FieldHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldModel;

    /**
     * @var VariableExpresserHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $variableExpresserHelper;

    /**
     * @var ChannelListHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $channelListHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var MauticSyncFieldsLoadEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mauticSyncFieldsLoadEvent;


    protected function setUp()
    {
        $this->fieldModel = $this->createMock(FieldModel::class);
        $this->variableExpresserHelper = $this->createMock(VariableExpresserHelperInterface::class);
        $this->channelListHelper = $this->createMock(ChannelListHelper::class);
        $this->channelListHelper->method('getFeatureChannels')
            ->willReturn(['Email' => 'email']);

        $this->mauticSyncFieldsLoadEvent = $this->createMock(MauticSyncFieldsLoadEvent::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')
            ->willReturn($this->mauticSyncFieldsLoadEvent);
    }

    public function testContactSyncFieldsReturned()
    {
        $objectName = MauticSyncDataExchange::OBJECT_CONTACT;
        $syncFields = [ 'email' => 'Email'];

        $this->mauticSyncFieldsLoadEvent->method('getObjectName')
            ->willReturn($objectName);
        $this->mauticSyncFieldsLoadEvent->method('getFields')
            ->willReturn($syncFields);

        $this->fieldModel->method('getFieldList')
            ->willReturn($syncFields);

        $fields = $this->getFieldHelper()->getSyncFields($objectName);

        $this->assertEquals(
            [
                'mautic_internal_dnc_email',
                'mautic_internal_id',
                'mautic_internal_contact_timeline',
                'email',
            ],
            array_keys($fields)
        );
    }

    public function testCompanySyncFieldsReturned()
    {
        $objectName = MauticSyncDataExchange::OBJECT_CONTACT;

        $this->mauticSyncFieldsLoadEvent->method('getObjectName')
            ->willReturn($objectName);
        $this->mauticSyncFieldsLoadEvent->method('getFields')
            ->willReturn([ 'email' => 'Email']);

        $this->fieldModel->method('getFieldList')
            ->willReturn([ 'email' => 'Email']);

        $fields = $this->getFieldHelper()->getSyncFields($objectName);

        $this->assertEquals(
            [
                'mautic_internal_dnc_email',
                'mautic_internal_id',
                'mautic_internal_contact_timeline',
                'email',
            ],
            array_keys($fields)
        );
    }

    public function testGetRequiredFieldsForContact(): void
    {
        $this->fieldModel->expects($this->once())
            ->method('getFieldList')
            ->willReturn(['some fields']);

        $this->fieldModel->expects($this->once())
            ->method('getUniqueIdentifierFields')
            ->willReturn(['some unique fields']);

        $this->assertSame(
            ['some fields', 'some unique fields'],
            $this->getFieldHelper()->getRequiredFields('lead')
        );
    }

    public function testGetRequiredFieldsForCompany(): void
    {
        $this->fieldModel->expects($this->once())
            ->method('getFieldList')
            ->willReturn(['some fields']);

        $this->fieldModel->expects($this->never())
            ->method('getUniqueIdentifierFields');

        $this->assertSame(
            ['some fields'],
            $this->getFieldHelper()->getRequiredFields('company')
        );
    }

    private function getFieldHelper()
    {
        return new FieldHelper($this->fieldModel, $this->variableExpresserHelper, $this->channelListHelper, $this->createMock(TranslatorInterface::class), $this->eventDispatcher);
    }
}