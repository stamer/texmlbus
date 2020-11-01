<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
namespace Server;

class View
{
    public static function renderRetvalColumn(
        $retval,
        $stderrFileLink,
        $destFileLink,
        $id,
        $stage,
        $target,
        $date_modified,
        $queued
    ) {
        $str = '';
        $cfg = Config::getConfig();
        // the current retval for given stage
        if ($retval != 'unknown') {
            $color = $cfg->ret_color_sm[$cfg->ret_class[$retval]];
            $str .= '<td id="td_' . $id . '_' . $stage . '" class="'.$color.'">'.PHP_EOL;
            $str .= $retval.'<br />'.PHP_EOL;
            $str .= '<a href="'.htmlspecialchars($stderrFileLink).'">ErrFile</a><br />'.PHP_EOL;
            $str .= '<a href="'.htmlspecialchars($destFileLink).'">DestFile</a><br />'.PHP_EOL;
            $str .= $date_modified.'<br />'.PHP_EOL;
            $str .= '<a href="javascript:rerunById('.$id.',\''.$stage.'\',\''.$target.'\')">queue</a>'.PHP_EOL;
            $str .= '<span id="rerun_'.$id.'_'.$stage.'">' . $queued .'</span>'.PHP_EOL;
            $str .= '</td>'.PHP_EOL;
        } else {
            $color = $cfg->ret_color_sm[$cfg->ret_class[$retval]];
            $str .= '<td id="td_' . $id . '_' . $stage . '" class="'.$color.'">'.PHP_EOL;
            $str .= '<a href="javascript:rerunById('.$id.',\''.$stage.'\',\''.$target.'\')">queue</a>'.PHP_EOL;
            $str .= '<span id="rerun_'.$id.'_'.$stage.'">' . $queued .'</span>'.PHP_EOL;
            $str .= '</td>'.PHP_EOL;
        }
        return $str;
    }

    public static function renderPrevRetvalColumn(
        $prevRetval,
        $id,
        $stage
    ) {
        $cfg = Config::getConfig();
        $str = '';
        if ($prevRetval != 'unknown') {
            $color = $cfg->ret_color_sm[$cfg->ret_class[$prevRetval]];
            $str = '<td id="td_' . $id . '_prev' . $stage . '" class="'.$color.' tdbottomline">'.$prevRetval.'</td>'.PHP_EOL;
        } else {
            $color = $cfg->ret_color_sm[$cfg->ret_class[$prevRetval]];
            $str = '<td id="td_' . $id . '_prev' . $stage . '" class="'.$color.' tdbottomline">&nbsp;</td>'.PHP_EOL;
        }
        return $str;
    }

    public static function renderDateColumn($id, $dateModified)
    {
        return '<td id="td_' . $id . '_date" rowspan="2">'.$dateModified.'</td>' . PHP_EOL;
    }

    public static function getColumnsByRetval($stage, $retval)
    {
        $cfg = Config::getConfig();
        // use configured columns
        if (isset($cfg->stages[$stage]->retvalDetail[$retval])) {
            $columns = $cfg->stages[$stage]->retvalDetail[$retval];
        } else {
            // Default values
            $columns = array();

            switch ((string)$retval) {

                case 'missing_errlog':
                    break;

                case 'warning':
                    $columns[0]['sql'] = 'warnmsg';
                    $columns[0]['html'] = 'Warning message';
                    $columns[0]['align'] = 'left';
                    break;

                case 'missing_figure':
                case 'missing_bib':
                case 'missing_file':
                case 'error':
                case 'fatal_error':
                    $columns[0]['sql'] = 'errmsg';
                    $columns[0]['html'] = 'Error message';
                    $columns[0]['align'] = 'left';
                    break;

                case 'timeout':
                    break;

                case 'missing_macros':
                    $columns[0]['sql'] = 'num_warning';
                    $columns[0]['html'] = 'num<br />warning';
                    $columns[0]['align'] = 'right';
                    $columns[1]['sql'] = 'num_error';
                    $columns[1]['html'] = 'num<br />error';
                    $columns[1]['align'] = 'right';
                    $columns[2]['sql'] = 'missing_macros';
                    $columns[2]['html'] = 'Missing macros';
                    $columns[2]['align'] = 'left';
                    break;

                case 'success':
                    $columns[0]['sql'] = 'num_warning';
                    $columns[0]['html'] = 'num<br />warning';
                    $columns[0]['align'] = 'right';
                    $columns[1]['sql'] = 'num_error';
                    $columns[1]['html'] = 'num<br />error';
                    $columns[1]['align'] = 'right';
                    $columns[2]['sql'] = 'num_xmarg';
                    $columns[2]['html'] = 'num<br />xmarg';
                    $columns[2]['align'] = 'right';
                    $columns[3]['sql'] = 'ok_xmarg';
                    $columns[3]['html'] = 'ok<br />xmarg';
                    $columns[3]['align'] = 'right';
                    $columns[4]['sql'] = 'num_xmath';
                    $columns[4]['html'] = 'num<br />xmath';
                    $columns[4]['align'] = 'right';
                    $columns[5]['sql'] = 'ok_xmath';
                    $columns[5]['html'] = 'ok<br />xmath';
                    $columns[5]['align'] = 'right';
                    break;
            }
        }
        return $columns;
    }

    public static function renderRetvalResultColumns($stage, $retval, $row, $columns)
    {
        $str = '';
        foreach ($columns as $field) {
            $rowContent = '';
            if (is_array($field['sql'])) {
                foreach ($field['sql'] as $count => $fieldname) {
                    if ($count) {
                        $rowContent .= '<br />';
                    }
                    $rowContent .= nl2br(htmlspecialchars($row[$fieldname]));
                }
            } else {
                $rowContent .= nl2br(htmlspecialchars($row[$field['sql']]));
            }
            $str .= '<td align="' . $field['align'] . '">' . $rowContent;
            $str .= '</td>';
        }
        return $str;
    }

    public static function renderDetailRow(
        $id,
        $no,
        $directory,
        $stage,
        $target,
        $retval,
        $stderrFileLink,
        $destFileLink,
        $row,
        $columns
    ) {
        $str = '<tr id="tr_' . $id . '_' . $stage . '">';

        if ($row['wq_priority'] && $row['wq_action'] === $target) {
            $queued = 'queued';
        } else {
            $queued = '';
        }

        $str .= '<td id="td_count_' . $id . '" align="right">'.$no."</td>" . PHP_EOL;
        $str .= '<td>'.$row['date_modified']."</td>" . PHP_EOL;
        $str .= '<td><a href="'.$directory.'">'.$row['filename'].'</a></td>' . PHP_EOL;

        $str .= self::renderRetvalColumn(
            $retval,
            $stderrFileLink,
            $destFileLink,
            $id,
            $stage,
            $target,
            $row['date_modified'],
            $queued
        );

        $str .= self::renderRetvalResultColumns(
            $stage,
            $retval,
            $row,
            $columns
        );

        $str .= '</tr>' . PHP_EOL;

        return $str;
    }
}
