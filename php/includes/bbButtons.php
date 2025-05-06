<?php
$trLang1 = $language ? 'en' : 'fr';
$trLang2 = $language ? 'fr' : 'en';
$trButton = '<input type="button" class="mAlign" title="' . ($language ? 'Translate' : 'Traduction') . '" 
    style="width:26px;background-image: url(\'images/translate.png\');" 
    onclick="insert(\'[' . $trLang1 . ']\',\'[/' . $trLang1 . ']\\n[' . $trLang2 . '][/' . $trLang2 . ']\')" 
    value="&nbsp;" />';
?>

<!-- Formatting Buttons -->
<input type="button" value="<?php echo $language ? 'B' : 'G'; ?>" 
    title="<?php echo $language ? 'Bold' : 'Gras'; ?>" 
    style="font-weight: bold;" onclick="insertTag('b');" />

<input type="button" value="<?php echo $language ? 'U' : 'S'; ?>" 
    title="<?php echo $language ? 'Underlined' : 'Souligné'; ?>" 
    style="text-decoration: underline;" onclick="insertTag('u');" />

<input type="button" value="I" 
    title="<?php echo $language ? 'Italic' : 'Italique'; ?>" 
    style="font-style: italic;" onclick="insertTag('i');" />

<input type="button" value="<?php echo $language ? 'S' : 'B'; ?>" 
    title="<?php echo $language ? 'Strikethrough' : 'Barré'; ?>" 
    style="text-decoration: line-through;" onclick="insertTag('s');" />

<!-- Media and Extra Tags -->
<input type="button" value="url" title="<?php echo $language ? 'Link' : 'Lien'; ?>" onclick="insertTag('url');" />
<input type="button" value="img" title="<?php echo $language ? 'Image' : 'Image'; ?>" onclick="insertTag('img')" />
<input type="button" value="youtube" title="<?php echo $language ? 'Youtube' : 'Youtube'; ?>" onclick="insertTag('yt')" />

<?php if (!isset($isNews)) : ?>
    <input type="button" value="quote" title="<?php echo $language ? 'Quote' : 'Citer'; ?>" onclick="insertTag('quote')" />
<?php endif; ?>

<input type="button" value="spoiler" title="<?php echo $language ? 'Hidden text' : 'Texte masqué'; ?>" onclick="insertTag('spoiler')" />

<?php if (isset($isNews)) echo $trButton; ?>

<br />

<!-- Text Alignment Buttons -->
<input type="button" class="mAlign" title="<?php echo $language ? 'Left' : 'Gauche'; ?>" 
    style="background-image: url('images/left.png');" onclick="insertTag('left')" value="&nbsp;" />

<input type="button" class="mAlign" title="<?php echo $language ? 'Center' : 'Centré'; ?>" 
    style="background-image: url('images/center.png');" onclick="insertTag('center')" value="&nbsp;" />

<input type="button" class="mAlign" title="<?php echo $language ? 'Right' : 'Droite'; ?>" 
    style="background-image: url('images/right.png');" onclick="insertTag('right')" value="&nbsp;" />

<?php
function addSelect($bbcode, $style, $param, $values, $infos, $styled) {
    echo '<select onchange="insertCustomTag(\'' . $bbcode . '\', this.value);this.selectedIndex=0">';
    echo '<option selected="selected">' . $param . '</option>';
    foreach ($values as $i => $value) {
        echo '<option value="' . $value . '" style="' . $style . ': ' . $styled[$i] . '">' . $infos[$i] . '</option>';
    }
    echo '</select> ';
}

// font sizes
$sizes = [8, 10, 12, 14, 18, 24, 36];
addSelect(
    'size', 'font-size',
    $language ? 'Size' : 'Taille',
    $sizes, $sizes,
    ['8pt', '10pt', '12pt', '14pt', '18pt', '24pt', '36pt']
);

// fonts
$fonts = [
    "Arial", "Arial Black", "Comic Sans MS", "Courier New", "Georgia", "Helvetica",
    "Impact", "Monospace", "Roman", "Symbol", "Tahoma", "Terminal",
    "Times New Roman", "Trebuchet MS", "Verdana"
];
addSelect('font', 'font-family', $language ? 'Font' : 'Police', $fonts, $fonts, $fonts);

// colors
$colors = [
    "black", "maroon", "green", "olive", "navy", "purple", "teal", "gray",
    "#F60", "red", "blue", "fuchsia", "dodgerblue", "aqua", "white", "custom_picker"
];
$styles = $colors;
$styles[count($styles) - 1] = '#D31';

addSelect(
    'color', 'color',
    $language ? 'Color' : 'Couleur',
    $colors,
    $language ? [
        "Black", "Maroon", "Dark green", "Light brown", "Navy", "Purple", "Blue-green", "Gray",
        "Orange", "Red", "Blue", "Fuchsia", "Blue sky", "Cyan", "White", "Other..."
    ] : [
        "Noir", "Rouge foncé", "Vert foncé", "Marron clair", "Bleu marine", "Violet", "Bleu-vert", "Gris",
        "Orange", "Rouge", "Bleu", "Fuchsia", "Bleu ciel", "Cyan", "Blanc", "Autre..."
    ],
    $styles
);
?>

<!-- Custom Color Picker -->
<input type="color" id="bbColPicker" tabindex="-1" onchange="insertCustomTag('color', this.value)" />

<?php if (!isset($isNews)) echo $trButton; ?>
