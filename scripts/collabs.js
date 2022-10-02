if (!window.xhr) {
    var script = document.createElement('script');
    script.src = "scripts/xhr.js";
    document.getElementsByTagName('head')[0].appendChild(script);
}

function showCollabPopup(type, id) {
    var $popup = document.createElement("div");
    $popup.dataset.id = id;
    $popup.dataset.type = type;
    $popup.className = "collab-backdrop";
    document.body.appendChild($popup);
    resetTrackCollabPopup();
}
function closeCollabPopup(e) {
    if (e) e.preventDefault();
    var $popup = document.querySelector(".collab-backdrop");
    if ($popup) $popup.parentNode.removeChild($popup);
}
function resetTrackCollabPopup() {
    var $popup = document.querySelector(".collab-backdrop");
    var id = $popup.dataset.id;
    var type = $popup.dataset.type;
    xhr("getTrackCollabPopup.php", "type="+type+"&id="+id, function(html) {
        if (!html) return false;
        $popup.innerHTML = html;
        setupCollabForm($popup);
        return true;
    });
}

function onSaveTrackCollab(payload) {
    var url = payload.url;
    document.querySelector(".collab-track-success a").href = url;
    document.querySelector(".collab-track-success a").innerHTML = url;
    document.querySelector(".collab-track .collab-form").classList.remove("show");
    document.querySelector(".collab-track .collab-track-success").classList.add("show");
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
                if ($collabForm.dataset.onsave && window[$collabForm.dataset.onsave])
                    window[$collabForm.dataset.onsave](res);
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
        });
    });
}

function editTrackCollabLink(collab) {
    document.querySelector(".collab-track-links").classList.remove("show");
    var $collabForm = document.querySelector(".collab-form");
    $collabForm.dataset.collab = collab.id;
    $collabForm.dataset.onsave = "resetTrackCollabPopup";
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
function addTrackCollabLink() {
    document.querySelector(".collab-track-links").classList.remove("show");
    var $collabForm = document.querySelector(".collab-form");
    $collabForm.classList.add("add");
    $collabForm.classList.add("new");
    $collabForm.classList.add("show");
}

function delCollabLink(id, callback) {
    if (confirm("Delete collab link?")) {
        xhr("delCollab.php", "id="+ id, function(res) {
            if (res != 1) return false;
            callback(res);
            return true;
        });
    }
}
function delTrackCollabLink(id) {
    delCollabLink(id, resetTrackCollabPopup);
}

function backToTrackCollabLinks(e) {
    if (e) e.preventDefault();
    document.querySelector(".collab-form").classList.remove("show");
    document.querySelector(".collab-track-links").classList.add("show");
    resetTrackCollabPopup();
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