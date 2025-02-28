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

// takes globals for tests
// takes arguments when run from cli

$path = dirname(dirname(dirname(__FILE__)));

global $readPath, $writeFile;

    if (!empty($argc)) {
        $readPath = $argv[1];
        $writeFile = $argv[2];
        echo 'reading directory ' . $readPath . "\n";
        echo 'writing file ' . $writeFile . "\n";
    }
    if (empty($readPath)) {
        $readPath = $path . '/etc/changes';
    }
    if (empty($writeFile)) {
        $writeFile = $path . '/etc/changes/openads_upgrade_array.txt';
    }

    $fp = fopen($writeFile, 'w');
    if ($fp === false) {
        echo __FILE__ . ' : unable to open output file ' . $writeFile . "\n";
        exit();
    }

    $aVersions = [];

    foreach (glob($readPath . '/*_upgrade_*.xml') as $file) {
        $file = basename($file);

        if (preg_match('/_upgrade_[\w\W]+\.xml$/', $file, $aMatches)) {
            preg_match('/(?P<release>[\d]+)\.(?P<major>[\d]+)\.(?P<minor>[\d]+)(?P<beta>\-beta)?(?P<rc>\-rc)?(?P<build>[\d]+)?(?P<toversion>_to_)?/i', $file, $aParsed);

            // we don't want *milestone* packages included in this array  (openads_upgrade_n.n.nn_to_n.n.nn.xml)
            if (empty($aParsed['toversion'])) {
                $release = $aParsed['release'] ?? null;
                $major = $aParsed['major'] ?? null;
                $minor = $aParsed['minor'] ?? null;
                $beta = $aParsed['beta'] ?? null;
                $rc = $aParsed['rc'] ?? null;
                $build = $aParsed['build'] ?? null;

                if (!isset($aVersions[$release])) {
                    $aVersions[$release] = [];
                }
                if (!isset($aVersions[$release][$major])) {
                    $aVersions[$release][$major] = [];
                }
                if (!isset($aVersions[$release][$major][$minor])) {
                    $aVersions[$release][$major][$minor] = [];
                }
                if ($rc && $beta) {
                    $aVersions[$release][$major][$minor][$beta . $rc][$build]['file'] = $file;
                } elseif ($beta) {
                    $aVersions[$release][$major][$minor][$beta]['file'] = $file;
                } elseif ($rc) {
                    $aVersions[$release][$major][$minor][$rc][$build]['file'] = $file;
                } else {
                    $aVersions[$release][$major][$minor]['file'] = $file;
                }
            }
        }
    }

    $array = serialize($aVersions);
    $x = fwrite($fp, $array);
    fclose($fp);
