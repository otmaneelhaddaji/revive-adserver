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

require_once LIB_PATH . '/Dal/Maintenance/Statistics/Factory.php';

/**
 * A class for testing the saveSummary() method of the
 * DB agnostic OX_Dal_Maintenance_Statistics class.
 *
 * @package    OpenXDal
 * @subpackage TestSuite
 */
class Test_OX_Dal_Maintenance_Statistics_saveSummary extends UnitTestCase
{
    /**
     * The constructor method.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Tests the saveSummary() method.
     *
     * Requirements:
     * Test 1: Test with no data.
     * Test 2: Test a single day summarisation.
     * Test 3: Test multi-day summarisation.
     */
    public function testSaveSummary()
    {
        $aConf = &$GLOBALS['_MAX']['CONF'];
        $oDbh = OA_DB::singleton();

        $oFactory = new OX_Dal_Maintenance_Statistics_Factory();
        $oDalMaintenanceStatistics = $oFactory->factory();

        // Test 1
        $start = new Date('2004-06-06 12:00:00');
        $end = new Date('2004-06-06 12:29:59');
        $aActionTypes = [
            'types' => [
                0 => 'request',
                1 => 'impression',
                2 => 'click'
            ],
            'connections' => [
                1 => MAX_CONNECTION_AD_IMPRESSION,
                2 => MAX_CONNECTION_AD_CLICK
            ]
        ];
        $oDalMaintenanceStatistics->saveSummary($start, $end, $aActionTypes, 'data_intermediate_ad', 'data_summary_ad_hourly');
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true);
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['number'], 0);

        // Test 2
        // Insert the test data
        $this->_insertTestSaveSummaryPlacement();
        $this->_insertTestSaveSummaryAd();
        $this->_insertTestSaveSummaryZone();
        $query = "
            INSERT INTO
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_intermediate_ad'], true) . "
                (
                    date_time, operation_interval, operation_interval_id, interval_start, interval_end,
                    ad_id, creative_id, zone_id, impressions, clicks, conversions, total_basket_value, total_num_items
                )
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $aTypes = [
            'timestamp',
            'integer',
            'integer',
            'timestamp',
            'timestamp',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer',
            'integer'
        ];
        $st = $oDbh->prepare($query, $aTypes, MDB2_PREPARE_MANIP);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 1, 1, 1, 1, 1, 1, 1, 0
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 1, 2, 1, 1, 1, 1, 1, 0
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 1, 2, 1, 1, 1, 1, 1, 0
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 2, 1, 1, 1, 1, 0, 0, 0
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 3, 1, 2, 1, 1, 0, 0, 0
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 4, 1, 3, 1, 1, 5, 0, 0
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 4, 1, 4, 1, 1, 5, 0, 0
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 4, 1, 5, 1, 1, 5, 100, 1
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 4, 1, 6, 1, 1, 5, 100, 3
        ];
        $rows = $st->execute($aData);
        // Test
        $start = new Date('2004-06-06 18:00:00');
        $end = new Date('2004-06-06 18:29:59');
        $oDalMaintenanceStatistics->saveSummary($start, $end, $aActionTypes, 'data_intermediate_ad', 'data_summary_ad_hourly');
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true);
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['number'], 8);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 1
                AND creative_id = 1";
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['zone_id'], 1);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 1);
        $this->assertEqual($aRow['total_basket_value'], 1);
        $this->assertEqual($aRow['total_revenue'], 5);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 1
                AND creative_id = 2";
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['zone_id'], 1);
        $this->assertEqual($aRow['impressions'], 2);
        $this->assertEqual($aRow['clicks'], 2);
        $this->assertEqual($aRow['conversions'], 2);
        $this->assertEqual($aRow['total_basket_value'], 2);
        $this->assertEqual($aRow['total_revenue'], 10);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 2";
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['creative_id'], 1);
        $this->assertEqual($aRow['zone_id'], 1);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 0);
        $this->assertEqual($aRow['total_basket_value'], 0);
        $this->assertEqual($aRow['total_revenue'], 2);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 3";
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['creative_id'], 1);
        $this->assertEqual($aRow['zone_id'], 2);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 0);
        $this->assertEqual($aRow['total_basket_value'], 0);
        $this->assertEqual($aRow['total_revenue'], 0);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 4
            ORDER BY
                zone_id";
        $rc = $oDbh->query($query);
        $this->assertEqual($rc->numRows(), 4);

        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['creative_id'], 1);
        $this->assertEqual($aRow['zone_id'], 3);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 5);
        $this->assertEqual($aRow['total_basket_value'], 0);
        $this->assertEqual($aRow['total_revenue'], 20);

        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['creative_id'], 1);
        $this->assertEqual($aRow['zone_id'], 4);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 5);
        $this->assertEqual($aRow['total_basket_value'], 0);
        $this->assertEqual($aRow['total_revenue'], 20);

        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['creative_id'], 1);
        $this->assertEqual($aRow['zone_id'], 5);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 5);
        $this->assertEqual($aRow['total_basket_value'], 100);
        $this->assertEqual($aRow['total_revenue'], 20);

        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['creative_id'], 1);
        $this->assertEqual($aRow['zone_id'], 6);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 5);
        $this->assertEqual($aRow['total_basket_value'], 100);
        $this->assertEqual($aRow['total_revenue'], 20);
        TestEnv::restoreEnv();

        // Test 3
        // Insert the test data
        $this->_insertTestSaveSummaryPlacement();
        $this->_insertTestSaveSummaryAd();
        $this->_insertTestSaveSummaryZone();
        $aData = [
            '2004-06-06 18:00:00', 30, 36, '2004-06-06 18:00:00', '2004-06-06 18:29:59', 1, 1, 1, 1, 1, 1, 1
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-07 18:00:00', 30, 36, '2004-06-07 18:00:00', '2004-06-07 18:29:59', 1, 2, 1, 1, 1, 1, 1
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-07 18:00:00', 30, 36, '2004-06-07 18:00:00', '2004-06-07 18:29:59', 1, 2, 1, 1, 1, 1, 1
        ];
        $rows = $st->execute($aData);
        $aData = [
            '2004-06-08 18:00:00', 30, 36, '2004-06-08 18:00:00', '2004-06-08 18:29:59', 2, 1, 1, 1, 1, 0, 0
        ];
        $rows = $st->execute($aData);
        // Test
        $start = new Date('2004-06-06 18:00:00');
        $end = new Date('2004-06-08 18:29:59');
        $oDalMaintenanceStatistics->saveSummary($start, $end, $aActionTypes, 'data_intermediate_ad', 'data_summary_ad_hourly');
        $query = "
            SELECT
                COUNT(*) AS number
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true);
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['number'], 3);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 1
                AND creative_id = 1";
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['date_time'], '2004-06-06 18:00:00');
        $this->assertEqual($aRow['zone_id'], 1);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 1);
        $this->assertEqual($aRow['total_basket_value'], 1);
        $this->assertEqual($aRow['total_revenue'], 5);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 1
                AND creative_id = 2";
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['date_time'], '2004-06-07 18:00:00');
        $this->assertEqual($aRow['zone_id'], 1);
        $this->assertEqual($aRow['impressions'], 2);
        $this->assertEqual($aRow['clicks'], 2);
        $this->assertEqual($aRow['conversions'], 2);
        $this->assertEqual($aRow['total_basket_value'], 2);
        $this->assertEqual($aRow['total_revenue'], 10);
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['data_summary_ad_hourly'], true) . "
            WHERE
                ad_id = 2";
        $aRow = $oDbh->queryRow($query);
        $this->assertEqual($aRow['date_time'], '2004-06-08 18:00:00');
        $this->assertEqual($aRow['creative_id'], 1);
        $this->assertEqual($aRow['zone_id'], 1);
        $this->assertEqual($aRow['impressions'], 1);
        $this->assertEqual($aRow['clicks'], 1);
        $this->assertEqual($aRow['conversions'], 0);
        $this->assertEqual($aRow['total_basket_value'], 0);
        $this->assertEqual($aRow['total_revenue'], 2);
        TestEnv::restoreEnv();
        TestEnv::restoreConfig();
    }

    /**
     * A private method to insert placements as test data for the
     * saveSummary() test.
     *
     * @access private
     */
    public function _insertTestSaveSummaryPlacement()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();
        $query = "
            INSERT INTO
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['campaigns'], true) . "
                (
                    campaignid,
                    revenue,
                    revenue_type
                )
            VALUES
                (?, ?, ?)";
        $aTypes = [
            'integer',
            'integer',
            'integer'
        ];
        $st = $oDbh->prepare($query, $aTypes, MDB2_PREPARE_MANIP);
        $aData = [
            1, 5000, MAX_FINANCE_CPM
        ];
        $rows = $st->execute($aData);
        $aData = [
            2, 2, MAX_FINANCE_CPC
        ];
        $rows = $st->execute($aData);
        $aData = [
            3, 4, MAX_FINANCE_CPA
        ];
        $rows = $st->execute($aData);
    }

    /**
     * A private method to insert ads as test data for the
     * saveSummary() test.
     *
     * @access private
     */
    public function _insertTestSaveSummaryAd()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();
        $query = "
            INSERT INTO
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['banners'], true) . "
                (
                    bannerid,
                    campaignid,
                    htmltemplate,
                    htmlcache,
                    url,
                    bannertext,
                    compiledlimitation,
                    append
                )
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)";
        $aTypes = [
            'integer',
            'integer',
            'text',
            'text',
            'text',
            'text',
            'text',
            'text'
        ];
        $st = $oDbh->prepare($query, $aTypes, MDB2_PREPARE_MANIP);
        $aData = [
            1, 1, '', '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            2, 2, '', '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            3, 3, '', '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            4, 3, '', '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
    }

    /**
     * A private method to insert zones as test data for the
     * saveSummary() test.
     *
     * @access private
     */
    public function _insertTestSaveSummaryZone()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();
        $query = "
            INSERT INTO
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['zones'], true) . "
                (
                    zoneid,
                    category,
                    ad_selection,
                    chain,
                    prepend,
                    append
                )
            VALUES
                (?, ?, ?, ?, ?, ?)";
        $aTypes = [
            'integer',
            'text',
            'text',
            'text',
            'text',
            'text'
        ];
        $st = $oDbh->prepare($query, $aTypes, MDB2_PREPARE_MANIP);
        $aData = [
            1, '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            2, '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            3, '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            4, '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            5, '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
        $aData = [
            6, '', '', '', '', ''
        ];
        $rows = $st->execute($aData);
    }
}
