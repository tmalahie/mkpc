<?php
function print_rank($rank) {
    global $language;
    echo $rank .'<sup>';
    if ($language) {
        $centaines = $rank%100;
        if (($centaines >= 10) && ($centaines < 20))
            echo 'th';
        else {
            switch ($rank%10) {
            case 1 :
                echo 'st';
                break;
            case 2 :
                echo 'nd';
                break;
            case 3 :
                echo 'rd';
                break;
            default :
                echo 'th';
            }
        }
    }
    else
        echo 'e'. ($rank>1 ? null:'r');
    echo '</sup>';
}