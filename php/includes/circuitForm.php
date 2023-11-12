<div class="editor-upload">
    <div class="editor-upload-tabs">
        <div class="editor-upload-tab editor-upload-tab-selected">
            <?php echo $language ? 'Upload an image':'Uploader une image'; ?>
        </div><div class="editor-upload-tab">
            <?php echo $language ? 'Paste image URL':'Coller l\'URL de l\'image'; ?>
        </div>
    </div>
    <div class="editor-upload-inputs">
        <div class="editor-upload-input editor-upload-input-selected">
            <input type="file" accept="image/png,image/gif,image/jpeg" required="required" name="image" />
        </div>
        <div class="editor-upload-input">
            <input type="url" name="url" placeholder="https://www.mariouniverse.com/wp-content/img/maps/ds/mk/delfino-square.png" />
            <br />
            <label><input type="checkbox" name="import" />
            <span><?php echo $language ? 'Import into MKPC servers':'Importer dans les serveurs MKPC'; ?> <a href="javascript:showImportHelp()">[?]</a></span></label>
        </div>
    </div>
</div>
<div class="editor-submit">
    <button type="submit"><?php echo $language ? 'Create '. (empty($isBattle) ? 'track':'arena'):'Valider !'; ?></button>
    <?php
    if (isset($_GET['help']))
        echo '<a href="'.(empty($isBattle) ? 'draw':'course').'.php">'. ($language ? 'Back':'Retour') .'</a>';
    elseif ($nbTracks)
        echo '<a href="?help">'. ($language ? 'Help':'Aide') .'</a>';
    ?>
    <div class="editor-restrictions">
        <?php
		$maxUploadSize = upload_max_size();
        echo $language
            ? 'Your image must be in png, gif or jpg format and must not exceed '. filesize_str($maxUploadSize)
            : 'Votre image doit être au format png, gif ou jpg et ne doit pas dépasser '. filesize_str($maxUploadSize);
        ?>
    </div>
</div>