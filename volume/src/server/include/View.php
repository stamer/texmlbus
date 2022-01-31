<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
namespace Server;

class View
{
    /**
     * Render a single result cell.
     */
    public static function renderRetvalCell(
        ?string $retval,
        string $stdErrFileLink,
        string $destFileLink,
        string $id,
        string $stage,
        string $target,
        ?string $date_modified,
        string $queued
    ): string
    {
        $str = '';
        $cfg = Config::getConfig();
        if ($retval === null) {
            $retval = 'unknown';
        }
        // the current retval for given stage
        if ($retval != 'unknown') {
            $color = $cfg->ret_color_sm[$cfg->ret_class[$retval]];
            $str .= '<td id="td_' . $id . '_' . $stage . '" class="'.$color.'">'.PHP_EOL;
            $str .= $retval.'<br />'.PHP_EOL;
            $str .= '<a href="'.htmlspecialchars($stdErrFileLink).'">ErrFile</a><br />'.PHP_EOL;
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

    /**
     * Render a previous result cell.
     */
    public static function renderPrevRetvalCell(
        ?string $prevRetval,
        string $id,
        string $stage
    ): string
    {
        $cfg = Config::getConfig();
        if ($prevRetval != 'unknown') {
            $color = $cfg->ret_color_sm[$cfg->ret_class[$prevRetval]];
            $str = '<td id="td_' . $id . '_prev' . $stage . '" class="'.$color.' tdbottomline">'.$prevRetval.'</td>'.PHP_EOL;
        } else {
            $color = $cfg->ret_color_sm[$cfg->ret_class[$prevRetval]];
            $str = '<td id="td_' . $id . '_prev' . $stage . '" class="'.$color.' tdbottomline">&nbsp;</td>'.PHP_EOL;
        }
        return $str;
    }

    /**
     * Render a date cell.
     */
    public static function renderDateCell(string $id, string $dateModified): string
    {
        return '<td class="right" id="td_' . $id . '_date" rowspan="2">' . $dateModified
                . '<br /><br /><span class="grey">' . $id . '</span><br>'
                . '<button type="button" class="btn btn reset abc_reset" title="Reset document" onclick="resetDocument(this, ' . $id . ')">'
                . '<i class="fas fa-undo"></i>'
                . '<span></span></button>'

                . '</td>' . PHP_EOL;
    }

    /**
     * Create columns depending on $stage and $retval.
     * @return array
     */
    public static function getColumnsByRetval(string $stage, string $retval): array
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

    /**
     * Render retval result columns.
     */
    public static function renderRetvalResultColumns(
        string $stage,
        string $retval,
        array $row,
        array $columns
    ): string
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
                if (!empty($field['detail'])) {
                    $id = $row['id'];
                    $rowContent .= '
<div class="accordion" id="accordion' . $id . '">
  <div class="card">
    <div class="card-header" id="heading' . $id . '">
      <div class="mb-0">
        <a data-toggle="collapse" href="#collapse' . $id . '" aria-expanded="false" aria-controls="collapse' . $id .'">
                ' . nl2br(htmlspecialchars(mb_substr($row[$field['sql']], 0, 60))) . '
        </a>
      </div>
    </div>
     <div id="collapse' . $id . '" class="collapse" aria-labelledby="heading' . $id . '" data-parent="#accordion' . $id . '">
      <div class="card-body">
        ' . nl2br(htmlspecialchars($row[$field['sql']])) . '
      </div>
    </div>
  </div>
</div>';
                } else {
                    $rowContent .= nl2br(htmlspecialchars($row[$field['sql']]));
                }
            }
            $str .= '<td align="' . $field['align'] . '">' . $rowContent;

            $str .= '</td>';
        }
        return $str;
    }

    /**
     * Render a whole result row.
     */
    public static function renderDetailRow(
        string $id,
        string $no,
        string $directory,
        string $stage,
        string $target,
        string $retval,
        string $stdErrFileLink,
        string $destFileLink,
        array $row,
        array $columns
    ): string
    {
        $str = '<tr id="tr_' . $id . '_' . $stage . '">';

        if ($row['wq_action'] === $target) {
            if ($row['wq_priority']) {
                $queued = 'queued';
            } else {
                $queued = 'running';
            }
        } else {
            $queued = '';
        }

        $str .= '<td id="td_count_' . $id . '" align="right">'.$no."</td>" . PHP_EOL;
        $str .= '<td class="right">'.$row['date_modified'].'<br /><br /><span class="grey">' . $id . '</span></td>' . PHP_EOL;
        $str .= '<td><a href="'.$directory.'">'.$row['filename'].'</a></td>' . PHP_EOL;

        $str .= self::renderRetvalCell(
            $retval,
            $stdErrFileLink,
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
