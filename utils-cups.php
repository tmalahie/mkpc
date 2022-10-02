<?php
function printCupCircuit(&$circuit, $options=array()) {
    global $language;
    $circuitnb = isset($options['nb']) ? $options['nb'] : '';
    ?>
    <tr id="circuit<?php echo $circuit['id']; ?>" data-id="<?php echo $circuit['id']; ?>" onclick="selectCircuit(this)">
        <td class="td-preview" <?php
        if (isset($circuit['icon']))
            echo ' style="background-image:url(\'images/creation_icons/'.$circuit['icon'][0].'\')"';
        else
            echo ' data-cicon="'.$circuit['cicon'].'"';
        ?> onclick="previewImg(event,'<?php echo $circuit['srcs'][0]; ?>')"></td>
        <td class="td-name"><em><?php echo $circuitnb; ?></em><?php echo ($circuit['nom'] ? escapeUtf8($circuit['nom']):($language ? 'Untitled':'Sans titre')); ?></td>
        <td class="td-access">&rarr; <a href="<?php echo $circuit['href']; ?>" target="_blank" onclick="event.stopPropagation()"><?php echo $language ? 'Access':'Acc&eacute;der'; ?></a></td>
    </tr>
    <?php
}