if (!window.xhr) {
    var script = document.createElement('script');
    script.src = "scripts/xhr.js";
    document.getElementsByTagName('head')[0].appendChild(script);
}

function showCollabPopup(type, id, src) {
    if (document.querySelector(".collab-backdrop")) return;
    var $popup = document.createElement("div");
    $popup.dataset.id = id;
    $popup.dataset.type = type;
    $popup.dataset.src = src;
    $popup.className = "collab-backdrop";
    $popup.onclick = function(e) {
        if (e.target === this)
            closeCollabPopup(e);
    };
    document.body.appendChild($popup);
    resetCollabPopup();
    document.addEventListener("keydown", closeCollabPopupOnEscape);
    return $popup;
}
function showTrackCollabPopup(type, id) {
    var $popup = showCollabPopup(type, id, "getTrackCollabPopup.php");
    $popup.style.zIndex = 20010;
}
function closeCollabPopup(e) {
    if (e) e.preventDefault();
    var $popup = document.querySelector(".collab-backdrop");
    if ($popup) $popup.parentNode.removeChild($popup);
    document.removeEventListener("keydown", closeCollabPopupOnEscape);
}
function closeCollabPopupOnEscape(e) {
    if (e.keyCode === 27) {
        e.stopPropagation();
        closeCollabPopup(e);
    }
}
function resetCollabPopup() {
    var $popup = document.querySelector(".collab-backdrop");
    var id = $popup.dataset.id;
    var type = $popup.dataset.type;
    var src = $popup.dataset.src;
    xhr(src, "type="+type+"&id="+id, function(html) {
        if (!html) return false;
        $popup.innerHTML = html;
        setupCollabForm($popup);
        return true;
    });
}

function onSavePopupCollab(payload) {
    var url = payload.url;
    document.querySelector(".collab-popup-success a").href = url;
    document.querySelector(".collab-popup-success a").innerHTML = url;
    document.querySelector(".collab-popup .collab-form").classList.remove("show");
    document.querySelector(".collab-popup .collab-popup-success").classList.add("show");
}

function setupCollabForm($parent) {
    var $collabForms = $parent.querySelectorAll(".collab-form");
    [...$collabForms].forEach(function($collabForm) {
        $collabForm.onsubmit = function(e) {
            e.preventDefault();
            var $form = e.target;
            var $submits = $form.querySelectorAll('input[type="submit"]');
            [...$submits].forEach(function($submit) {
                $submit.disabled = true;
            });
            var fd = new FormData($form);
            if ($form.dataset.collab) {
                fd.append("id", $form.dataset.collab);
                var queryString = new URLSearchParams(fd).toString();
                xhr("editCollab.php", queryString, handleCollabSave);
            }
            else {
                fd.append("id", $form.dataset.id);
                fd.append("type", $form.dataset.type);
                var queryString = new URLSearchParams(fd).toString();
                xhr("createCollab.php", queryString, handleCollabSave);
            }

            function handleCollabSave(res) {
                if (!res) return false;
                try {
                    res = JSON.parse(res);
                }
                catch (e) {
                    return false;
                }
                if ($form.dataset.collab)
                    resetCollabPopup(res);
                else
                    onSavePopupCollab(res);
                return true;
            }
        };
        var $checkboxes = $collabForm.querySelectorAll("input[type='checkbox'][name^='rights[']");
        [...$checkboxes].forEach(function($checkbox) {
            $checkbox.onclick = function() {
                var rightKey = this.name.replace(/^rights\[(.+)\]$/, "$1");
                var isChecked = this.checked;
                var $dependingCheckboxes = $collabForm.querySelectorAll("input[type='checkbox'][name^='rights['][data-depends-on='"+ rightKey +"']");
                $dependingCheckboxes.forEach(function($dependingCheckbox) {
                    if (isChecked)
                        $dependingCheckbox.disabled = false;
                    else {
                        $dependingCheckbox.checked = false;
                        $dependingCheckbox.disabled = true;
                    }
                });
            };
            $checkbox.onclick();
        });
    });
}

function editPopupCollabLink(collab) {
    document.querySelector(".collab-popup-links").classList.remove("show");
    var $collabForm = document.querySelector(".collab-form");
    $collabForm.dataset.collab = collab.id;
    $collabForm.querySelector(".collab-current a").href = collab.url;
    $collabForm.querySelector(".collab-current a").innerHTML = collab.url;

    var $checkboxes = $collabForm.querySelectorAll("input[type='checkbox'][name^='rights[']");
    [...$checkboxes].forEach(function($checkbox) {
        var rightKey = $checkbox.name.replace(/^rights\[(.+)\]$/, "$1");
        $checkbox.checked = !!collab.rights[rightKey];
        $checkbox.onclick();
    });

    $collabForm.classList.remove("add");
    $collabForm.classList.add("edit");
    $collabForm.classList.add("show");
}
function addPopupCollabLink() {
    document.querySelector(".collab-popup-links").classList.remove("show");
    var $collabForm = document.querySelector(".collab-form");
    $collabForm.classList.add("add");
    $collabForm.classList.add("new");
    $collabForm.classList.add("show");
}

function delCollabLink(id, callback) {
    if (confirm(window.language ? "Delete collaboration link?" : "Supprimer le lien de collaboration ?")) {
        xhr("delCollab.php", "id="+ id, function(res) {
            if (res != 1) return false;
            callback(res);
            return true;
        });
    }
}
function delPopupCollabLink(id) {
    delCollabLink(id, resetCollabPopup);
}

function backToPopupCollabLinks(e) {
    if (e) e.preventDefault();
    document.querySelector(".collab-form").classList.remove("show");
    document.querySelector(".collab-popup-links").classList.add("show");
    resetCollabPopup();
}

function getCollabQuery(type, ids) {
    var res = "";
    for (var i=0;i<ids.length;i++) {
        var collabKey = sessionStorage.getItem("collab.track."+type+"."+ids[i]+".key");
        if (collabKey) {
            res += "&";
            res += "collabs["+ids[i]+"]="+collabKey;
        }
    }
    return res;
}