/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */
!function (i) {
	if ("object" == typeof exports && "undefined" != typeof module) module.exports = i(); else if ("function" == typeof define && define.amd) define([], i); else {
		("undefined" != typeof window ? window : "undefined" != typeof global ? global : "undefined" != typeof self ? self : this).mimelite = i()
	}
}(function () {
	return function () {
		return function i(a, t, p) {
			function e(l, n) {
				if (!t[l]) {
					if (!a[l]) {
						var m = "function" == typeof require && require;
						if (!n && m) return m(l, !0);
						if (o) return o(l, !0);
						var s = new Error("Cannot find module '" + l + "'");
						throw s.code = "MODULE_NOT_FOUND", s
					}
					var c = t[l] = {exports: {}};
					a[l][0].call(c.exports, function (i) {
						return e(a[l][1][i] || i)
					}, c, c.exports, i, a, t, p)
				}
				return t[l].exports
			}

			for (var o = "function" == typeof require && require, l = 0; l < p.length; l++) e(p[l]);
			return e
		}
	}()({
		1: [function (i, a, t) {
			"use strict";

			function p() {
				this._types = Object.create(null), this._extensions = Object.create(null);
				for (var i = 0; i < arguments.length; i++) this.define(arguments[i]);
				this.define = this.define.bind(this), this.getType = this.getType.bind(this), this.getExtension = this.getExtension.bind(this)
			}

			p.prototype.define = function (i, a) {
				for (var t in i) {
					for (var p = i[t], e = 0; e < p.length; e++) {
						if ("*" != (o = p[e])[0]) {
							if (!a && o in this._types) throw new Error('Attempt to change mapping for "' + o + '" extension from "' + this._types[o] + '" to "' + t + '". Pass `force=true` to allow this, otherwise remove "' + o + '" from the list of extensions for "' + t + '".');
							this._types[o] = t
						}
					}
					if (a || !this._extensions[t]) {
						var o = p[0];

						/* original v2
						this._extensions[t] = "*" != o[0] ? o : o.substr(1) */

						/* original v1 (fix multiple extensions in types */
						this._extensions[t] = p
					}
				}
			}, p.prototype.getType = function (i) {
				var a = (i = String(i)).replace(/^.*[\/\\]/, "").toLowerCase(),
					t = a.replace(/^.*\./, "").toLowerCase(), p = a.length < i.length;
				return (t.length < a.length - 1 || !p) && this._types[t] || null
			}, p.prototype.getTypes = function (i) {
				i = String(i);
				var wildcard = i.split('/')[1] == '*' ? true : false,
					_return = '';
				if (wildcard === true) {
					i = /^\s*([^;\s]*)/.test(i) && RegExp.$1 && i.split('/')[0];
					var matches = [];
					var extObject = this._extensions;
					matches = Object.keys(this._extensions).map(function (key) {
						var patt = new RegExp("^" + i + "/");
						if (patt.test(key)) {
							return key;
						}
					}).filter(function(el) { return el != null; });
					_return = matches.toString() || null;
				} else {
					_return = i = /^\s*([^;\s]*)/.test(i) && RegExp.$1, i || null;
				}
				return _return;
			}, p.prototype.getExtension = function (i) {
				i = String(i);
				var wildcard = i.split('/')[1] == '*' ? true : false,
					_return = '';
				if (wildcard === true) {
					i = /^\s*([^;\s]*)/.test(i) && RegExp.$1 && i.split('/')[0];
					var matches = [];
					var extObject = this._extensions;
					matches = Object.keys(this._extensions).map(function (key) {
						var test = a;
						var patt = new RegExp("^" + i + "/");
						if (patt.test(key)) {
							return extObject[key];
						}
					}).filter(function(el) { return el != null; });
					_return = matches.toString() || null;
				} else {
					i = /^\s*([^;\s]*)/.test(i) && RegExp.$1, i && this._extensions[i.toLowerCase()] || null;
					var type = this._extensions[i.toLowerCase()];
					_return = type ? type.toString() : null;
				}
				return _return;
			}, a.exports = p
		}, {}], 2: [function (i, a, t) {
			"use strict";
			var p = i("./Mime");
			a.exports = new p(i("./types/standard"))
		}, {"./Mime": 1, "./types/standard": 3}], 3: [function (i, a, t) {
			a.exports = {
				"application/andrew-inset": ["ez"],
				"application/applixware": ["aw"],
				"application/atom+xml": ["atom"],
				"application/atomcat+xml": ["atomcat"],
				"application/atomsvc+xml": ["atomsvc"],
				"application/bdoc": ["bdoc"],
				"application/ccxml+xml": ["ccxml"],
				"application/cdmi-capability": ["cdmia"],
				"application/cdmi-container": ["cdmic"],
				"application/cdmi-domain": ["cdmid"],
				"application/cdmi-object": ["cdmio"],
				"application/cdmi-queue": ["cdmiq"],
				"application/cu-seeme": ["cu"],
				"application/dash+xml": ["mpd"],
				"application/davmount+xml": ["davmount"],
				"application/docbook+xml": ["dbk"],
				"application/dssc+der": ["dssc"],
				"application/dssc+xml": ["xdssc"],
				"application/ecmascript": ["ecma", "es"],
				"application/emma+xml": ["emma"],
				"application/epub+zip": ["epub"],
				"application/exi": ["exi"],
				"application/font-tdpfr": ["pfr"],
				"application/geo+json": ["geojson"],
				"application/gml+xml": ["gml"],
				"application/gpx+xml": ["gpx"],
				"application/gxf": ["gxf"],
				"application/gzip": ["gz"],
				"application/hjson": ["hjson"],
				"application/hyperstudio": ["stk"],
				"application/inkml+xml": ["ink", "inkml"],
				"application/ipfix": ["ipfix"],
				"application/java-archive": ["jar", "war", "ear"],
				"application/java-serialized-object": ["ser"],
				"application/java-vm": ["class"],
				"application/javascript": ["js", "mjs"],
				"application/json": ["json", "map"],
				"application/json5": ["json5"],
				"application/jsonml+json": ["jsonml"],
				"application/ld+json": ["jsonld"],
				"application/lost+xml": ["lostxml"],
				"application/mac-binhex40": ["hqx"],
				"application/mac-compactpro": ["cpt"],
				"application/mads+xml": ["mads"],
				"application/manifest+json": ["webmanifest"],
				"application/marc": ["mrc"],
				"application/marcxml+xml": ["mrcx"],
				"application/mathematica": ["ma", "nb", "mb"],
				"application/mathml+xml": ["mathml"],
				"application/mbox": ["mbox"],
				"application/mediaservercontrol+xml": ["mscml"],
				"application/metalink+xml": ["metalink"],
				"application/metalink4+xml": ["meta4"],
				"application/mets+xml": ["mets"],
				"application/mods+xml": ["mods"],
				"application/mp21": ["m21", "mp21"],
				"application/mp4": ["mp4s", "m4p"],
				"application/msword": ["doc", "dot"],
				"application/mxf": ["mxf"],
				"application/octet-stream": ["bin", "dms", "lrf", "mar", "so", "dist", "distz", "pkg", "bpk", "dump", "elc", "deploy", "exe", "dll", "deb", "dmg", "iso", "img", "msi", "msp", "msm", "buffer"],
				"application/oda": ["oda"],
				"application/oebps-package+xml": ["opf"],
				"application/ogg": ["ogx"],
				"application/omdoc+xml": ["omdoc"],
				"application/onenote": ["onetoc", "onetoc2", "onetmp", "onepkg"],
				"application/oxps": ["oxps"],
				"application/patch-ops-error+xml": ["xer"],
				"application/pdf": ["pdf"],
				"application/pgp-encrypted": ["pgp"],
				"application/pgp-signature": ["asc", "sig"],
				"application/pics-rules": ["prf"],
				"application/pkcs10": ["p10"],
				"application/pkcs7-mime": ["p7m", "p7c"],
				"application/pkcs7-signature": ["p7s"],
				"application/pkcs8": ["p8"],
				"application/pkix-attr-cert": ["ac"],
				"application/pkix-cert": ["cer"],
				"application/pkix-crl": ["crl"],
				"application/pkix-pkipath": ["pkipath"],
				"application/pkixcmp": ["pki"],
				"application/pls+xml": ["pls"],
				"application/postscript": ["ai", "eps", "ps"],
				"application/pskc+xml": ["pskcxml"],
				"application/raml+yaml": ["raml"],
				"application/rdf+xml": ["rdf", "owl"],
				"application/reginfo+xml": ["rif"],
				"application/relax-ng-compact-syntax": ["rnc"],
				"application/resource-lists+xml": ["rl"],
				"application/resource-lists-diff+xml": ["rld"],
				"application/rls-services+xml": ["rs"],
				"application/rpki-ghostbusters": ["gbr"],
				"application/rpki-manifest": ["mft"],
				"application/rpki-roa": ["roa"],
				"application/rsd+xml": ["rsd"],
				"application/rss+xml": ["rss"],
				"application/rtf": ["rtf"],
				"application/sbml+xml": ["sbml"],
				"application/scvp-cv-request": ["scq"],
				"application/scvp-cv-response": ["scs"],
				"application/scvp-vp-request": ["spq"],
				"application/scvp-vp-response": ["spp"],
				"application/sdp": ["sdp"],
				"application/set-payment-initiation": ["setpay"],
				"application/set-registration-initiation": ["setreg"],
				"application/shf+xml": ["shf"],
				"application/smil+xml": ["smi", "smil"],
				"application/sparql-query": ["rq"],
				"application/sparql-results+xml": ["srx"],
				"application/srgs": ["gram"],
				"application/srgs+xml": ["grxml"],
				"application/sru+xml": ["sru"],
				"application/ssdl+xml": ["ssdl"],
				"application/ssml+xml": ["ssml"],
				"application/tei+xml": ["tei", "teicorpus"],
				"application/thraud+xml": ["tfi"],
				"application/timestamped-data": ["tsd"],
				"application/voicexml+xml": ["vxml"],
				"application/wasm": ["wasm"],
				"application/widget": ["wgt"],
				"application/winhlp": ["hlp"],
				"application/wsdl+xml": ["wsdl"],
				"application/wspolicy+xml": ["wspolicy"],
				"application/xaml+xml": ["xaml"],
				"application/xcap-diff+xml": ["xdf"],
				"application/xenc+xml": ["xenc"],
				"application/xhtml+xml": ["xhtml", "xht"],
				"application/xml": ["xml", "xsl", "xsd", "rng"],
				"application/xml-dtd": ["dtd"],
				"application/xop+xml": ["xop"],
				"application/xproc+xml": ["xpl"],
				"application/xslt+xml": ["xslt"],
				"application/xspf+xml": ["xspf"],
				"application/xv+xml": ["mxml", "xhvml", "xvml", "xvm"],
				"application/yang": ["yang"],
				"application/yin+xml": ["yin"],
				"application/zip": ["zip"],
				"audio/3gpp": ["*3gpp"],
				"audio/adpcm": ["adp"],
				"audio/basic": ["au", "snd"],
				"audio/midi": ["mid", "midi", "kar", "rmi"],
				"audio/mp3": ["*mp3"],
				"audio/mp4": ["m4a", "mp4a"],
				"audio/mpeg": ["mpga", "mp2", "mp2a", "mp3", "m2a", "m3a"],
				"audio/ogg": ["oga", "ogg", "spx"],
				"audio/s3m": ["s3m"],
				"audio/silk": ["sil"],
				"audio/wav": ["wav"],
				"audio/wave": ["*wav"],
				"audio/webm": ["weba"],
				"audio/xm": ["xm"],
				"font/collection": ["ttc"],
				"font/otf": ["otf"],
				"font/ttf": ["ttf"],
				"font/woff": ["woff"],
				"font/woff2": ["woff2"],
				"image/aces": ["exr"],
				"image/apng": ["apng"],
				"image/bmp": ["bmp"],
				"image/cgm": ["cgm"],
				"image/dicom-rle": ["drle"],
				"image/emf": ["emf"],
				"image/fits": ["fits"],
				"image/g3fax": ["g3"],
				"image/gif": ["gif"],
				"image/heic": ["heic"],
				"image/heic-sequence": ["heics"],
				"image/heif": ["heif"],
				"image/heif-sequence": ["heifs"],
				"image/ief": ["ief"],
				"image/jls": ["jls"],
				"image/jp2": ["jp2", "jpg2"],
				"image/jpeg": ["jpeg", "jpg", "jpe"],
				"image/jpm": ["jpm"],
				"image/jpx": ["jpx", "jpf"],
				"image/ktx": ["ktx"],
				"image/png": ["png"],
				"image/sgi": ["sgi"],
				"image/svg+xml": ["svg", "svgz"],
				"image/t38": ["t38"],
				"image/tiff": ["tif", "tiff"],
				"image/tiff-fx": ["tfx"],
				"image/webp": ["webp"],
				"image/wmf": ["wmf"],
				"message/disposition-notification": ["disposition-notification"],
				"message/global": ["u8msg"],
				"message/global-delivery-status": ["u8dsn"],
				"message/global-disposition-notification": ["u8mdn"],
				"message/global-headers": ["u8hdr"],
				"message/rfc822": ["eml", "mime"],
				"model/gltf+json": ["gltf"],
				"model/gltf-binary": ["glb"],
				"model/iges": ["igs", "iges"],
				"model/mesh": ["msh", "mesh", "silo"],
				"model/vrml": ["wrl", "vrml"],
				"model/x3d+binary": ["x3db", "x3dbz"],
				"model/x3d+vrml": ["x3dv", "x3dvz"],
				"model/x3d+xml": ["x3d", "x3dz"],
				"text/cache-manifest": ["appcache", "manifest"],
				"text/calendar": ["ics", "ifb"],
				"text/coffeescript": ["coffee", "litcoffee"],
				"text/css": ["css"],
				"text/csv": ["csv"],
				"text/html": ["html", "htm", "shtml"],
				"text/jade": ["jade"],
				"text/jsx": ["jsx"],
				"text/less": ["less"],
				"text/markdown": ["markdown", "md"],
				"text/mathml": ["mml"],
				"text/n3": ["n3"],
				"text/plain": ["txt", "text", "conf", "def", "list", "log", "in", "ini"],
				"text/richtext": ["rtx"],
				"text/rtf": ["*rtf"],
				"text/sgml": ["sgml", "sgm"],
				"text/shex": ["shex"],
				"text/slim": ["slim", "slm"],
				"text/stylus": ["stylus", "styl"],
				"text/tab-separated-values": ["tsv"],
				"text/troff": ["t", "tr", "roff", "man", "me", "ms"],
				"text/turtle": ["ttl"],
				"text/uri-list": ["uri", "uris", "urls"],
				"text/vcard": ["vcard"],
				"text/vtt": ["vtt"],
				"text/xml": ["*xml"],
				"text/yaml": ["yaml", "yml"],
				"video/3gpp": ["3gp", "3gpp"],
				"video/3gpp2": ["3g2"],
				"video/h261": ["h261"],
				"video/h263": ["h263"],
				"video/h264": ["h264"],
				"video/jpeg": ["jpgv"],
				"video/jpm": ["*jpm", "jpgm"],
				"video/mj2": ["mj2", "mjp2"],
				"video/mp2t": ["ts"],
				"video/mp4": ["mp4", "mp4v", "mpg4"],
				"video/mpeg": ["mpeg", "mpg", "mpe", "m1v", "m2v"],
				"video/ogg": ["ogv"],
				"video/quicktime": ["qt", "mov"],
				"video/webm": ["webm"]
			}
		}, {}]
	}, {}, [2])(2)
});