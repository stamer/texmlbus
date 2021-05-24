<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
namespace Server;

use Dmake\ApiWorkerRequest;
use Dmake\UtilStage;

class UtilMisc
{
    /**
     * @param $current
     * @param $min
     * @param $max_pp
     * @param int $count
     */
    public static function navigator($current, $min, $max_pp, $count = -1)
    {
        global $IS_CRAWLER;

        if ($IS_CRAWLER) return;
        echo '<div style="text-align:center">' . PHP_EOL;
        $prev = max(0, $min - $max_pp);
        $next = $min + $max_pp;
        $param = '';
        foreach ($_GET as $key => $val) {
            if ($key == 'min') continue;
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
            $thisparam = preg_replace('/^&amp;/', '?', $param.'&amp;min='.$i * $max_pp);
            $thisurl = $_SERVER['PHP_SELF'].$thisparam;
            echo '<a href="'.$thisurl.'">['.(($i * $max_pp) + 1) . ']</a> ';
        }
        echo '</div>' . PHP_EOL;
    }

    public static function getActiveHostGroups()
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
     *
     * @return mixed|string
     */
    public static function getLatexmlVersion()
    {
        $cfg = Config::getConfig();

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
     *
     * @param $ltxfile
     * @return string
     */
    public static function getLtxmlLink($ltxfile) {
        $ltxfile = basename($ltxfile);
        //echo '<pre>'.HTDOCS.'/sty/'.$ltxfile;
        //exit;
        if (is_readable(HTDOCS.'/sty/'.$ltxfile)) {
            //$ltxlink = '<a href="sty/'.$ltxfile.'">sty/'.$ltxfile.'</a> (rel. '.$info['release'].' by '.$info['user'].')';
            $ltxlink = '<span class="ok">o </span><a href="sty/'.$ltxfile.'">sty/'.$ltxfile.'</a>';
        } elseif (is_file(STYARXMLIVDIR.'/'.$ltxfile)) {
            $info['user'] = '';
            //$ltxlink = '<span class="ok">o </span><a href="ltx_sty/'.$ltxfile.'">ltx_sty/'.$ltxfile.'</a>';
            $ltxlink = '<span class="ok">oo </span><a href="ltx_sty/'.$ltxfile.'">ltx_sty/'.$ltxfile.'</a>';
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
    public static function isCrawler()
    {
        if (stristr($_SERVER['HTTP_USER_AGENT'], 'googlebot')) {
            return TRUE;
        }

        $cfg = Config::getConfig();
        if (isset($cfg->browscap)
            && !empty($cfg->browscap->file))
        {
            require_once($cfg->file);
            $br = new Browscap($cfg->browscap->dir);
            if ($br->getBrowser()->Crawler) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
}
