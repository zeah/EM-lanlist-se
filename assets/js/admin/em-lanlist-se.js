(() => {

	console.log(emlanlistse_meta);

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
		else input.setAttribute('value', emlanlistse_meta.sort);

		if (!o.notData) input.setAttribute('name', 'emlanlistse_data['+o.name+']');
		else input.setAttribute('name', o.name);

		container.appendChild(input);


		return container;
	}

	container.appendChild(newinput({name: 'emlanlistse_sort', title: 'Sortering', notData: true, sort: true}));

	container.appendChild(newinput({name: 'readmore', title: 'Read More Link'}));

	container.appendChild(newinput({name: 'bestill', title: 'Bestill Link'}));

	container.appendChild(newinput({name: 'info01', title: 'Text 01'}));
	container.appendChild(newinput({name: 'info02', title: 'Text 02'}));
	container.appendChild(newinput({name: 'info03', title: 'Text 03'}));
	container.appendChild(newinput({name: 'info04', title: 'Text 04'}));
	container.appendChild(newinput({name: 'info05', title: 'Text 05'}));
	container.appendChild(newinput({name: 'info06', title: 'Text 06'}));
	container.appendChild(newinput({name: 'info07', title: 'Text 07'}));
	container.appendChild(newinput({name: 'info08', title: 'Text 08'}));



})();