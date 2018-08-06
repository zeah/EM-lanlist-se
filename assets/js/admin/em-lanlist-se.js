(() => {

	// console.log(emlanlistse_meta);

	let newtype = '';

	let container = document.querySelector('.emlanlistse-meta-container');

	let newdiv = (o = {}) => {
		let div = document.createElement('div');

		if (o.class) {
			if (Array.isArray(o.class))
				for (let c of o.class)
					div.classList.add(c);
			else
				div.classList.add(o.class);
		}

		if (o.text) div.appendChild(document.createTextNode(o.text));

		return div;
	}

	let newinput = (o = {}) => {
		if (!o.name) return document.createElement('div');

		let container = newdiv({class: 'emlanlistse-input-container'});

		let title = newdiv({class: 'emlanlistse-input-title', text: o.title});
		container.appendChild(title);

		let input = document.createElement('input');

		if (!o.type) input.setAttribute('type', 'text');
		else input.setAttribute('type', o.type);

		if (!o.sort) input.setAttribute('value', (emlanlistse_meta.meta[o.name] == undefined) ? '' : emlanlistse_meta.meta[o.name]);
		else {
			let sort = emlanlistse_meta.emlanlistse_sort;

			if (o.sort != 'default') sort = emlanlistse_meta['emlanlistse_sort_'+o.sort];

			if (sort == undefined) sort = 100;

			input.setAttribute('value', sort);
		}


		if (!o.notData) input.setAttribute('name', 'emlanlistse_data['+o.name+']');
		else input.setAttribute('name', o.name);

		container.appendChild(input);


		return container;
	}

	let dicedropdown = (o = {}) => {
		let container = document.createElement('div');

		let input = document.createElement('select');
		input.setAttribute('name', 'emlanlistse_data[terning]');

		container.appendChild(newdiv({class: 'emlanlistse-input-title', text: 'Terningkast'}));

		let addOption = (o = {}) => {
			let option = document.createElement('option');
			option.setAttribute('value', o.value);
			if (o.value == emlanlistse_meta.meta.terning) option.setAttribute('selected', '');
			option.appendChild(document.createTextNode(o.value));
			return option;
		}

		input.appendChild(addOption({value: 'ingen'}));
		input.appendChild(addOption({value: 'en'}));
		input.appendChild(addOption({value: 'to'}));
		input.appendChild(addOption({value: 'tre'}));
		input.appendChild(addOption({value: 'fire'}));
		input.appendChild(addOption({value: 'fem'}));
		input.appendChild(addOption({value: 'seks'}));

		container.appendChild(input);

		return container; 
	}

	container.appendChild(newinput({name: 'emlanlistse_sort', title: 'Sortering', notData: true, sort: 'default'}));


	for (let sort of emlanlistse_meta['tax'])
		container.appendChild(newinput({name: 'emlanlistse_sort_'+sort, title: 'Sortering '+sort, notData: true, sort: sort}));

	container.appendChild(newinput({name: 'readmore', title: 'Read More Link'}));

	container.appendChild(newinput({name: 'bestill', title: 'Bestill Link'}));
	container.appendChild(newinput({name: 'bestill_text', title: 'Bestill Text (under bestillknapp)'}));

	let info_container = newdiv({class: 'emlanlistse-info-container'});

	info_container.appendChild(newinput({name: 'info01', title: 'Text 01'}));
	info_container.appendChild(newinput({name: 'info05', title: 'Text 05'}));
	info_container.appendChild(newinput({name: 'info02', title: 'Text 02'}));
	info_container.appendChild(newinput({name: 'info06', title: 'Text 06'}));
	info_container.appendChild(newinput({name: 'info03', title: 'Text 03'}));
	info_container.appendChild(newinput({name: 'info07', title: 'Text 07'}));
	info_container.appendChild(newinput({name: 'info04', title: 'Text 04'}));
	info_container.appendChild(newinput({name: 'info08', title: 'Text 08'}));

	container.appendChild(info_container);

	container.appendChild(dicedropdown());


	jQuery('#emlanlistsetypechecklist').on('change', function(e) {

		let text = $(e.target).parent().text().trim().replace(/ /g, '-');

		if (!e.target.checked) $("input[name='emlanlistse_sort_"+text+"']").parent().remove();
		else {
			let input = newinput({name: 'emlanlistse_sort_'+text, title: 'Sortering '+text.replace(/-/g, ' '), notData: true, sort: text});

			$(input).insertAfter($("input[name='emlanlistse_sort']"));

		}
	});

	jQuery('#newemlanlistsetype').on('input', function(e) {
		newtype = e.target.value;
	});

	jQuery('#emlanlistsetype-add-submit').click(function(e) {
		let text = newtype.trim().replace(/ /g, '-');
		let input = newinput({name: 'emlanlistse_sort_'+text, title: 'Sortering '+text.replace(/-/g, ' '), notData: true, sort: text});

		$(input).insertAfter($("input[name='emlanlistse_sort']"));
	});

})();