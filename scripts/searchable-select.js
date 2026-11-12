function searchableSelect(select, params) {
	if (select.searchableSelect)
		return select.searchableSelect;
	if (!params) params = {};

	var wrapper = document.createElement("span");
	wrapper.className = "searchable-select";
	select.parentNode.insertBefore(wrapper, select);
	wrapper.appendChild(select);
	select.classList.add("searchable-select-native");
	select.tabIndex = -1;

	var toggle = document.createElement("button");
	toggle.type = "button";
	toggle.className = "searchable-select-toggle";
	wrapper.appendChild(toggle);

	var dropdown = document.createElement("div");
	dropdown.className = "searchable-select-dropdown";
	var search = document.createElement("input");
	search.type = "text";
	search.className = "searchable-select-search";
	search.autocomplete = "off";
	search.placeholder = params.placeholder || "";
	var list = document.createElement("ul");
	list.className = "searchable-select-list";
	dropdown.appendChild(search);
	dropdown.appendChild(list);
	wrapper.appendChild(dropdown);

	var items = [];
	var highlighted = -1;
	var isOpen = false;

	function normalize(text) {
		return text.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase();
	}
	function matches(label, query) {
		var target = normalize(label);
		var words = normalize(query).split(/\s+/);
		for (var i=0;i<words.length;i++) {
			if (words[i] && target.indexOf(words[i]) === -1)
				return false;
		}
		return true;
	}
	function refresh() {
		var option = select.options[select.selectedIndex];
		toggle.textContent = option ? option.textContent : "";
		toggle.classList.toggle("searchable-select-placeholder", !option || option.value === "");
	}
	function addItem(option) {
		var id = items.length;
		var li = document.createElement("li");
		li.className = "searchable-select-option";
		li.textContent = option.textContent;
		li.addEventListener("mousedown", function(e) {
			e.preventDefault();
			choose(id);
		});
		li.addEventListener("mousemove", function() {
			highlight(id, true);
		});
		list.appendChild(li);
		items.push({ li: li, option: option });
	}
	function addGroupLabel(label) {
		var li = document.createElement("li");
		li.className = "searchable-select-group";
		li.textContent = label;
		list.appendChild(li);
	}
	function render() {
		list.innerHTML = "";
		items = [];
		highlighted = -1;
		var query = search.value;
		var children = select.children;
		for (var i=0;i<children.length;i++) {
			var child = children[i];
			if (child.tagName === "OPTGROUP") {
				var groupOptions = [];
				for (var j=0;j<child.children.length;j++) {
					if (matches(child.children[j].textContent, query))
						groupOptions.push(child.children[j]);
				}
				if (!groupOptions.length) continue;
				addGroupLabel(child.label);
				for (var j=0;j<groupOptions.length;j++)
					addItem(groupOptions[j]);
			}
			else if (child.value !== "" && matches(child.textContent, query))
				addItem(child);
		}
		if (!items.length) {
			var empty = document.createElement("li");
			empty.className = "searchable-select-empty";
			empty.textContent = params.noResults || "-";
			list.appendChild(empty);
			return;
		}
		var selectedId = 0;
		for (var i=0;i<items.length;i++) {
			if (query === "" && items[i].option.selected && items[i].option.value !== "")
				selectedId = i;
		}
		highlight(selectedId);
	}
	function highlight(id, noScroll) {
		if (items[highlighted])
			items[highlighted].li.classList.remove("searchable-select-highlighted");
		highlighted = id;
		var item = items[id];
		if (!item) return;
		item.li.classList.add("searchable-select-highlighted");
		if (noScroll) return;
		var li = item.li;
		if (li.offsetTop < list.scrollTop)
			list.scrollTop = li.offsetTop;
		else if (li.offsetTop + li.offsetHeight > list.scrollTop + list.clientHeight)
			list.scrollTop = li.offsetTop + li.offsetHeight - list.clientHeight;
	}
	function choose(id) {
		var item = items[id];
		if (!item) return;
		close();
		toggle.focus();
		if (select.value === item.option.value)
			return;
		select.value = item.option.value;
		refresh();
		select.dispatchEvent(new Event("change", { bubbles: true }));
	}
	function position() {
		var viewportMargin = 8;
		var viewportWidth = document.documentElement.clientWidth;
		dropdown.style.left = "";
		dropdown.style.maxWidth = Math.min(450, viewportWidth - 2*viewportMargin) + "px";
		var rect = dropdown.getBoundingClientRect();
		var overflow = rect.right - (viewportWidth - viewportMargin);
		if (overflow > 0)
			dropdown.style.left = -Math.min(overflow, rect.left - viewportMargin) + "px";
	}
	function onOutsideClick(e) {
		if (!wrapper.contains(e.target))
			close();
	}
	function open(initialQuery) {
		if (isOpen) return;
		isOpen = true;
		wrapper.classList.add("searchable-select-open");
		search.value = initialQuery || "";
		render();
		position();
		search.focus();
		document.addEventListener("mousedown", onOutsideClick);
		window.addEventListener("resize", position);
	}
	function close() {
		if (!isOpen) return;
		isOpen = false;
		wrapper.classList.remove("searchable-select-open");
		document.removeEventListener("mousedown", onOutsideClick);
		window.removeEventListener("resize", position);
	}

	toggle.addEventListener("click", function() {
		if (isOpen)
			close();
		else
			open();
	});
	toggle.addEventListener("keydown", function(e) {
		if (e.ctrlKey || e.metaKey || e.altKey) return;
		if (e.key === "ArrowDown" || e.key === "ArrowUp") {
			e.preventDefault();
			open();
		}
		else if (e.key.length === 1 && e.key !== " ") {
			e.preventDefault();
			open(e.key);
		}
	});
	search.addEventListener("input", render);
	search.addEventListener("keydown", function(e) {
		switch (e.key) {
		case "ArrowDown":
			e.preventDefault();
			if (highlighted < items.length-1)
				highlight(highlighted+1);
			break;
		case "ArrowUp":
			e.preventDefault();
			if (highlighted > 0)
				highlight(highlighted-1);
			break;
		case "Enter":
			e.preventDefault();
			choose(highlighted);
			break;
		case "Escape":
			e.preventDefault();
			close();
			toggle.focus();
			break;
		case "Tab":
			close();
			break;
		}
	});
	select.addEventListener("change", refresh);
	if (window.jQuery)
		jQuery(select).on("change", refresh);
	select.addEventListener("invalid", function() {
		wrapper.classList.add("searchable-select-invalid");
	});
	select.addEventListener("change", function() {
		wrapper.classList.remove("searchable-select-invalid");
	});

	refresh();
	select.searchableSelect = {
		refresh: refresh,
		open: open,
		close: close
	};
	return select.searchableSelect;
}
