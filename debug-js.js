// https://gist.github.com/stracker-phil/e5b3bbd5d5eb4ffb2acdcda90d8bd04f
window.globalSearch = function (startObject, needle, searchField, limit) {
	if (!startObject) {
		console.warn('globalSearch() usage:');
		console.info('globalSearch(scope, needle, field, limit)');
		console.info("\nParams:" +
			"\n- scope .. document, window, or a global variable name, like 'etCore'" +
			"\n- needle .. scalar value, RegExp object or callback function" +
			"\n- field .. either 'key', 'value', 'all'" +
			"\n- limit .. optional. Stop searching after number of matches"
		);
		console.info("\nSamples:" +
			"\nglobalSearch(document, 'sample', 'value', 10)" +
			"\nglobalSearch(window, '^et', 'key')" +
			"\nglobalSearch(window, /sub(word|term)/', 'all', 3)" +
			"\nglobalSearch('etCore', /portability/', 'all')" +
			"\nglobalSearch('etCore', item => 'function' === typeof item, 'all')"
		);
		console.info("\nNotes:" +
			"\nResults are stored in the global `gsResults` array:" +
			"\n`gsResults[1]` contains details about the first match."
		);
		return;
	}

	var startName = '';

	if ('string' === typeof startObject) {
		startName = startObject;
		try {
			startObject = eval(startObject);
		} catch (exception) {
			console.error(
				'The scope object "' + startName + '" was not found:',
				exception.message
			);
			startObject = {};
		}
	} else if (window === startObject) {
		startName = 'window';
	} else if (document === startObject) {
		startName = 'document';
	}

	var stack, fullSearch;

	if ('gsResults' === startName && window.gsResults && Array.isArray(gsResults)) {
		fullSearch = false;
		stack = [];
		for (var resI = 0; resI < gsResults.length; resI++) {
			if (gsResults[resI] && 'object' === typeof gsResults[resI]) {
				var resPath = gsResults[resI].pathOrig;
				var resObj;

				try {
					resObj = eval(resPath);
				} catch (exception) {
					resObj = null;
				}

				stack.push([resObj, resPath, resPath]);
			}
		}
	} else {
		fullSearch = true;
		stack = [[startObject, startName, startName]];
	}

	var searched = [];
	var found = 0;
	var count = 1;
	var isCallback = 'function' === typeof needle;
	var isRegex = 'string' === typeof needle
		&& (
			-1 !== needle.indexOf('*')
			|| '^' === needle[0]
			|| '$' === needle[needle.length - 1]
		);
	var startTime = (new Date()).getTime();

	window.gsResults = [];

	if (isRegex) {
		needle = new RegExp(needle);
	} else if ('object' === typeof needle && needle instanceof RegExp) {
		isRegex = true;
	}

	if (undefined === limit) {
		limit = -1;
	}

	if (undefined === searchField) {
		searchField = 'value';
	}

	if (-1 === ['value', 'key', 'all'].indexOf(searchField)) {
		console.error(
			'The "searchField" parameter must be either of [value|key|all]. Found:',
			searchField
		);
		return;
	}

	function isArray(test) {
		var type = Object.prototype.toString.call(test);
		return '[object Array]' === type || '[object NodeList]' === type;
	}

	function isElement(o) {
		var res;
		try {
			res = typeof HTMLElement === "object"
				? o instanceof HTMLElement  //DOM2
				: o && typeof o === "object"
				&& o.nodeType === 1
				&& typeof o.nodeName === "string";
		} catch ($ex) {
			res = false;
		}
		return res;
	}

	function isMatch(type, value) {
		if ('undefined' === typeof value || null === value) {
			return value === needle;
		}

		if (isCallback) {
			return needle(value, type);
		} else if (isRegex) {
			return needle.test(value.toString ? value.toString() : '');
		} else {
			return needle === value;
		}
	}

	function result(type, address, shortAddr, value) {
		var charLimit = 150;
		var msg = [];
		var displayRes = value.toString();
		var addressParts = shortAddr.split('.');
		var displayAddress = shortAddr;

		if (displayRes.length > charLimit) {
			displayRes = displayRes.substr(0, charLimit - 3) + '...';
		}
		if (displayAddress.length > charLimit) {
			var addrFront = '';
			var addrTail = '';
			for (var i = 0; i < addressParts.length / 2; i++) {
				if (addrTail.length) {
					addrTail = '.' + addrTail;
				}
				addrTail = addressParts[addressParts.length - i - 1] + addrTail;
				if (3 + addrTail.length + addrFront.length > charLimit) {
					break;
				}
				if (addrFront.length) {
					addrFront += '.';
				}
				addrFront += addressParts[i];
				if (3 + addrTail.length + addrFront.length > charLimit) {
					break;
				}
			}
			displayAddress = addrFront + '...' + addrTail;
		}

		found++;
		window.gsResults[found] = {
			match: type,
			value: value,
			name: addressParts[addressParts.length - 1],
			pathOrig: address,
			pathShort: shortAddr
		};

		msg.push(found + ". Match: \t" + type.toUpperCase());
		msg.push("   Type:    \t" + typeof value);
		msg.push("   Name:    \t" + addressParts[addressParts.length - 1]);
		msg.push("   Value:   \t" + displayRes);
		msg.push("   Address: \t" + displayAddress);
		msg.push("   Details: \tgsResults[" + found + ']');

		console.log(msg.join("\n"));
	}

	function skip(obj, key) {
		var traversing = [
			'firstChild',
			'previousSibling',
			'nextSibling',
			'lastChild',
			'previousElementSibling',
			'nextElementSibling',
			'firstEffect',
			'nextEffect',
			'lastEffect'
		];
		var scopeChange = [
			'ownerDocument'
		];
		var deprecatedDOM = [
			'webkitStorageInfo'
		];

		if (-1 !== traversing.indexOf(key)) {
			return true;
		}
		if (-1 !== scopeChange.indexOf(key)) {
			return true;
		}
		if (-1 !== deprecatedDOM.indexOf(key)) {
			return true;
		}

		var isInvalid = false;
		try {
			obj[key];
		} catch (ex) {
			isInvalid = true;
		}

		return isInvalid;
	}

	while (stack.length) {
		if (limit > 0 && found >= limit) {
			break;
		}

		var fromStack = stack.pop();
		var obj = fromStack[0];
		var address = fromStack[1];
		var display = fromStack[2];

		count++;
		if ('key' !== searchField && isMatch('value', obj)) {
			result('value', address, display, obj);
			if (limit > 0 && found >= limit) {
				break;
			}
		}

		if (obj && typeof obj == 'object' && -1 === searched.indexOf(obj)) {
			var objIsArray = isArray(obj);

			if (isElement(obj) && obj.id) {
				display = 'document.getElementById("' + obj.id + '")';
			}

			for (var i in obj) {
				if (obj.hasOwnProperty && !obj.hasOwnProperty(i)) {
					continue;
				}
				if (skip(obj, i)) {
					continue;
				}

				var subAddr = (objIsArray || !isNaN(i)) ? '[' + i + ']' : '.' + i;
				var addr = address + subAddr;
				var displayAddr = display + subAddr;

				if (fullSearch) {
					stack.push([obj[i], addr, displayAddr]);
				}
				count++;

				if ('value' !== searchField && isMatch('key', i)) {
					result('key', address, displayAddr, obj[i]);
					if (limit > 0 && found >= limit) {
						break;
					}
				}
			}
			searched.push(obj);
		}
	}

	searched = null;

	console.log("\n-----"
		+ "\nAll Done!"
		+ "\nSearched "
		+ count.toLocaleString()
		+ ' items'
		+ "\nFound "
		+ found.toLocaleString()
		+ ' results'
		+ "\nIn "
		+ (Math.round(((new Date()).getTime() - startTime) / 10) / 100)
		+ ' sec'
		+ "\n-----\n"
	);
	return found;
};
