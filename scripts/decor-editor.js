function setupUploadTabs($parent) {
    if (!$parent) return;
    var required = $parent.querySelector(".editor-upload-input-selected input").required;
    var $uploadTabs = $parent.querySelectorAll(".editor-upload-tab");
    var $uploadInputs = $parent.querySelectorAll(".editor-upload-input");
    for (var i=0;i<$uploadTabs.length;i++) {
        (function(i) {
            $uploadTabs[i].onclick = function() {
                var $selectedTab = $parent.querySelector(".editor-upload-tab-selected");
                if ($selectedTab)
                    $selectedTab.classList.remove("editor-upload-tab-selected");
                $uploadTabs[i].classList.add("editor-upload-tab-selected");

                var $selectedInputCtn = $parent.querySelector(".editor-upload-input-selected");
                if ($selectedInputCtn) {
                    $selectedInputCtn.classList.remove("editor-upload-input-selected");
                    $selectedInputCtn.querySelector("input").value = "";
                    if (required)
                        $selectedInputCtn.querySelector("input").required = false;
                }
                $uploadInputs[i].classList.add("editor-upload-input-selected");
                if (required)
                    $uploadInputs[i].querySelector("input").required = true;
				$uploadInputs[i].querySelector("input").focus();
            };
        })(i);
    }
}