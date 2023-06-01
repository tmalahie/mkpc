<?php
require_once('collabUtils.php');
include('circuitUser.php');
function includeShareLib() {
    global $nid, $creationType, $cName, $cPrefix, $cAuteur, $cDate, $pNote, $pNotes, $isCup, $isMCup, $isBattle, $cupIDs, $clId, $sid, $cOptions, $identifiants, $language, $creator, $canShare, $canChange, $creationMode, $trackEditPage, $cNote;
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
        $canRemove = $isCup ? isset($nid) : isset($shareParams['remove']);
        ?>
        function saveRace() {
            document.getElementById("cAnnuler").disabled = true;
            document.getElementById("cAnnuler").className = "cannotChange";
            document.getElementById("cEnregistrer").disabled = true;
            document.getElementById("cEnregistrer").className = "cannotChange";
            var $form = document.getElementById("cSave");
            var formData = new FormData($form);
            if (!$form.elements["name_tr"].checked) {
                formData.delete("name_en");
                formData.delete("name_fr");
            }
            formData.delete("name_tr");

            <?php
            $formData = array();
            if ($isCup) {
                $formData['mode'] = $creationMode;
                foreach ($cupIDs as $i=>$cupID)
                    $formData['cid'. $i] = $cupID;
            }
            elseif (isset($shareParams['send']['params'])) {
                $formData = array_merge($formData, $shareParams['send']['params']);
            }
            if (isset($nid)) $formData['id'] = $nid;
            if ($clId) $formData['cl'] = $clId;
            if ($collab) $formData['collab'] = $collab['key'];

            ?>
            var extraData = <?php echo json_encode($formData); ?>;
            for (var key in extraData)
                formData.append(key, extraData[key]);
            <?php
            
            if ($isCup) {
                if (!empty($cOptions))
                    echo 'formData.append("opt", JSON.stringify(cupOpts));';
                echo 'addCollabQuery(formData, "'. ($isMCup ? 'mkcups':$CREATION_ENTITIES[$creationMode]['table']) .'", ['. implode(',',$cupIDs) .']);';
            }
            ?>

            fetch("api/<?php echo ($isMCup ? 'saveMCup.php' : ($isCup?'saveCup.php':$shareParams['send']['endpoint'])); ?>", {
                body: formData,
                method: "POST"
            }).then(function(res) {
                return res.text();
            }).then(function(reponse) {
                if (reponse && !isNaN(reponse)) {
                    var cP = document.createElement("p");
                    cP.style.margin = "5px";
                    cP.style.textAlign = "center";
                    cP.innerHTML = '<?php
                        if ($canRemove)
                            echo $language ? ($isCup ? 'Cup':($isBattle ? 'Arena' : 'Circuit')) .' updated successfully.':'Le partage de votre '. ($isCup ? 'coupe':($isBattle ? 'arène' : 'circuit')) .' a été mis à jour.';
                        else
                            echo $language ? 'Your '. ($isCup ? 'cup':($isBattle ? 'arena' : 'circuit')) .' has just been added to the <a href="creations.php" target="_blank">list</a>!':'Votre '. ($isCup ? 'coupe':($isBattle ? 'arène':'circuit')) .' vient d\\\'être '. ($isCup||$isBattle ? 'ajoutée':'ajouté') .' à la <a href="creations.php" target="_blank">liste</a> !';
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
                    document.getElementById("cTable").innerHTML = '<tr><td id="cTableSingleCell"></td></tr>';
                    document.getElementById("cTableSingleCell").appendChild(cP);
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
                    cCont.focus();
                }
                else
                    throw new Error();
            }).catch(function() {
                alert(language ? "An error occured while saving" : "Une erreur est survenue lors de l'enregistrement");
                document.getElementById("cAnnuler").disabled = false;
                document.getElementById("cAnnuler").className = "";
                document.getElementById("cEnregistrer").disabled = false;
                document.getElementById("cEnregistrer").className = "";
            });
        }
        <?php
        if ($canRemove) {
            ?>
        function supprRace() {
            document.getElementById("sAnnuler").disabled = true;
            document.getElementById("sAnnuler").className = "cannotChange";
            document.getElementById("sConfirmer").disabled = true;
            document.getElementById("sConfirmer").className = "cannotChange";
            xhr("<?php echo ($isMCup ? 'supprMCup.php':($isCup ? 'supprCup.php':$shareParams['remove']['endpoint'])); ?>", "<?php
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
                        if ($isCup) {
                            echo 'document.location.href = "?';
                            if ($isMCup) {
                                foreach ($cupIDs as $i => $cupID) {
                                    if ($i)
                                        echo '&';
                                    echo 'mid'. $i .'='. $cupIDs[$i];
                                }
                                if (!empty($cOptions))
                                    echo '&opt='. urlencode($cOptions);
                            }
                            else {
                                for ($i=0;$i<4;$i++) {
                                    if ($i)
                                        echo '&';
                                    echo 'cid'. $i .'='. $cupIDs[$i];
                                }
                            }
                            if ($clId) echo '&cl='.$clId;
                            echo '";';
                        }
                        else
                            echo $shareParams['remove']['onSuccess'];
                        ?>
                    };
                    document.getElementById("supprButtons").appendChild(cCont);
                    document.getElementById("changeRace").disabled = true;
                    document.getElementById("shareRace").disabled = true;
                    cCont.focus();
                    return true;
                }
                return false;
            });
        }
        function toggleUnshareForm(show) {
            if (show) {
                document.getElementById('confirmSuppr').style.display = 'flex';
                document.getElementById('sConfirmer').focus();
                document.addEventListener("keydown", closeUnshareFormOnEscape);
            }
            else {
                var $cancelBtn = document.getElementById('sAnnuler');
                if (!$cancelBtn || $cancelBtn.disabled)
                    return;
                document.getElementById('confirmSuppr').style.display = 'none';
                document.removeEventListener("keydown", closeUnshareFormOnEscape);
            }
        }
        function handleUnshareBackdropClick(e) {
            if (e.target.id === "confirmSuppr")
                toggleUnshareForm(false);
        }
        function closeUnshareFormOnEscape(e) {
            if (e.keyCode === 27) {
                e.stopPropagation();
                toggleUnshareForm(false);
            }
        }
            <?php
        }
        ?>
        function getValue(name) {
            var $form = document.getElementById("cSave");
            return encodeURIComponent($form.elements[name].value);
        }
        function showPrefixHelp() {
            alert(language ? "Will appear before the circuit name, this allows to disambiguate 2 circuits of the same name in a different series (Ex: SNES Rainbow Road / DS Rainbow Road)" : "Apparaitra avant le nom du circuit, permet de lever l'ambiguité entre 2 circuits du même nom mais d'une série différente (Ex : SNES Route Arc-en-Ciel / DS Route Arc-en-Ciel)");
        }
        function toggleShareForm(show) {
            var $form = document.getElementById("cSave");
            if (show) {
                $form.style.display = 'flex';
                if (getValue("cPseudo"))
                    $form.elements["cName"].select();
                else
                    $form.elements["cPseudo"].select();
                document.addEventListener("keydown", closeShareFormOnEscape);
            }
            else {
                var $cancelBtn = document.getElementById('cAnnuler');
                if (!$cancelBtn || $cancelBtn.disabled)
                    return;
                $form.style.display = 'none';
                document.removeEventListener("keydown", closeShareFormOnEscape);
            }
        }
        function handleShareBackdropClick(e) {
            if (e.target.id === "cSave")
                toggleShareForm(false);
        }
        function closeShareFormOnEscape(e) {
            if (e.keyCode === 27) {
                e.stopPropagation();
                toggleShareForm(false);
            }
        }
        function toggleAdvancedOptions(show) {
            var $table = document.getElementById("cTable");
            if (show)
                $table.classList.add("cShowAdvanced");
            else
                $table.classList.remove("cShowAdvanced");
        }
        function removeThumbnail() {
            var $form = document.getElementById("cSave");
            $form.querySelector(".cThumbnailValue").removeChild($form.querySelector(".cThumbnailCurrent"));
            $form.elements["thumbnail_unset"].value = "1";
        }
        function toggleNameTr(show) {
            var $table = document.getElementById("cTable");
            if (show) {
                $table.classList.add("cShowTr");
                var $mainLanguage = document.getElementById(language ? "cNameEn" : "cNameFr");
                var $otherLanguage = document.getElementById(language ? "cNameFr" : "cNameEn");
                if (!$mainLanguage.value)
                    $mainLanguage.value = document.getElementById("cName").value;
                $otherLanguage.select();
            }
            else
                $table.classList.remove("cShowTr");
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
            elseif ($complete) {
                echo '&complete=1';
                if ($isBattle)
                    echo '&battle=1';
            }
        ?>";
        <?php
    }
    if (isset($nid))
        echo 'var commentCircuit = '.$nid.', commentType = "'. $creationType.'", circuitPrefix = "'. ($cPrefix ? addSlashes(htmlspecialchars($cPrefix)) : '') .'";';
    ?>
	circuitName = "<?php echo addSlashes(htmlEscapeCircuitNames($cName)) ?>", circuitAuthor = "<?php echo addSlashes(htmlEscapeCircuitNames($cAuteur)) ?>", circuitNote = <?php echo $pNote ?>, circuitNotes = <?php echo $pNotes ?>,
	circuitDate = "<?php echo formatDate($cDate); ?>";
	var circuitUser = <?php echo findCircuitUser($cAuteur,$nid,$creationType); ?>;
    <?php
}

function printCircuitActions() {
    global $language, $canChange, $cannotChange, $isCup, $isMCup, $isBattle, $canShare, $creator, $creationType, $creationMode, $trackEditPage, $nid, $sid, $cShared, $message, $infoMsg;
    $complete = ($creationMode%2);
    include('ip_banned.php');
    if (isBanned())
        echo '&nbsp;';
    elseif ($canChange) {
        $typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')) : ($isBattle ? ($language ? 'arena':'l\'arène') : ($language ? 'circuit':'le circuit'));
        $cupEditPage = $complete ? 'completecup.php' : 'simplecup.php';
        $cupsEditPage = $complete ? 'completecups.php' : 'simplecups.php';
        ?>
        <input type="button" id="changeRace"<?php if (!$creator) echo ' data-collab="1"'; ?> onclick="document.location.href='<?php echo ($isCup ? ($isMCup ? $cupsEditPage:$cupEditPage):$trackEditPage) ?>'+document.location.search<?php if ($isCup&&$isBattle) echo '+\'&battle\''; ?>" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" /><br /><?php
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
        <input type="button" id="shareRace" onclick="toggleShareForm(true)" value="<?php
        if ($cShared)
            echo $language ? 'Edit sharing':'Modifier partage';
        else
            echo $language ? "Share $typeStr":"Partager $typeStr";
        ?>"<?php if (isset($message)&&!isset($infoMsg)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
            if ($cShared && isset($_GET[$sid])) {
                ?>
            <br /><br class="br-small" /><input type="button" id="supprRace" onclick="toggleUnshareForm(true)" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
                <?php
            }
        }
    }
    else
        printRatingView($language ? ('Rate this '.($isMCup?'multicup':($isCup?'cup':($isBattle?'course':'circuit'))).'!'):('Notez '.($isMCup?'cette multicoupe':($isCup?'cette coupe':($isBattle?'cette arène':'ce circuit')))).' !');
}
function printCircuitShareUI() {
    global $language, $isCup, $isMCup, $isBattle, $cName0, $cPseudo, $creationType, $nid;
    $softDeleted = in_array($creationType, array('circuits', 'arenes'));
    ?>
    <div id="confirmSuppr" onclick="handleUnshareBackdropClick(event)">
        <div class="confirmSupprDialog">
            <p id="supprInfos"><?php echo $language ?
                'Stop sharing this '. ($isCup ? ($isMCup ? 'multicup':'cup'):($isBattle ? 'arena':'circuit')) .'?<br />
                '.($isCup ? ($isMCup ? 'The multicup':'The cup'):($isBattle ? 'The arena':'The circuit')).' will be only removed from the list:<br />
                data will be '. ($softDeleted ? 'preserved' : 'recoverable') .'.' :
                'Supprimer le partage de '. ($isCup ? ($isMCup ? 'cette multicoupe':'cette coupe'):($isBattle ? 'cette arène':'ce circuit')) .' ?<br />
                '.($isCup ? ($isMCup ? 'La multicoupe':'La coupe'):($isBattle ? "L'arène":"Le circuit")).' sera simplement '.($isBattle||$isCup ? 'retirée' : 'retiré').' de la liste :<br />
                les données seront '. ($softDeleted ? 'conservées' : 'récupérables') .'.';
            ?></p>
            <p id="supprButtons"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="sAnnuler" onclick="document.getElementById('confirmSuppr').style.display='none'" /> &nbsp; <input type="button" value="<?php echo $language ? 'Delete':'Supprimer'; ?>" id="sConfirmer" onclick="supprRace()" /></p>
        </div>
    </div>
    <?php
    if (!isset($cannotChange)) {
        if (isset($nid))
            $getTrackSettings = mysql_fetch_array(mysql_query('SELECT * FROM mktracksettings WHERE type="'. $creationType .'" AND circuit="'. $nid .'"'));
        $cNameFr = isset($getTrackSettings['name_fr']) ? $getTrackSettings['name_fr'] : '';
        $cNameEn = isset($getTrackSettings['name_en']) ? $getTrackSettings['name_en'] : '';
        $cNameTr = ($cNameEn || $cNameFr);
        $cPrefix = isset($getTrackSettings['prefix']) ? $getTrackSettings['prefix'] : '';
        $cThumbnail = isset($getTrackSettings['thumbnail']) ? $getTrackSettings['thumbnail'] : '';
        ?>
        <form id="cSave" method="post" action="" onclick="handleShareBackdropClick(event)" onsubmit="saveRace();return false">
        <table id="cTable"<?php if ($cNameTr) echo ' class="cShowTr"'; ?>>
        <tr><td class="cLabel"><label for="cPseudo"><?php echo $language ? 'Enter your nick:':'Indiquez votre pseudo :'; ?></label></td><td><input type="text" name="auteur" id="cPseudo" value="<?php echo htmlEscapeCircuitNames($cPseudo) ?>" placeholder="Yoshi64" /></td></tr>
        <?php
        if ($language)
            $ofTrack = ($isCup ? ($isMCup ? 'Multicup':'Cup'):($isBattle ? 'Arena':'Circuit'));
        else
            $ofTrack = ($isCup ? ($isMCup?'de la multicoupe':'de la coupe'):($isBattle ? "de l'arène":"du circuit"));
        $oftrack = strtolower($ofTrack);
        ?>
        <tr><td class="cLabel"><label for="cName"><?php echo $language ? "$ofTrack name":"Nom $ofTrack"; ?><?php echo $language ? ':':' :'; ?></label></td><td><input type="text" name="nom" id="cName" value="<?php echo htmlEscapeCircuitNames($cName0) ?>" placeholder="<?php echo $language ? 'Mario Circuit' : 'Circuit Mario'; ?>" /></td></tr>
        <tr class="cAdvanced"><td colspan="2" class="cToggle"><label><input type="checkbox" name="name_tr" onclick="toggleNameTr(this.checked)"<?php if ($cNameTr) echo ' checked="checked"'; ?> /> <?php echo $language ? "Translate $oftrack name" : "Traduire le nom $ofTrack"; ?></label></td></tr>
        <tr class="cAdvanced cTogglable-cNameTr"><td class="cLabel"><label for="<?php echo $language ? 'cNameEn' : 'cNameFr'; ?>"><?php echo $language ? "$ofTrack name [EN]:":"Nom $ofTrack [FR] :"; ?></label></td><td><input type="text" name="<?php echo $language ? 'name_en' : 'name_fr'; ?>" id="<?php echo $language ? 'cNameEn' : 'cNameFr'; ?>" value="<?php echo htmlspecialchars($language ? $cNameEn : $cNameFr); ?>" placeholder="<?php echo $language ? 'Mario Circuit' : 'Circuit Mario'; ?>" /></td></tr>
        <tr class="cAdvanced cTogglable-cNameTr"><td class="cLabel"><label for="<?php echo $language ? 'cNameFr' : 'cNameEn'; ?>"><?php echo $language ? "$ofTrack name [FR]:":"Nom $ofTrack [EN] :"; ?></label></td><td><input type="text" name="<?php echo $language ? 'name_fr' : 'name_en'; ?>" id="<?php echo $language ? 'cNameFr' : 'cNameEn'; ?>" value="<?php echo htmlspecialchars($language ? $cNameFr : $cNameEn); ?>" placeholder="<?php echo $language ? 'Circuit Mario' : 'Mario Circuit'; ?>" /></td></tr>
        <tr class="cAdvanced"><td colspan="2">
            <div class="cThumbnail">
                <label for="cThumbnail"><?php echo $language ? 'Thumbnail:':'Miniature :'; ?></label>
                <div class="cThumbnailValue">
                <?php
                if ($cThumbnail) {
                    ?>
                    <div class="cThumbnailCurrent">
                        <img src="images/creation_icons/uploads/<?php echo $cThumbnail; ?>" alt="Thumbnail" />
                        <a href="javascript:removeThumbnail()">[<?php echo $language ? 'Remove':'Supprimer'; ?>]</a>
                    </div>
                    <?php
                }
                ?>
                <div class="cThumbnailInput">
                    <?php
                    if ($cThumbnail)
                        echo '<input type="hidden" name="thumbnail_unset" />';
                    ?>
                    <input type="file" name="thumbnail" id="cThumbnail" accept="image/png,image/gif,image/jpeg" /></td></tr>
                </div>
            </div>
        <tr class="cAdvanced"><td class="cLabel"><label for="cPrefix"><?php echo $language ? 'Prefix':'Préfixe'; ?><a class="cHelp" href="javascript:showPrefixHelp()">[?]</a><?php echo $language ? ':' : ' :'; ?></label></td><td><input type="text" name="prefix" id="cPrefix" value="<?php echo htmlspecialchars($cPrefix); ?>" placeholder="DS" /></td></tr>
        <tr><td colspan="2" id="cSubmit">
            <div class="cSubmit">
                <div class="cActions">
                    <input type="button" class="cSecondary" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="cAnnuler" onclick="toggleShareForm(false)" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Share':'Partager'; ?>" id="cEnregistrer" />
                </div>
                <div class="cOptions">
                    <div class="cOptionsShow"><a href="javascript:toggleAdvancedOptions(true)"><?php echo $language ? "More options" : "Plus d'options"; ?></a> &gt;</div>
                     <div class="cOptionsHide">&lt; <a href="javascript:toggleAdvancedOptions(false)"><?php echo $language ? " Less options" : "Moins d'options"; ?></a></div>
                </div>
            </div>
        </td></tr>
        </table>
        </form>
        <?php
    }
}