<?php
/**
 * MIT License
 * (c) 2007 - 2021 Heinrich Stamerjohanns
 *
 * A class that converts latexml test cases to a directory structure.
 * It is able to automatically create directories for new files, but
 * for new included files some manual adjustment might be necessary.
 */
namespace Dmake;

class LatexmlTestCases
{
    /*
     * Files that are to be ignored. They are either not working on purpose,
     * or are not part of tests, bt rather included in other tests.
     */
    public static $ignoreFiles = [
        'daemon/broken.tex',
        'expansion/endinputinner.tex',
        'expansion/endinputinner2.tex',
        'expansion/fragment1.tex',
        'expansion/fragment2.tex',
        'expansion/whichfrag1.tex',
        'expansion/whichfrag2.tex',
        'namespace/ns1.tex',
        'namespace/ns2.tex',
        'namespace/ns3.tex',
        'namespace/ns4.tex',
        'namespace/ns5.tex',
        'structure/asubfile.tex',
    ];

    /**
     * @var \string[][]
     * These files need to be copied from src to dest. They are typically included in
     * several tests, and therefore need to be in multiple directories.
     */
    public static $addFiles = [
        ['src' => '../doc/graphics/mascot.png', 'dest' => 'moderncv_cs_cv/mascot.png'],
        ['src' => 'alignment/any.sty.ltxml', 'dest' => 'alignment_listing/any.sty.ltxml'],
        ['src' => 'alignment/listing.tex', 'dest' => 'alignment_listing/listing.tex'],
        ['src' => 'alignment/foo.png', 'dest' => 'alignment_vmode/foo.png'],
        ['src' => 'complex/sunset.jpg', 'dest' => 'complex_aliceblog/sunset.jpg'],
        ['src' => 'complex/sunset.jpg', 'dest' => 'complex_labelled/sunset.jpg'],
        ['src' => 'daemon/tiny.bib', 'dest' => 'daemon_amsarticle/tiny.bib'],
        ['src' => 'expansion/endinputinner.tex', 'dest' => 'expansion_endinput/endinputinner.tex'],
        ['src' => 'expansion/endinputinner2.tex', 'dest' => 'expansion_endinput/endinputinner2.tex'],
        ['src' => 'expansion/fragment1.tex', 'dest' => 'expansion_testinput/fragment1.tex'],
        ['src' => 'expansion/fragment2.tex', 'dest' => 'expansion_testinput/fragment2.tex'],
        // destfilename must change..
        ['src' => 'expansion/testinput.foo', 'dest' => 'expansion_testinput/expansion_testinput.foo'],
        ['src' => 'expansion/whichpkga.sty', 'dest' => 'expansion_whichcache/whichpkga.sty'],
        ['src' => 'expansion/subdir', 'dest' => 'expansion_whichcache/subdir/'],
        ['src' => 'expansion/whichfrag1', 'dest' => 'expansion_whichinput/whichfrag1'],
        ['src' => 'expansion/whichfrag1.tex', 'dest' => 'expansion_whichinput/whichfrag1.tex'],
        ['src' => 'expansion/whichfrag1.tex.tex', 'dest' => 'expansion_whichinput/whichfrag1.tex.tex'],
        ['src' => 'expansion/whichfrag2', 'dest' => 'expansion_whichinput/whichfrag2'],
        ['src' => 'expansion/whichfrag2.tex', 'dest' => 'expansion_whichinput/whichfrag2.tex'],
        ['src' => 'expansion/whichfrag3', 'dest' => 'expansion_whichinput/whichfrag3'],
        ['src' => 'expansion/whichpkga.sty', 'dest' => 'expansion_whichinput/whichpkga.sty'],
        ['src' => 'expansion/whichpkgb.sty.sty', 'dest' => 'expansion_whichinput/whichpkgb.sty.sty'],
        ['src' => 'keyval/mykeyval.sty', 'dest' => 'keyval_keyvalstyle/mykeyval.sty'],
        ['src' => 'keyval/mykeyval.sty.ltxml', 'dest' => 'keyval_keyvalstyle/mykeyval.sty.ltxml'],
        ['src' => 'keyval/myxkeyval.sty', 'dest' => 'keyval_xkeyvalstyle/myxkeyval.sty'],
        ['src' => 'keyval/myxkeyval.sty.ltxml', 'dest' => 'keyval_xkeyvalstyle/myxkeyval.sty.ltxml'],
        ['src' => 'keyval_options/xkvdop1.*', 'dest' => 'keyval_options_xkvdop1a/'],
        ['src' => 'keyval_options/xkvdop1a.*', 'dest' => 'keyval_options_xkvdop1a/'],
        ['src' => 'keyval_options/xkvdop1.*', 'dest' => 'keyval_options_xkvdop1b/'],
        ['src' => 'keyval_options/xkvdop1b.*', 'dest' => 'keyval_options_xkvdop1b/'],
        ['src' => 'keyval_options/xkvdop2.*', 'dest' => 'keyval_options_xkvdop2a/'],
        ['src' => 'keyval_options/xkvdop2a.*', 'dest' => 'keyval_options_xkvdop2a/'],
        ['src' => 'keyval_options/xkvdop2.*', 'dest' => 'keyval_options_xkvdop2b/'],
        ['src' => 'keyval_options/xkvdop2b.*', 'dest' => 'keyval_options_xkvdop2b/'],
        ['src' => 'keyval_options/xkvdop3.*', 'dest' => 'keyval_options_xkvdop3a/'],
        ['src' => 'keyval_options/xkvdop3a.*', 'dest' => 'keyval_options_xkvdop3a/'],
        ['src' => 'keyval_options/xkvdop3.*', 'dest' => 'keyval_options_xkvdop3b/'],
        ['src' => 'keyval_options/xkvdop3b.*', 'dest' => 'keyval_options_xkvdop3b/'],
        ['src' => 'keyval_options/xkvdop4.*', 'dest' => 'keyval_options_xkvdop4a/'],
        ['src' => 'keyval_options/xkvdop4a.*', 'dest' => 'keyval_options_xkvdop4a/'],
        ['src' => 'keyval_options/xkvdop5.*', 'dest' => 'keyval_options_xkvdop5a/'],
        ['src' => 'keyval_options/xkvdop5a.*', 'dest' => 'keyval_options_xkvdop5a/'],
        ['src' => 'keyval_options/xkvdop5.*', 'dest' => 'keyval_options_xkvdop5b/'],
        ['src' => 'keyval_options/xkvdop5b.*', 'dest' => 'keyval_options_xkvdop5b/'],
        ['src' => 'keyval_options/xkvdop6.*', 'dest' => 'keyval_options_xkvdop6a/'],
        ['src' => 'keyval_options/xkvdop6a.*', 'dest' => 'keyval_options_xkvdop6a/'],
        ['src' => 'keyval_options/xkvdop6.*', 'dest' => 'keyval_options_xkvdop6b/'],
        ['src' => 'keyval_options/xkvdop6b.*', 'dest' => 'keyval_options_xkvdop6b/'],
        ['src' => 'structure/asubfile.tex', 'dest' => 'structure_mainfile/asubfile.tex'],
        ['src' => 'structure/filelistclass.cls', 'dest' => 'structure_filelist/filelistclass.cls'],
        ['src' => 'structure/filelistclass.cls.ltxml', 'dest' => 'structure_filelist/filelistclass.cls.ltxml'],
        ['src' => 'structure/mainfile.tex', 'dest' => 'structure_mainfile/mainfile.tex'],
        ['src' => 'structure/myclass.cls', 'dest' => 'structure_options/myclass.cls'],
        ['src' => 'structure/myclass.cls.ltxml', 'dest' => 'structure_options/myclass.cls.ltxml'],
        ['src' => 'structure/apackage.sty', 'dest' => 'structure_options/apackage.sty'],
        ['src' => 'structure/apackage.sty.ltxml', 'dest' => 'structure_options/apackage.sty.ltxml'],
        ['src' => 'structure/lit.bib', 'dest' => 'structure_bibsect/lit.bib'],
        ['src' => 'tokenize/snippet.tex', 'dest' => 'tokenize_verb/snippet.tex'],
    ];

    /**
     * Some files might need to be rewritten.
     * Use 'search' for string replace or 'pattern' for regular expressions.
     */
    public static $rewriteFiles = [
        'moderncv_cs_cv/moderncv_cs_cv.tex' => [
        'search' => '../../doc/graphics/mascot.png',
        'replace' => 'mascot.png'
        ],
        'tokenize_verbata/tokenize_verbata.tex' => [
        'search' => 'verbata.tex',
        'replace' => 'tokenize_verbata.tex'
        ],
    ];

    public function create()
    {
        $cfg = Config::getConfig();

        $testDir = '/opt/latexml/t';
        $destDir = ARTICLEDIR . '/latexml-test';

        if (!is_dir($destDir)) {
            $success = mkdir($destDir, 0777);
            if (!$success) {
                die("Unable to mkdir $destDir");
            }
        }

        $result_dirs = array();
        $current_depth = 0;
        UtilFile::listDirR($testDir, $result_dirs, $current_depth, true, false, '/\.tex$/');

        foreach ($result_dirs as $filename) {
            $shortfile = str_replace($testDir . '/', '', $filename);
            if (!in_array($shortfile, self::$ignoreFiles)) {
                $shortfile = str_replace('/', '_', $shortfile);
                $shortdir = preg_replace('/\.tex$/', '', $shortfile);
                // echo $shortfile . PHP_EOL;
                $myDestDir = $destDir . '/' . $shortdir;
                mkdir($myDestDir, 0777);
                copy($filename, $myDestDir . '/' . $shortfile);
            }
        }

        foreach (self::$addFiles as $addFile) {
            $srcFile = $testDir . '/' . $addFile['src'];
            $destFile = $destDir . '/' . $addFile['dest'];

            if (strpos($srcFile, '*') !== false) {
                foreach (glob($srcFile) as $file) {
                    $myDestDir = pathinfo($destFile, PATHINFO_DIRNAME);
                    if (!is_dir($myDestDir)) {
                        mkdir($myDestDir, 0777, true);
                    }
                    $destfilename = $destFile . pathinfo($file, PATHINFO_BASENAME);
                    // echo "Copying $file to $destfilename" . PHP_EOL;
                    copy($file, $destfilename);
                }
            } else {
                UtilFile::copyR($srcFile, $destFile);
            }
        }

        $result = chdir($destDir);
        if (!$result) {
            error_log(__METHOD__ . ": Unable to chdir to $destDir");
        }
        foreach (self::$rewriteFiles as $rewriteFile => $val) {
            $content = file_get_contents($rewriteFile);
            if (isset($val['pattern'])) {
                $content = preg_replace($val['pattern'], $val['replace'], $content);
            } else {
                $content = str_replace($val['search'], $val['replace'], $content);
            }
            $result = file_put_contents($rewriteFile, $content);
            if (!$result) {
                error_log(__METHOD__ . ": Unable to write to $rewriteFile");
            }
        }
    }
}