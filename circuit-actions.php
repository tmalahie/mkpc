<?php
require_once('collabUtils.php');
function includeShareLib() {
    global $nid, $creationType, $isCup, $isMCup, $isBattle, $cupIDs, $clId, $sid, $identifiants, $language, $creator, $canShare, $canChange, $creationMode, $trackEditPage;
    $isBattle = ($creationMode > 1);
    $complete = ($creationMode%2);
    include('creation-entities.php');
    $creationType = $isMCup ? 'mkmcups':($isCup ? 'mkcups':$CREATION_ENTITIES[$creationMode]['table']);
    $collab = getCollabLinkFromQuery($creationType, $nid);
    if (isset($nid)) {
        $creator = mysql_numrows(mysql_query('SELECT * FROM `'.$creationType.'` WHERE id="'. $nid.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]));
        $canChange = $creator || isset($collab['rights']['view']);
        $canShare = $creator || isset($collab['rights']['edit']);
    }
    else {
        $creator = true;
        $canChange = true;
        $canShare = true;
    }
    if ($canChange) {
        if (!$isCup) {
            $shareParams = $CREATION_ENTITIES[$creationMode]['get_share_params']();
            $trackEditPage = $shareParams['edit']['page'];
        }
        ?>
        function saveRace() {
            document.getElementById("cAnnuler").disabled = true;
            document.getElementById("cAnnuler").className = "cannotChange";
            document.getElementById("cEnregistrer").disabled = true;
            document.getElementById("cEnregistrer").className = "cannotChange";
            xhr("<?php echo ($isMCup ? 'saveMCup' : ($isCup?'saveCup':$shareParams['send']['endpoint'])); ?>", "<?php
            if ($isCup) {
                echo 'mode='. $creationMode;
                foreach ($cupIDs as $i=>$cupID)
                    echo '&cid'. $i .'='. $cupID;
                if (!empty($cOptions))
                    echo '&opt="+ encodeURIComponent(JSON.stringify(cupOpts)) +"';
                echo '&';
            }
            elseif ($shareParams['send']['params']) {
                echo $shareParams['send']['params'];
                echo '&';
            }
            if (isset($nid)) echo 'id='.$nid;
            if ($clId) echo '&cl='.$clId;
            if ($collab) echo '&collab='.$collab['key'];
            if ($isCup)
                echo '"+getCollabQuery("'. ($isMCup ? 'mkcups':$CREATION_ENTITIES[$creationMode]['table']) .'", ['. implode(',',$cupIDs) .'])+"';
            ?>&nom="+ getValue("cName") +"&auteur="+ getValue("cPseudo"), function(reponse) {
                if (reponse && !isNaN(reponse)) {
                    document.getElementById("cSave").removeChild(document.getElementById("cTable"));
                    var cP = document.createElement("p");
                    cP.style.margin = "5px";
                    cP.style.textAlign = "center";
                    cP.innerHTML = '<?php
                        if ($isCup ? isset($nid) : isset($shareParams['remove']))
                            echo $language ? ($isCup ? 'Cup':($isBattle ? 'Arena' : 'Circuit')) .' updated successfully.':'Le partage de votre '. ($isCup ? 'coupe':($isBattle ? 'arène' : 'circuit')) .' a été mis à jour.';
                        else
                            echo $language ? 'Your '. ($isCup ? 'cup':($isBattle ? 'arena' : 'circuit')) .' has just been added to the <a href="creations.php" target="_blank">list</a>!':'Votre '. ($isCup ? 'coupe':($isBattle ? 'arène':'circuit')) .' vient d\\\'être ajoutée à la <a href="creations.php" target="_blank">liste</a> !';
                    ?><br /><br />';
                    var cCont = document.createElement("input");
                    cCont.type = "button";
                    cCont.value = language ? "Continue":"Continuer";
                    cCont.onclick = function() {
                        <?php
                        echo 'document.location.href = "?'.$sid.'="+ reponse';
                        if ($collab) echo '+"&collab='.$collab['key'].'"';
                        echo ';';
                        ?>
                    };
                    cP.appendChild(cCont);
                    document.getElementById("cSave").appendChild(cP);
                    document.getElementById("changeRace").onclick = function() {
                        document.location.href = "<?php
                            if ($isCup) {
                                if ($complete)
                                    echo ($isMCup ? 'completecups':'completecup');
                                else
                                    echo ($isMCup ? 'simplecups':'simplecup');
                            }
                            else
                                echo $shareParams['edit']['page'];
                            ?>?<?php echo $sid; ?>="+ reponse +"<?php echo $isCup ? '&battle':''; ?>";
                    };
                    return true;
                }
                return false;
            });
        }
        <?php
        if (isset($shareParams['remove'])) {
            ?>
        function supprRace() {
            document.getElementById("sAnnuler").disabled = true;
            document.getElementById("sAnnuler").className = "cannotChange";
            document.getElementById("sConfirmer").disabled = true;
            document.getElementById("sConfirmer").className = "cannotChange";
            xhr("<?php echo ($isMCup ? 'supprMCup':($isCup ? 'supprCup':$shareParams['remove']['endpoint'])); ?>", "<?php
                echo 'id='.$nid;
                if ($collab) echo '&collab='.$collab['key'];
            ?>", function(reponse) {
                if (reponse == 1) {
                    document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The '. ($isCup ? 'cup':($isBattle ? 'arena':'circuit')) .' has been successfully removed from the list.':($isCup ? 'La coupe':($isBattle ? "L\\'arène":'Le circuit')) .' a été '. ($isCup||$isBattle ? 'retirée':'retiré') .' de la liste avec succès.'; ?>';
                    document.getElementById("supprButtons").innerHTML = '';
                    var cCont = document.createElement("input");
                    cCont.type = "button";
                    cCont.value = language ? "Continue":"Continuer";
                    cCont.onclick = function() {
                        <?php
                        if ($isMCup) {
                            echo 'document.location.href = "?';
                            foreach ($cupIDs as $i => $cupID) {
                                if ($i)
                                    echo '&';
                                echo 'mid'. $i .'='. $cupIDs[$i];
                            }
                            if (!empty($cOptions))
                                echo '&opt='. urlencode($cOptions);
                            echo '";';
                        }
                        elseif ($isCup) {
                            echo 'document.location.href = "?';
                            for ($i=0;$i<4;$i++) {
                                if ($i)
                                    echo '&';
                                echo 'cid'. $i .'='. $cupIDs[$i];
                            }
                            echo '";';
                        }
                        else
                            echo $shareParams['remove']['onSuccess'];
                        ?>
                    };
                    document.getElementById("supprButtons").appendChild(cCont);
                    document.getElementById("changeRace").disabled = true;
                    document.getElementById("shareRace").disabled = true;
                    return true;
                }
                return false;
            });
        }
            <?php
        }
        ?>
        function getValue(name) {
            return encodeURIComponent(document.getElementById(name).value);
        }
        <?php
    }
    else {
        require_once('utils-ratings.php');
        $cNote = getMyRating($creationType, $nid);
        ?>
        var cNote = <?php echo $cNote ?>;
        var ratingParams = "id=<?php
            echo $nid;
            if ($isMCup)
                echo '&mc=1';
            elseif ($isCup)
                echo '&cup=1';
        ?>";
        <?php
    }
}

function printCircuitActions() {
    global $language, $canChange, $isCup, $isMCup, $isBattle, $canShare, $creator, $creationType, $trackEditPage, $nid, $sid, $cShared;
    include('ip_banned.php');
    if (isBanned())
        echo '&nbsp;';
    elseif ($canChange) {
        $typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')) : ($isBattle ? ($language ? 'arena':'l\'arène') : ($language ? 'circuit':'le circuit'));
        ?>
        <input type="button" id="changeRace"<?php if (!$creator) echo ' data-collab="1"'; ?> onclick="document.location.href='<?php echo ($isCup ? ($isMCup ? 'simplecups.php':'simplecup.php'):$trackEditPage) ?>'+document.location.search<?php if ($isCup&&$isBattle) echo '+\'&battle\''; ?>" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" /><br /><?php
        if ($creator && isset($nid) && !isset($_GET['nid'])) {
            ?>
            <br class="br-small" />
            <input type="button" id="linkRace" onclick="showTrackCollabPopup('<?php echo $creationType ?>', <?php echo $nid; ?>)" value="<?php echo ($language ? 'Collaborate...':'Collaborer...'); ?>" /><br /><br />
            <?php
        }
        else {
            ?>
            <br />
            <?php
        }
        if ($canShare) {
            ?>
        <input type="button" id="shareRace" onclick="document.getElementById('cSave').style.display='block'" value="<?php
        if ($cShared)
            echo $language ? 'Edit sharing':'Modifier partage';
        else
            echo $language ? "Share $typeStr":"Partager $typeStr";
        ?>"<?php if (isset($message)&&!isset($infoMsg)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
            if ($cShared && isset($_GET[$sid])) {
                ?>
            <br /><br class="br-small" /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
                <?php
            }
        }
    }
    else
        printRatingView($language ? ('Rate this '.($isMCup?'multicup':($isCup?'cup':($isBattle?'course':'circuit'))).'!'):('Notez '.($isMCup?'cette multicoupe':($isCup?'cette coupe':($isBattle?'cette arène':'ce circuit')))).' !');
}
function printCircuitShareUI() {
    global $language, $isCup, $isMCup, $isBattle, $cName, $cPseudo;
    ?>
    <div id="confirmSuppr">
    <p id="supprInfos"><?php echo $language ?
        'Stop sharing this '. ($isCup ? ($isMCup ? 'multicup':'cup'):($isBattle ? 'arena':'circuit')) .'?<br />
        '.($isCup ? ($isMCup ? 'The multicup':'The cup'):($isBattle ? 'The arena':'The circuit')).' will be only removed from the list:<br />
        data will be recoverable.' :
        'Supprimer le partage de '. ($isCup ? ($isMCup ? 'cette multicoupe':'cette coupe'):($isBattle ? 'cette arène':'ce circuit')) .' ?<br />
        '.($isCup ? ($isMCup ? 'La multicoupe':'La coupe'):($isBattle ? "L'arène":"Le circuit")).' sera simplement '.($isBattle||$isCup ? 'retirée' : 'retiré').' de la liste :<br />
        les données seront récupérables.';
    ?></p>
    <p id="supprButtons"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="sAnnuler" onclick="document.getElementById('confirmSuppr').style.display='none'" /> &nbsp; <input type="button" value="<?php echo $language ? 'Delete':'Supprimer'; ?>" id="sConfirmer" onclick="supprRace()" /></p>
    </div>
    <?php
    if (!isset($cannotChange)) {
        ?>
        <form id="cSave" method="post" action="" onsubmit="saveRace();return false">
        <table id="cTable">
        <tr><td style="text-align: right"><label for="cPseudo"><?php echo $language ? 'Enter your nick':'Indiquez votre pseudo'; ?> :</label></td><td><input type="text" name="cPseudo" id="cPseudo" value="<?php echo escapeUtf8($cPseudo) ?>" /></td></tr>
        <tr><td style="text-align: right"><label for="cName"><?php echo $language ? ($isCup ? ($isMCup ? 'Multicup':'Cup'):($isBattle ? 'Arena':'Circuit')).' name':'Nom '.($isCup ? ($isMCup?'de la multicoupe':'de la coupe'):($isBattle ? "de l'arène":"du circuit")); ?> :</label></td><td><input type="text" name="cName" id="cName" value="<?php echo escapeUtf8($cName) ?>" /></td></tr>
        <tr><td colspan="2" id="cSubmit"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="cAnnuler" onclick="document.getElementById('cSave').style.display='none'" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Share':'Partager'; ?>" id="cEnregistrer" /></td></tr>
        </table>
        </form>
        <?php
    }
}