<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
namespace Server;

use Dmake\ApiWorkerRequest;
use Dmake\UtilFile;
use Dmake\UtilStage;
use Dmake\UtilBindingFile;

class UtilMisc
{
    /**
     */
    public static function navigator(
        int $current,
        int $min,
        int $max_pp,
        int $count = -1
    ): void
    {
        global $IS_CRAWLER;

        if ($IS_CRAWLER) {
            return;
        }
        echo '<div style="text-align:center">' . PHP_EOL;
        $prev = max(0, $min - $max_pp);
        $next = $min + $max_pp;
        $param = '';
        foreach ($_GET as $key => $val) {
            if ($key === 'min') {
                continue;
            }
            $param .= '&amp;'.$key.'='.urlencode($val);
        }
        $prevparam = preg_replace('/^&amp;/', '?', $param.'&amp;min='.$prev);
        $nextparam = preg_replace('/^&amp;/', '?', $param.'&amp;min='.$next);
        if ($min > 0) {
            $prevurl = $_SERVER['PHP_SELF'].$prevparam;
            echo '<a href="'.$prevurl.'">&lt;&lt;&lt;</a> ';
        } else {
            echo '&nbsp;&nbsp;&nbsp;';
        }
        if ($next < $count) {
            $nexturl = $_SERVER['PHP_SELF'] . $nextparam;
            echo '<a href="' . $nexturl . '">&gt;&gt;&gt;</a> ';
        } else {
            echo '&nbsp;&nbsp;&nbsp;';
        }
        echo '<br /><br />';
        for ($i = 0; $i * $max_pp < $count; $i++) {
            $newMin = $i * $max_pp;
            $thisparam = preg_replace('/^&amp;/', '?', $param . '&amp;min=' . $newMin);
            $thisurl = $_SERVER['PHP_SELF'] . $thisparam;
            if ($min === $newMin) {
                echo '<b>';
            }
            echo '<a href="'.$thisurl.'">[' . (($newMin) + 1) . ']</a> ';
            if ($min === $newMin) {
                echo '</b>';
            }
        }
        echo '</div>' . PHP_EOL;
    }

    public static function getActiveHostGroups(): array
    {
        // the server does not know the current hosts
        $activeStages = UtilStage::loadActiveStages();
        $hosts = [];
        foreach ($activeStages as $stage) {
            // automatically remove duplicates
            $hosts[UtilStage::getHostGroupByStage($stage)] = 1;
        }
        $hostGroups = array_keys($hosts);
        return $hostGroups;
    }

    /**
     * get current version of latexml for each HostGroup
     */
    public static function getLatexmlVersion(): array
    {
        $hostGroups = self::getActiveHostGroups();

        $retArr = [];
        foreach ($hostGroups as $hostGroupName) {

            $apr = new ApiWorkerRequest();
            $apr->setWorker($hostGroupName)
                ->setCommand('latexmlversion');
            $apiResult = $apr->sendRequest();
            $retStr = implode("\n", $apiResult->getOutput());

            $arr = preg_split("/[\s)]+/", $retStr);
            if (isset($arr[3])) {
                $retArr[] = $hostGroupName
                            . ': ' . $arr[3]
                            . ' '
                            . ($arr[4] ?? '')
                            . ' '
                            . ($arr[5] ?? '');

            } else {
                $retArr[] = $hostGroupName . ': Unknown';
            }
        }
        return $retArr;
    }

    /**
     * expects just filename
     */
    public static function getLtxmlLink(string $stylefile): string
    {
        static $ltxmlFiles = [];
        if (empty($ltxmlFiles['cls'])) {
            $ltxmlFiles['cls'] = UtilBindingFile::getClsFiles(true);
        }
        if (empty($ltxmlFiles['sty'])) {
            $ltxmlFiles['sty'] = UtilBindingFile::getStyFiles(true);
        }

        $suffix = UtilFile::getSuffix($stylefile, false);
        $stylefile = basename($stylefile);
        $ltxfile = $stylefile . '.ltxml';

        if (isset($ltxmlFiles[$suffix][$stylefile])) {
            if ($ltxmlFiles[$suffix][$stylefile] === 'latexml') {
                $ltxlink = '<span class="ok">oo </span><a href="ltx_sty/' . $ltxfile . '">ltx_sty/' . $ltxfile . '</a>';
            } else {
                $ltxlink = '<span class="ok">o </span><a href="sty/' . $ltxfile . '">sty/' . $ltxfile . '</a>';
            }
        } else {
            $ltxlink = '<span class="warn">x </span>';
        }
        return $ltxlink;
    }

    /**
     *
     * parses Browscap and returns TRUE if User Agent is
     * determined to be a crawler or robot.
     * This should only be called on slow paths
     */
    public static function isCrawler(): bool
    {
        if (stristr($_SERVER['HTTP_USER_AGENT'], 'googlebot')) {
            return true;
        }

        $cfg = Config::getConfig();
        if (isset($cfg->browscap)
            && !empty($cfg->browscap->file))
        {
            require_once($cfg->file);
            /*
            $br = new Browscap($cfg->browscap->dir);
            if ($br->getBrowser()->Crawler) {
                return true;
            } else {
                return false;
            }
            */
            return false;
        }
        return false;
    }
}
