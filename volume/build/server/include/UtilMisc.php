<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
namespace Server;

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

    /**
     * returns the current version of latexml
     *
     * @return mixed|string
     */
    public static function getLatexmlVersion()
    {
        $cfg = Config::getConfig();


        /* cannot run latexml via webserver
        $retstr = shell_exec($cfg->app->latexml." --VERSION 2>&1");

        $arr = preg_split("/[\s)]+/", $retstr);
        if (isset($arr[3])) {
            return $arr[3];
        } else {
            return 'Unknown';
        }
        */
        /* retrieve version via MYMETA.yml */
        $latexmldir = dirname($cfg->server->app->latexml, 2);
        $metafile = $latexmldir . '/MYMETA.yml';
        if (!is_readable($metafile)) {
            return 'Unknown (1)';
        }

        $content = file_get_contents($metafile);
        $result = preg_match('/^version:\s*(.+)/m', $content, $matches);
        if (empty($matches[1])) {
            return 'Unknown (2)';
        }
        return $matches[1];
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
