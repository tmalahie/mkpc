<?php
if (isset($_POST['id']) && isset($_POST['type'])) {
    include('language.php');
    include('initdb.php');
    require_once('collabUtils.php');
    $itemType = $_POST['type'];
    $itemId = $_POST['id'];
    $existingLinks = getCollabLinks($itemType, $itemId);
    $hasLinks = !empty($existingLinks);
    switch ($itemType) {
    case 'arenes':
        $itemCategory = 'arena';
        break;
    case 'mkcups':
        $itemCategory = 'cup';
        break;
    case 'mkmcups':
        $itemCategory = 'multicup';
        break;
    case 'mkcircuits':
        if ($getType = mysql_fetch_array(mysql_query('SELECT type FROM mkcircuits WHERE id="'. $itemId .'"')))
            $itemCategory = $getType['type'] ? 'arena' : 'circuit';
        else
            $itemCategory = 'circuit';
        break;
    default:
        $itemCategory = 'circuit';
        break;
    }
    switch ($itemCategory) {
    case 'circuit':
        $itemLabel = $language ? "circuit" : "circuit";
        $theItemLabel = $language ? "the circuit" : "le circuit";
        break;
    case 'arena':
        $itemLabel = $language ? "arena" : "arène";
        $theItemLabel = $language ? "the arena" : "l'arène";
        break;
    case 'cup':
        $itemLabel = $language ? "cup" : "coupe";
        $theItemLabel = $language ? "the cup" : "la coupe";
        break;
    case 'multicup':
        $itemLabel = $language ? "multicup" : "multicoupe";
        $theItemLabel = $language ? "the multicup" : "la multicoupe";
        break;
    }
    ?>
    <div class="collab-track">
      <a href="#null" class="collab-track-close" onclick="closeCollabPopup(event)">&times;</a>
      <h2><?php echo $language ? "Collaborate with other members" : "Collaborer avec d'autres membres"; ?></h2>
      <form class="collab-form collab-track-section<?php if (!$hasLinks) echo ' add show'; ?>" data-type="<?php echo $itemType; ?>" data-id="<?php echo $itemId; ?>" data-onsave="onSaveTrackCollab">
          <div class="collab-explain collab-form-on-add">
              Cette option vous permet d'autoriser d'autres membres à visualiser, utiliser ou modifier votre <?php echo $itemLabel; ?> via un lien que vous leur enverrez. Cela peut servir dans le cadre d'une collab par exemple.
          </div>
          <div class="collab-current collab-form-on-edit">
              Modifier le lien <a href="#null"></a>
          </div>
          <div class="collab-options">
              <h3>Options</h3>
              Les utilisateurs ayant le lien pourront :
              <div class="collab-options-list">
                  <label class="collab-option">
                      <input type="checkbox" name="rights[view]" checked="checked" />
                      <span>Voir <?php echo $theItemLabel; ?> dans l'éditeur</span>
                  </label>
                  <label class="collab-option">
                      <input type="checkbox" name="rights[edit]" data-depends-on="view" />
                      <span>Modifier <?php echo $theItemLabel; ?></span>
                  </label>
                  <?php
                  if ($itemCategory === 'circuit') {
                    ?>
                    <label class="collab-option">
                        <input type="checkbox" name="rights[use]" />
                        <span>Utiliser <?php echo $theItemLabel; ?> dans les coupes</span>
                    </label>
                    <?php
                  }
                  if ($itemCategory === 'cup') {
                    ?>
                    <label class="collab-option">
                        <input type="checkbox" name="rights[use]" />
                        <span>Utiliser <?php echo $theItemLabel; ?> dans les multicoupes</span>
                    </label>
                    <?php
                  }
                  ?>
              </div>
          </div>
          <div class="collab-submit collab-form-on-add">
            <a href="#null" class="collab-form-on-new" onclick="backToTrackCollabLinks(event)"><?php echo $language ? 'Back' : 'Retour'; ?></a>
            <input type="submit" value="Créer le lien de partage" />
          </div>
          <div class="collab-submit collab-form-on-edit">
            <a href="#null" onclick="backToTrackCollabLinks(event)"><?php echo $language ? 'Back' : 'Retour'; ?></a>
            <input type="submit" value="Modifier le lien de partage" />
          </div>
      </form>
      <div class="collab-track-success collab-track-section">
          Le lien suivant a été généré :<br />
          <a href="#null"></a><br />
          Partagez-le à tous les membres à qui vous souhaitez y donner accès.
      </div>
      <?php
      if ($hasLinks) {
          ?>
      <div class="collab-track-links collab-track-section show">
          <h3>Vos liens</h3>
          <div class="collab-track-links-list">
          <?php
          foreach ($existingLinks as $existingLink) {
              ?>
              <div class="collab-track-link">
                  <a href="<?php echo $existingLink['url']; ?>"><?php echo $existingLink['url']; ?></a>
                  <div class="collab-track-link-actions">
                      <input type="button" value="✎" class="collab-track-link-action-edit" onclick="editTrackCollabLink(<?php echo htmlspecialchars(json_encode($existingLink)); ?>)" />
                      <input type="button" value="&times;" class="collab-track-link-action-del" onclick="delTrackCollabLink(<?php echo $existingLink['id']; ?>)" />
                  </div>
              </div>
              <?php
          }
          ?>
          </div>
          <div class="collab-track-links-new">
              <input type="button" value="Nouveau lien de partage..." onclick="addTrackCollabLink()" />
          </div>
      </div>
          <?php
      }
      ?>
  </div>
  <?php
}