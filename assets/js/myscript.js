/**
 * Plugin Template admin js.
 *
 *  @package WordPress Plugin Template/JS
 */

jQuery(document).ready(function(e) {
	function addOrganisations(id, innerText) {
		var myHeaders = new Headers();
		myHeaders.append("Content-Type", "application/json");

		var url =
			organisations.ajax_url +
			"?action=addOrganisation&security=" +
			organisations.ajax_nonce +
			"&id=" +
			id +
			"&name=" +
			innerText;

		fetch(url, {
			method: "GET",
			mode: "cors",
			headers: myHeaders,
		})
			.then(response => response.json())
			.then(response => {
				updateOrganisations(response.data);
			});
	}

	function removeOrganisations(id) {
		var myHeaders = new Headers();
		myHeaders.append("Content-Type", "application/json");

		var url =
			organisations.ajax_url + "?action=removeOrganisation&security=" + organisations.ajax_nonce + "&id=" + id;

		fetch(url, {
			method: "GET",
			mode: "cors",
			headers: myHeaders,
		})
			.then(response => response.json())
			.then(response => {
				updateOrganisations(response.data);
			});
	}

	function addContent(id, innerText) {
		var myHeaders = new Headers();
		myHeaders.append("Content-Type", "application/json");

		var url =
			organisations.ajax_url +
			"?action=addContent&security=" +
			organisations.ajax_nonce +
			"&id=" +
			id +
			"&name=" +
			innerText;

		fetch(url, {
			method: "GET",
			mode: "cors",
			headers: myHeaders,
		})
			.then(response => response.json())
			.then(response => {
				updateContent(response.data);
			});
	}

	function removeContent(id) {
		var myHeaders = new Headers();
		myHeaders.append("Content-Type", "application/json");

		var url = organisations.ajax_url + "?action=removeContent&security=" + organisations.ajax_nonce + "&id=" + id;

		fetch(url, {
			method: "GET",
			mode: "cors",
			headers: myHeaders,
		})
			.then(response => response.json())
			.then(response => {
				updateContent(response.data);
			});
	}

	function updateOrganisations(data) {
		resetOrganisations(data);

		var selectedOrgs = jQuery(".organisations__selected");
		selectedOrgs.html("");

		jQuery.each(data, function(key, value) {
			selectedOrgs.append(
				'<li class="section__item"><a href="#" data-id="' +
					key +
					'" class="section__link remove_organisation">' +
					value +
					"</a></li>"
			);
		});

		addEvents();
	}

	function updateContent(data) {
		resetContent(data);

		var selectedContent = jQuery(".content__selected");
		selectedContent.html("");

		jQuery.each(data, function(key, value) {
			selectedContent.append(
				'<li class="section__item"><a href="#" data-id="' +
					key +
					'" class="section__link remove_content">' +
					value +
					"</a></li>"
			);
		});

		addEvents();
	}

	function resetOrganisations(data) {
		jQuery(".add_organisation")
			.parent()
			.removeClass("hidden");
		jQuery.each(data, function(key, value) {
			jQuery(".add_organisation[data-id=" + key + "]")
				.parent()
				.addClass("hidden");
		});
	}

	function resetContent(data) {
		jQuery(".add_content")
			.parent()
			.removeClass("hidden");
		jQuery.each(data, function(key, value) {
			jQuery(".add_content[data-id=" + key + "]")
				.parent()
				.addClass("hidden");
		});
	}

	function addEvents() {
		var orgListItem = jQuery(".add_organisation");
		orgListItem.each(function() {
			jQuery(this)
				.off()
				.on("click", function(e) {
					e.preventDefault();
					addOrganisations(e.currentTarget.dataset.id, e.currentTarget.innerText);
				});
		});

		var selOrgListItem = jQuery(".remove_organisation");
		selOrgListItem.each(function() {
			jQuery(this)
				.off()
				.on("click", function(e) {
					e.preventDefault();
					removeOrganisations(e.currentTarget.dataset.id);
				});
		});

		var contentListItem = jQuery(".add_content");
		contentListItem.each(function() {
			jQuery(this)
				.off()
				.on("click", function(e) {
					e.preventDefault();
					addContent(e.currentTarget.dataset.id, e.currentTarget.innerText);
				});
		});

		var selContentListItem = jQuery(".remove_content");
		selContentListItem.each(function() {
			jQuery(this)
				.off()
				.on("click", function(e) {
					e.preventDefault();
					removeContent(e.currentTarget.dataset.id);
				});
		});
	}

	resetOrganisations();
	resetContent();
	addEvents();
});
