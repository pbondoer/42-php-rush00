var h = function (tag, attr, children) {
	var elem = document.createElement(tag);
	Object.keys(attr).map(function(key) {
		elem.setAttribute(key, attr[key]);
	});
	if (typeof children === 'string') {
		elem.textContent = children;
	} else if (Array.isArray(children)) {
		append(elem, children);
	} else if (children instanceof HTMLElement) {
		elem.appendChild(children);
	}
	return elem;
};

var append = function (elem, children) {
	if (children instanceof HTMLElement)
	{
		elem.appendChild(children);
		return;
	}
	children.forEach(function (e) {
		if (Array.isArray(e))
			append(elem, e);
		else
			elem.appendChild(e);
	});
}

var util = {
	toUrlParams: function (obj) {
		return Object.keys(obj).map(function(key) {
			return key + '=' + encodeURIComponent(obj[key]);
		}).join('&');
	},
	empty: function (elem) {
		while (elem.lastChild)
			elem.removeChild(elem.lastChild);
	},
	urlParam: function(name, url) {
		if (!url) url = window.location.href;
		name = name.replace(/[\[\]]/g, "\\$&");
		var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
			results = regex.exec(url);
		if (!results) return null;
		if (!results[2]) return '';
		return decodeURIComponent(results[2].replace(/\+/g, " "));
	}
};

var log = {
	element: document.querySelector('.log'),
	error: function (message) {
		var e = h('DIV', {'class': 'error'}, message);
		this.element.appendChild(e);

		setTimeout(function() {
			log.element.removeChild(e);
		}, 5000);
	}
}

var api = {
	call: function (endpoint, args, callback) {
		const req = new XMLHttpRequest();

		req.onreadystatechange = function(event) {
			var uerror = function(obj) {
				if (obj.error)
					log.error(obj.result);
				else
					callback(obj);
			};
			if (this.readyState === XMLHttpRequest.DONE) {
				if (this.status === 200)
					try {
					uerror(JSON.parse(this.responseText));
					} catch (e) {
						console.log(this.responseText);
						uerror({ error: true, result: "Invalid JSON" });
					}
				else
					uerror({ error: true, result: "" + this.status + " " + this.statusText });
			}
		}

		req.open('GET', '/api.php?method=' + endpoint + '&' + util.toUrlParams(args));
		req.send(null);
	},
};

var app = {
	start: function(elem) {
		// init objects
		this.container = elem;

		// listen for history changes
		window.addEventListener('popstate', function(e) {
			app.state.change(e.target.location.pathname);
		});

		// bind menu
		app.menu.bind();
		app.menu.update();

		// update pathname
		app.state.change(document.location.pathname);
	},
	container: null,
	user: function() {
		return { login: window.localStorage.getItem('login') };
	},
	logout: function(user) {
		window.localStorage.removeItem('login');
		app.menu.update();
		app.state.change('/');
	},
	login: function(user) {
		window.localStorage.setItem('login', user);
		app.menu.update();
		app.state.change('/');
	},
	grid: {
		element: '.grid > .grid',
		clear: function() {
			util.empty(document.querySelector(this.element));
		},
		addProduct: function(p) {
			var elem = h('A', { 'class': 'product', 'href': '/product?id=' + p.p_id }, [
				h('IMG', { 'src': (p.path ? p.path : 'https://unsplash.it/300/300') }),
				h('DIV', { 'class': 'title' }, p.name),
				h('DIV', { 'class': 'meta' }, [
					h('DIV', { 'class': 'price' }, p.price + '€'),
					h('DIV', { 'class': 'stock' }, (p.stock > 0 ? 'En stock' : 'Écoulé'))
				])
			]);

			document.querySelector(this.element).appendChild(elem);
		}
	},
	menu: {
		element: '.nav a',
		update: function() {
			var login = document.querySelector(this.element + '#login-btn');
			var local = app.user();

			if (local.login) {
				login.textContent = 'Profil (' + local.login + ')';
				login.setAttribute('href', '/profile');
			} else {
				login.textContent = 'S\'identifier';
				login.setAttribute('href', '/login');
			}
		},
		bind: function() {
			document.querySelectorAll(this.element).forEach(function (e) {
				app.state.bind(e, e.getAttribute('href'));
			});
		},
		select: function() {
			document.querySelectorAll(this.element).forEach(function (e) {
				e.className = '';
				if (e.getAttribute('href') === document.location.pathname)
					e.className = 'selected';
			});
		}
	},
	state: {
		routes: {},
		goTo: function(path, fullPath) {
			if (fullPath == null)
				fullPath = path;

			window.history.pushState('data', '(' + path + ')', fullPath);
			this.change(path, fullPath);
		},
		change: function(path, fullPath) {
			var route = this.routes[path];

			if (!route) {
				console.error('Unknown route: ' + path);
				return;
			}

			// Note: highly inefficient, but sufficient for our purposes
			// Should write a better API at some point...
			util.empty(app.container);
			append(app.container, route.render());
			app.menu.select();

			route.callback();
		},
		route: function(path, render, callback) {
			this.routes[path] = { render: render, callback: callback };
		},
		bind: function(elem, path) {
			elem.addEventListener('click', function(e) {
				var href = elem.getAttribute('href');

				if (href.split('?').length >= 2)
					href = href.split('?')[0];

				if (!app.state.routes.hasOwnProperty(href))
					return;

				e.preventDefault();
				app.state.goTo(href, elem.getAttribute('href'));
			});
		}
	},
};

var render = {
	main: function(children) {
		return h('DIV', {'class': 'main'}, children);
	},
	sidebar: function () {
		var links = h('div', {'class': 'links'});

		(function(elem) {
			api.call('get_list_type', {}, function(res) {
				res.forEach(function (type) {
					var a = (h('A', {'href': '/category?id=' + type.p_types }, type.type));
					elem.appendChild(a);
					app.state.bind(a, a.getAttribute('href'));
				});
			});
		})(links);

		return h('DIV', {'class': 'sidebar'}, [
			h('h1', {}, 'Categories'),
			links,
			h('h1', {}, 'Garantie'),
			h('span', {}, 'Tous nos produits sont garantis 100% de source poilue durable.')
		]);
	},
	grid: function() {
		return [ render.sidebar(),
			render.main([
				h('DIV', {'class': 'carousel'}, [
					h('DIV', {'class': 'info'}, [
						h('H1', {}, 'Des produits de qualité'),
						h('H2', {}, 'Made in France')
					])
				]),
				h('DIV', {'class': 'grid'}, [
					h('DIV', {'class': 'pages'}, 'Page 1 / 1'),
					h('DIV', {'class': 'grid'})
				])
			])
		];
	},
	login: function () {
		return render.main([
			h('DIV', {'class': 'text'}, [
				h('H1', {}, 'Connexion'),
				h('H2', {}, 'Entrez vos données de connexion ce-dessous:'),
				h('DIV', {'class': 'form'}, [
					h('INPUT', {'type': 'text', 'name': 'login', 'placeholder': 'Login'}),
					h('INPUT', {'type': 'password', 'name': 'passwd', 'placeholder': 'Mot de passe'}),
					h('BUTTON', {'name': 'login', 'disabled': true}, 'Connexion'),
					h('BUTTON', {'name': 'register', 'disabled': true}, 'Créer un compte'),
				])
			])
		]);
	},
	text: function() {
		return [ render.sidebar(),
			render.main([
				h('DIV', {'class': 'text'})
			])
		];
	},
	pinfo: function(p) {
		return [
			h('H1', {}, p.name),
			h('IMG', {'src': 'http://www.leseclaireuses.com/ec_content/20160222-Curlyhairhacks22022016_3.jpg'}),
			h('DIV', {}, p.description),
			h('DIV', {'class': 'btn-cart'}, 'Ajouter au panier: ' + p.price + '€')
		];
	},
	cart: function() {
		var cart = h('div', {'class': 'cart'});

		(function(elem) {
			api.call('cart', {}, function(res) {
				console.log(res);
			});
		})(cart);

		return [ render.sidebar(),
			render.main([
				h('DIV', {'class': 'text'}, [
					h('H1', {}, 'Cart'),
					cart
				])
			])
		];
	},
	admin: function() {
		return [ render.sidebar(),
			render.main([
				h('DIV', {'class': 'text'}, [
					h('H1', {}, 'Admin'),
					h('DIV', {'class': 'admin'})
				])
			])
		];
	},
	profile: function () {
		return [];
	}
}


// route
app.state.route('/', render.grid, function() {
	api.call('get_product', { start: 0, len: 10 }, function(res) {
		console.log(res);
		res.result.forEach(function(item) {
			app.grid.addProduct(item);
		});
	});
});
app.state.route('/category', render.grid, function() {
	api.call('get_product', { type: parseInt(util.urlParam('id')), start: 0, len: 10 }, function(res) {
		console.log(res);
		res.result.forEach(function(item) {
			app.grid.addProduct(item);
		});
	});
});
app.state.route('/product', render.text, function() {
	api.call('get_pinfo', { pid: parseInt(util.urlParam('id')) }, function(res) {
		var p = res.result;

		append(document.querySelector('.main .text'), render.pinfo(p));
	});
});
app.state.route('/cart', render.cart, function() {
	// cart?
});
app.state.route('/admin', render.admin, function() {
	// admin
});
app.state.route('/profile', render.text, function() {
	// profile
	append(document.querySelector('.text'), [
		h('H1', {}, 'Profile'),
		h('H2', {}, app.user().login),
		h('A', {'href': '/logout'}, 'Log out'),
		h('BR', {}),
		h('A', {'href': '/admin'}, 'Admin')
	]);
});
app.state.route('/logout', render.text, function() {
	app.logout();
});
app.state.route('/login', render.login, function() {
	var login = document.querySelector('.form input[name="login"]');
	var passwd = document.querySelector('.form input[name="passwd"]');

	var connect = document.querySelector('.form button[name="login"]');
	var register = document.querySelector('.form button[name="register"]');

	document.querySelectorAll('.form input').forEach(function(input) {
		input.addEventListener('keyup', function(e) {
			if (e.keyCode === 13)
				connect.click();

			var disable = login.value === '' || passwd.value === '';
			connect.disabled = disable;
			register.disabled = disable;
		});
	});

	connect.addEventListener('click', function(e) {
		api.call('auth', { login: login.value, passwd: passwd.value }, function(res) {
			console.log(res);
			app.login(res.result.login);
		});
	});
	register.addEventListener('click', function(e) {
		api.call('add_user', { login: login.value, passwd: passwd.value }, function(res) {
			app.login(res.result.login);
		});
	});
});

// start the app
app.start(document.querySelector('.container'));
