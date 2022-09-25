if (!window.xhr) {
    var script = document.createElement('script');
    script.src = "scripts/xhr.js";
    document.getElementsByTagName('head')[0].appendChild(script);
}

function showCollabPopup() {
    var $popup = document.querySelector(".collab-backdrop");
    if ($popup) $popup.classList.add("open");
}
function closeCollabPopup(e) {
    if (e) e.preventDefault();
    var $popup = document.querySelector(".collab-backdrop.open");
    if ($popup) $popup.classList.remove("open");
}

function onSaveTrackCollab(payload) {
    var url = payload.url;
    document.querySelector(".collab-track-success a").href = url;
    document.querySelector(".collab-track-success a").innerHTML = url;
    document.querySelector(".collab-track .collab-form").classList.remove("show");
    document.querySelector(".collab-track .collab-track-success").classList.add("show");
}

document.addEventListener("DOMContentLoaded", function() {
    var $collabForms = document.querySelectorAll(".collab-form");
    [...$collabForms].forEach(function($collabForm) {
        $collabForm.onsubmit = function(e) {
            e.preventDefault();
            var $form = e.target;
            $form.querySelector('input[type="submit"]').disabled = true;
            var fd = new FormData($form);
            fd.append("id", $form.dataset.id);
            fd.append("type", $form.dataset.type);
            var queryString = new URLSearchParams(fd).toString();
            xhr("createCollab.php", queryString, function(res) {
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
            });
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
});