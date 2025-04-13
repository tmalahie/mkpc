<?php
function print_rank($rank, $noecho=false) {
    global $language;
    $ret = $rank;
    $ret .= '<sup>';
    if ($language) {
        $hundreds = $rank%100;
        if (($hundreds >= 10) && ($hundreds < 20))
            $ret .= 'th';
        else {
            switch ($rank%10) {
            case 1 :
                $ret .= 'st';
                break;
            case 2 :
                $ret .= 'nd';
                break;
            case 3 :
                $ret .= 'rd';
                break;
            default :
                $ret .= 'th';
            }
        }
    }
    else
        $ret .= 'e'. ($rank>1 ? null:'r');
    $ret .= '</sup>';

    if ($noecho) {
        return $ret;
    } else {
        echo $ret;
    }
}

define('CELLTYPE_PROFILE', 2);
define('CELLTYPE_PLACE', 3);

/**
 * Renders a leaderboard table from the provided data.
 * @param array $rows The data to render, where each row is an associative array.
 * @return void Echoes, does not return.
 */
function renderLeaderboardTable($rows, $noTableEnd=false) {
    if (empty($rows) || empty($rows[0])) return;

    $keys = array_keys($rows[0]);

    // render the header row
    echo '<table>';
    echo '<tr id="titres">';
    foreach ($keys as $header) {
        echo "<td>$header</td>";
    }
    echo '</tr>';
    // render each row
    foreach ($rows as $idx => $row) {
        // safety check: ensure all rows have the same keys
        if (array_keys($row) !== $keys) {
            return;
        }
        
        echo '<tr class='. ($idx % 2 ? 'clair' : 'fonce') . '>';
        foreach ($row as $cell) {
            echo '<td>';
            // check if the cell is a custom type
            if (is_array($cell) && isset($cell['type'])) {
                if ($cell['type'] == CELLTYPE_PROFILE) {
                    echo "<a href='profil.php?id={$cell['id']}' class='recorder'>";

                    if ($cell['flag']) echo "<img src='images/flags/{$cell['flag']}.png' alt='{$cell['flag']}' onerror=\"this.style.display='none'\" />&nbsp;";

                    echo "{$cell['nick']}</a>";
                } elseif ($cell['type'] == CELLTYPE_PLACE) {
                    echo $cell['place'];
                }
            } else {
                echo $cell;
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    
    if (!$noTableEnd) echo '</table>';
}