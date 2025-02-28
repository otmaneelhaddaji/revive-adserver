<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

require_once MAX_PATH . '/lib/max/Dal/Entities.php';
require_once MAX_PATH . '/lib/max/Dal/tests/util/DalUnitTestCase.php';

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/pear/Date.php';

/**
 * A class for testing the non-DB specific MAX_Dal_Entities DAL class.
 *
 * @package    MaxDal
 * @subpackage TestSuite
 */
class Dal_TestOfMAX_Dal_Entities extends UnitTestCase
{
    public $doBanners = null;
    public $doZones = null;
    public $doAdZone = null;
    public $doAcls = null;
    public $doAgency = null;
    public $doChannel = null;
    public $doAclsChannel = null;
    public $doCampaigns = null;
    public $doAffiliates = null;

    /**
     * The constructor method.
     */
    public function __construct()
    {
        parent::__construct();
        $this->doBanners = OA_Dal::factoryDO('banners');
        $this->doZones = OA_Dal::factoryDO('zones');
        $this->doAcls = OA_Dal::factoryDO('acls');
        $this->doAdZone = OA_Dal::factoryDO('ad_zone_assoc');
        $this->doAgency = OA_Dal::factoryDO('agency');
        $this->doChannel = OA_Dal::factoryDO('channel');
        $this->doAclsChannel = OA_Dal::factoryDO('acls_channel');
        $this->doCampaigns = OA_Dal::factoryDO('campaigns');
        $this->doAffiliates = OA_Dal::factoryDO('affiliates');
    }

    public function _insertBanner($aData)
    {
        $this->doBanners->storagetype = 'sql';
        foreach ($aData as $key => $val) {
            $this->doBanners->$key = $val;
        }
        return DataGenerator::generateOne($this->doBanners);
    }

    public function _insertZone($aData)
    {
        foreach ($aData as $key => $val) {
            $this->doZones->$key = $val;
        }
        return DataGenerator::generateOne($this->doZones);
    }

    public function _insertAdZoneAssoc($aData)
    {
        foreach ($aData as $key => $val) {
            $this->doAdZone->$key = $val;
        }
        return DataGenerator::generateOne($this->doAdZone);
    }

    public function _insertAgency($aData)
    {
        $this->doAgency->active = $aData[1];
        $this->doAgency->updated = $aData[2];
        return DataGenerator::generateOne($this->doAgency);
    }

    public function _insertAcls($aData)
    {
        $this->doAcls->bannerid = $aData[0];
        $this->doAcls->logical = $aData[1];
        $this->doAcls->type = $aData[2];
        $this->doAcls->comparison = $aData[3];
        $this->doAcls->data = $aData[4];
        $this->doAcls->executionorder = $aData[5];
        return DataGenerator::generateOne($this->doAcls);
    }

    public function _insertChannel($aData)
    {
        $this->doChannel->agencyid = $aData[1];
        $this->doChannel->affiliateid = $aData[2];
        $this->doChannel->active = $aData[3];
        $this->doChannel->compiledlimitation = $aData[4];
        $this->doChannel->updated = $aData[5];
        $this->doChannel->acls_updated = $aData[6];
        return DataGenerator::generateOne($this->doChannel);
    }

    public function _insertAclsChannel($aData)
    {
        $this->doAclsChannel->channelid = $aData[0];
        $this->doAclsChannel->logical = $aData[1];
        $this->doAclsChannel->type = $aData[2];
        $this->doAclsChannel->comparison = $aData[3];
        $this->doAclsChannel->data = $aData[4];
        $this->doAclsChannel->executionorder = $aData[5];
        return DataGenerator::generateOne($this->doAclsChannel);
    }

    public function _insertCampaign($aData)
    {
        $this->doCampaigns->campaignname = 'Test Campaign';
        $this->doCampaigns->weight = 1;
        $this->doCampaigns->priority = -1;
        $this->doCampaigns->views = -1;
        $this->doCampaigns->clicks = -1;
        $this->doCampaigns->conversions = -1;
        $this->doCampaigns->target_impression = -1;
        $this->doCampaigns->target_click = -1;
        $this->doCampaigns->target_conversion = -1;
        $this->doCampaigns->updated = null;
        $this->doCampaigns->expire_time = null;
        $this->doCampaigns->activate_time = null;
        foreach ($aData as $key => $val) {
            $this->doCampaigns->$key = $val;
        }
        return DataGenerator::generateOne($this->doCampaigns);
    }

    public function _insertAffiliate($aData)
    {
        $this->doAffiliates->agencyid = $aData[1];
        $this->doAffiliates->updated = $aData[2];
        return DataGenerator::generateOne($this->doAffiliates);
    }

    /**
     * A method to test the getAdsByCampaignId() method.
     *
     * Requirements:
     * Test 1: Test with invalid input, and ensure nothing returned
     * Test 2: Test with nothing in the database, and ensure nothing returned
     * Test 3: Test with a single ad in the database, and ensure it is returned
     * Test 4: Test with multiple ads in the database, and ensure only the
     *         required ads are returned
     */
    public function testGetAdsByCampaignId()
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        $oDbh = OA_DB::singleton();
        $oDal = new MAX_Dal_Entities();

        // Test 1
        $campaignId = 'foo';
        $aResult = $oDal->getAdsByCampaignId($campaignId);
        $this->assertNull($aResult);

        // Test 2
        $campaignId = 1;

        $aResult = $oDal->getAdsByCampaignId($campaignId);
        $this->assertNull($aResult);

        // Test 3
        $oNow = new Date();
        $aData = [
            'campaignid' => $campaignId,
            'status' => OA_ENTITY_STATUS_RUNNING,
            'weight' => 1,
            'updated' => $oNow->format('%Y-%m-%d %H:%M:%S'),
            'acls_updated' => $oNow->format('%Y-%m-%d %H:%M:%S')
        ];
        $idBanner1 = $this->_insertBanner($aData);
        $aResult = $oDal->getAdsByCampaignId($campaignId);
        $aExpectedResult = [
            $idBanner1 => [
                'ad_id' => $idBanner1,
                'status' => OA_ENTITY_STATUS_RUNNING,
                'type' => 'sql',
                'weight' => 1
            ]
        ];
        $this->assertEqual($aResult[1]['ad_id'], $aExpectedResult[1]['ad_id']);

        // Test 4
        $aData = [
            'campaignid' => $campaignId,
            'status' => OA_ENTITY_STATUS_PAUSED,
            'weight' => 5,
            'updated' => $oNow->format('%Y-%m-%d %H:%M:%S'),
            'acls_updated' => $oNow->format('%Y-%m-%d %H:%M:%S')
        ];
        $idBanner2 = $this->_insertBanner($aData);
        $aData = [
            'campaignid' => $campaignId + 1,
            'status' => OA_ENTITY_STATUS_RUNNING,
            'weight' => 2,
            'updated' => $oNow->format('%Y-%m-%d %H:%M:%S'),
            'acls_updated' => $oNow->format('%Y-%m-%d %H:%M:%S')
        ];
        $idBanner3 = $this->_insertBanner($aData);
        $aResult = $oDal->getAdsByCampaignId($campaignId);
        $aExpectedResult = [
            $idBanner1 => [
                'ad_id' => $idBanner1,
                'status' => OA_ENTITY_STATUS_RUNNING,
                'type' => 'sql',
                'weight' => 1
            ],
            $idBanner2 => [
                'ad_id' => $idBanner2,
                'status' => OA_ENTITY_STATUS_PAUSED,
                'type' => 'sql',
                'weight' => 5
            ]
        ];
        $this->assertEqual($aResult[1]['ad_id'], $aExpectedResult[1]['ad_id']);
        $this->assertEqual($aResult[2]['ad_id'], $aExpectedResult[2]['ad_id']);

        DataGenerator::cleanUp();
    }

    /**
     * A method to test the getDeliveryLimitationsByAdId() method.
     *
     * Requirements:
     * Test 1: Test with invalid input, and ensure nothing returned
     * Test 2: Test with nothing in the database, and ensure nothing returned
     * Test 3: Test with delivery limitations attached to ad NOT desired, and
     *         ensure nothing returned
     * Test 4: Test with delivery limitations attached to the ad desired, and
     *         ensure they are returned
     */
    public function testGetDeliveryLimitationsByAdId()
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        $oDbh = OA_DB::singleton();
        $oDal = new MAX_Dal_Entities();

        // Test 1
        $adId = 'foo';
        $aResult = $oDal->getDeliveryLimitationsByAdId($adId);
        $this->assertNull($aResult);

        // Test 2
        $adId = 1;
        $aResult = $oDal->getDeliveryLimitationsByAdId($adId);
        $this->assertNull($aResult);

        // Test 3
        $aData = [
            2,
            'and',
            'Site:Channel',
            '==',
            12,
            0
        ];
        $idAcls1 = $this->_insertAcls($aData);
        $adId = 1;
        $aResult = $oDal->getDeliveryLimitationsByAdId($adId);
        $this->assertNull($aResult);
        DataGenerator::cleanUp();


        // Test 4
        $aData = [
            1,
            'and',
            'Site:Channel',
            '==',
            10,
            0
        ];
        $idAcls1 = $this->_insertAcls($aData);
        $aData = [
            1,
            'or',
            'Site:Channel',
            '==',
            11,
            1
        ];
        $idAcls2 = $this->_insertAcls($aData);
        $aData = [
            2,
            'and',
            'Site:Channel',
            '==',
            12,
            0
        ];
        $idAcls3 = $this->_insertAcls($aData);
        $adId = 1;
        $aResult = $oDal->getDeliveryLimitationsByAdId($adId);
        $aExpectedResult = [
            0 => [
                'logical' => 'and',
                'type' => 'Site:Channel',
                'comparison' => '==',
                'data' => 10
            ],
            1 => [
                'logical' => 'or',
                'type' => 'Site:Channel',
                'comparison' => '==',
                'data' => 11
            ]
        ];
        $this->assertEqual($aResult, $aExpectedResult);
        DataGenerator::cleanUp();
    }

    /**
     * A method to test the getDeliveryLimitationsByChannelId() method.
     *
     * Requirements:
     * Test 1: Test with invalid input, and ensure nothing returned
     * Test 2: Test with nothing in the database, and ensure nothing returned
     * Test 3: Test with delivery limitations attached to channel NOT desired, and
     *         ensure nothing returned
     * Test 4: Test with delivery limitations attached to the channel desired, and
     *         ensure they are returned
     */
    public function testGetDeliveryLimitationsByChannelId()
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        $oDbh = OA_DB::singleton();
        $oDal = new MAX_Dal_Entities();

        // Test 1
        $channelId = 'foo';
        $aResult = $oDal->getDeliveryLimitationsByChannelId($channelId);
        $this->assertNull($aResult);

        // Test 2
        $channelId = 1;
        $aResult = $oDal->getDeliveryLimitationsByChannelId($channelId);
        $this->assertNull($aResult);

        // Test 3
        $aData = [
            2,
            'and',
            'Time:Hour',
            '==',
            12,
            0
        ];
        $idAclsChannel1 = $this->_insertAclsChannel($aData);
        $aResult = $oDal->getDeliveryLimitationsByChannelId(1);
        $this->assertNull($aResult);
        DataGenerator::cleanUp();


        // Test 4
        $aData = [
            1,
            'and',
            'Time:Hour',
            '==',
            10,
            0
        ];
        $idAclsChannel1 = $this->_insertAclsChannel($aData);
        $aData = [
            1,
            'or',
            'Time:Hour',
            '==',
            11,
            1
        ];
        $idAclsChannel2 = $this->_insertAclsChannel($aData);
        $aData = [
            2,
            'and',
            'Time:Hour',
            '==',
            12,
            0
        ];
        $idAclsChannel3 = $this->_insertAclsChannel($aData);
        $aResult = $oDal->getDeliveryLimitationsByChannelId(1);
        $aExpectedResult = [
            0 => [
                'logical' => 'and',
                'type' => 'Time:Hour',
                'comparison' => '==',
                'data' => 10
            ],
            1 => [
                'logical' => 'or',
                'type' => 'Time:Hour',
                'comparison' => '==',
                'data' => 11
            ]
        ];
        $this->assertEqual($aResult, $aExpectedResult);
        DataGenerator::cleanUp();
    }
}
