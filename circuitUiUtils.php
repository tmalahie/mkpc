<?php
function printDecorTypeSelector() {
    global $language;
    ?>
    Type:
    <div class="radio-selector" id="decor-selector" data-change="decorChange">
        <?php
        require_once('circuitDecors.php');
        foreach ($decors as $i=>$decorNames) {
            if ($i) echo '<br />';
            foreach ($decorNames as $decorName=>$title)
                echo '<button value="'.$decorName.'" class="radio-button radio-button-25 radio-button-decor button-img'.($title ? ' fancy-title':'').'" style="background-image:url(\'images/map_icons/'.$decorName.'.png\')"'.($title ? ' title="'.$title.'"':'').'></button>';
        }
        ?><button id="decor-selector-more" class="radio-button-25 button-img fancy-title" title="<?php echo $language ? 'Custom decor...':'Éditeur de décors...'; ?>" onclick="showDecorEditor()"></button>
    </div>
    <div id="decor-options">
        <div id="decor-option-truck" class="decor-option-bus-decors">
            <div id="decor-bus-decors">
                <label>
                    <img src="images/map_icons/truck.png" alt="Bus" />
                    <?php echo $language ? 'Route:':'Trajet :'; ?>
                    <select name="decor-bus-currenttraject" id="decor-bus-currenttraject" onchange="currentTrajectChange(this.value)"></select>
                </label>
                <a href="javascript:manageBusTrajects()"><?php echo $language ? 'Manage bus route...':'Gérer les trajets...'; ?></a>
            </div>
            <div id="decor-bus-trajects">
                <a href="javascript:manageBusDecor()"><?php echo $language ? 'Back':'Retour'; ?></a>&nbsp;
                <select name="decor-bus-traject" id="decor-bus-traject" onchange="trajectChange(this.value,'bus')">
                </select>
            </div>
        </div>
    </div>
    <?php
}
function printBgSelector() {
    global $language, $bgImages;
    ?>
    <div id="bg-selector" class="fs-popup" onclick="event.stopPropagation()">
        <div id="bg-selector-tabs">
        <?php
        $decors = Array (
            'SNES' => array_slice($bgImages, 0,8),
            'GBA' => array_slice($bgImages, 8,20),
            'DS' => array_slice($bgImages, 28,20),
            ' + ' => null
        );
        $i = 0;
        foreach ($decors as $name=>$decorGroup) {
            echo '<a id="bg-selector-tab-'.$i.'" href="javascript:showBgTab('.$i.')">'.$name.'</a>';
            $i++;
        }
        ?>
        </div>
        <div id="bg-selector-options">
        <?php
        $i = 0;
        $j = 0;
        foreach ($decors as $name=>$decorGroup) {
            echo '<div class="bg-selector-optgroup" data-value="'.$i.'" id="bg-selector-optgroup-'.$i.'"'.($decorGroup===null ? ' data-custom="1"':'').'>';
            if (null === $decorGroup) {
                ?>
                <div class="add-custom-bg" onclick="createCustomBg()">
                    <?php
                    echo '<span><strong>+</strong> '. ($language ? "Go to background editor..." : "Éditeur d'arrière-plans...") .'</span>';
                    ?>
                </div>
                <?php
            }
            else {
                foreach ($decorGroup as $decor) {
                    ?>
                    <div id="bgchoice-<?php echo $j; ?>" data-value="<?php echo $j; ?>" onclick="changeBg(this)">
                        <?php
                        foreach ($decor as $img)
                            echo '<span style="background-image:url(\'images/map_bg/'.$img.'.png\')"></span>';
                        ?>
                    </div>
                    <?php
                    $j++;
                }
            }
            echo '</div>';
            $i++;
        }
        ?>
        </div>
    </div>
    <?php
}