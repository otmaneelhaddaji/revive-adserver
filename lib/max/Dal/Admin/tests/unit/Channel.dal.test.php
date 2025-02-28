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

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/max/Dal/tests/util/DalUnitTestCase.php';

/**
 * A class for testing DAL Channel methods
 *
 * @package    MaxDal
 * @subpackage TestSuite
 *
 */
class MAX_Dal_Admin_ChannelTest extends DalUnitTestCase
{
    public $dalChannel;

    /**
     * The constructor method.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function setUp()
    {
        $this->dalChannel = OA_Dal::factoryDAL('channel');
    }

    public function tearDown()
    {
        DataGenerator::cleanUp();
    }

    public function testGetChannelsAndAffiliates()
    {
        // Insert 2 channels
        $aData = [
            'acls_updated' => ['2007-04-04 17:27:33']
        ];

        DataGenerator::setData('channel', $aData);
        $aChannelId = DataGenerator::generate('channel', 2, true);

        // Check the correct number of rows returned
        $expectedRows = 2;
        $rsChannel = $this->dalChannel->getChannelsAndAffiliates();
        $rsChannel->find();
        $actualRows = $rsChannel->getRowCount();
        $this->assertEqual($actualRows, $expectedRows);

        // Check each row has the correct number of fields
        $rsChannel->fetch();
        $aChannel = $rsChannel->export();
        $this->assertEqual(count($aChannel), 4);
    }
}
