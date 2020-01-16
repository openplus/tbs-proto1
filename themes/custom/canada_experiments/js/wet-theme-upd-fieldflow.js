/*!
 * Web Experience Toolkit (WET) / BoÃ®te Ã Â  outils de l'expÃ©rience Web (BOEW)
 * wet-boew.github.io/wet-boew/License-en.html / wet-boew.github.io/wet-boew/Licence-fr.html
 * v5.1.0-development - 2019-03-22
 * - gcweb-menu changed for gcweb-v2
 * - fix - Menu screen reader compatibility issue
 * - 2019-02-27 - Initial commit - Chat wizard
 * - 2019-02-28 - Binding wb5 table with URL mapping plugin
 * - 2019-03-09 - Added gcweb-menu to remove gcweb-v2 codename
 * - 2019-03-16 - Added Geomap filter in the action manager
 * - 2019-03-22 - Added Chat wizard and steps form plugins
 * - 2019-04-11 - Minor updates to the Chat wizard
 * - 2019-05-07 - Hotfix GCWeb menu V2
 * - 2019-05-14 - GCWeb responsive header and Experimental
 * - 2019-06-11 - GCWeb menu instructions
 * - 2019-06-26 - MT add pre-filtering support
 * - 2019-06-26 - CC allowed to enable smart data-table search
 *
 */

//  ( function($, Drupal, wb){

//     // var z = $('body');

//     // console.log(z);

//     ! function(u, l) {
//     "use strict";

//     function p(e, t, a) {
//         var r, n, i;
//         for (r = a[t]; n = r.shift();)(i = n.action) && (e.trigger(i + "." + h, n), delete n.action)
//     }
//     var e = l.doc,
//         f = "wb-actionmng",
//         d = "." + f,
//         t = "[data-" + f + "]",
//         c = f + "Rn",
//         h = f + d,
//         b = {},
//         g = {},
//         m = {},
//         a = ["mapfilter", "patch", "ajax", "addClass", "removeClass", "tblfilter", "run"].join("." + h + " ") + "." + h,
//         v = function(e, t, a) {
//             t[e] || (t[e] = []), t[e].push(a)
//         };
//     e.on("do." + h, function(e) {
//         var t, a, r, n, i, o, s, l = e.element || e.target,
//             d = l.id,
//             c = e.actions || [];
//         if ((l === e.target || e.currentTarget === e.target) && -1 === l.className.indexOf(f)) {
//             for (u.isArray(c) || (c = [c]), (r = c.length) && (t = u(l)).addClass(f), d && b[d] && p(t, d, b), a = 0; a !== r; a += 1)(i = (n = c[a]).action) && ((o = n.target) ? (n.trgbefore ? v(o, b, n) : v(o, g, n), (s = n.trggroup) && v(s, m, n)) : t.trigger(i + "." + h, n));
//             d && g[d] && p(t, d, g), u(e.target).removeClass(f)
//         }
//     }), e.on("clean." + h, function(e) {
//         var t, a, r = e.element || e.target,
//             n = e.trggroup;
//         if ((r === e.target || e.currentTarget === e.target) && n && m[n])
//             for (t = m[n]; a = t.shift();) delete a.action
//     }), e.on(a, d, function(e, t) {
//         var a = e.type;
//         if (h === e.namespace) switch (a) {
//             case "run":
//                 ! function(e, t) {
//                     var a, r, n, i, o = e.target,
//                         s = u(o),
//                         l = m[t.trggroup];
//                     if (l && !s.hasClass(c)) {
//                         for (s.addClass(c), r = l.length, a = 0; a !== r; a += 1)(i = (n = l[a]).action) && s.trigger(i + "." + h, n);
//                         s.removeClass(c)
//                     }
//                 }(e, t);
//                 break;
//             case "tblfilter":
//                 ! function(e, t) {
//                     var a = e.target,
//                         r = u(t.source || a),
//                         n = t.column,
//                         i = parseInt(n, 10),
//                         o = !!t.regex,
//                         s = !t.smart || !!t.smart,
//                         l = !t.caseinsen || !!t.caseinsen;
//                     if ("TABLE" !== r.get(0).nodeName) throw "Table filtering can only applied on table";
//                     n = !0 === i ? i : n, r.dataTable({
//                         retrieve: !0
//                     }).api().column(n).search(t.value, o, s, l).draw()
//                 }(e, t);
//                 break;
//             case "addClass":
//                 ! function(e, t) {
//                     var a = u(t.source || e.target);
//                     t.class && a.addClass(t.class)
//                 }(e, t);
//                 break;
//             case "removeClass":
//                 ! function(e, t) {
//                     var a = u(t.source || e.target);
//                     t.class && a.removeClass(t.class)
//                 }(e, t);
//                 break;
//             case "ajax":
//                 ! function(e, t) {
//                     var a, r, n;
//                     t.container ? a = u(t.container) : (r = l.getId(), a = u("<div id='" + r + "'></div>"), u(e.target).after(a)), t.trigger && a.attr("data-trigger-wet", "true"), n = t.type ? t.type : "replace", a.attr("data-ajax-" + n, t.url), a.one("wb-contentupdated", function(e, t) {
//                         var a = e.currentTarget,
//                             r = a.getAttribute("data-trigger-wet");
//                         a.removeAttribute("data-ajax-" + t["ajax-type"]), r && (u(a).find(l.allSelectors).addClass("wb-init").filter(":not(#" + a.id + " .wb-init .wb-init)").trigger("timerpoke.wb"), a.removeAttribute("data-trigger-wet"))
//                     }), a.trigger("wb-update.wb-data-ajax")
//                 }(e, t);
//                 break;
//             case "patch":
//                 ! function(e, t) {
//                     var a = t.source,
//                         r = t.patches,
//                         n = !!t.cumulative;
//                     r && (u.isArray(r) || (r = [r]), u(a).trigger({
//                         type: "patches.wb-jsonmanager",
//                         patches: r,
//                         fpath: t.fpath,
//                         filter: t.filter || [],
//                         filternot: t.filternot || [],
//                         cumulative: n
//                     }))
//                 }(0, t);
//                 break;
//             case "mapfilter":
//                 ! function(e, t) {
//                     var a = u(t.source || e.target).get(0).geomap,
//                         r = t.filter,
//                         n = t.value;
//                     "aoi" === r && a.zoomAOI(n), "layer" === r && a.showLayer(n, !0)
//                 }(e, t)
//         }
//     }), e.on("timerpoke.wb wb-init.wb-actionmng", t, function(e) {
//         var t, a, r, n, i, o, s = l.init(e, f, d);
//         if (s) {
//             if (t = u(s), a = l.getData(t, f))
//                 for (u.isArray(a) || (a = [a]), n = a.length, r = 0; r !== n; r += 1)(o = (i = a[r]).trggroup) && i.action && v(o, m, i);
//             l.ready(t, f)
//         }
//     }), l.add(t)
// }(jQuery, wb),
// function(a, r) {
//     "use strict";
//     var e = r.doc,
//         n = "wb-bgimg",
//         i = "[data-bgimg]";
//     e.on("timerpoke.wb wb-init." + n, i, function(e) {
//         var t = r.init(e, n, i);
//         t && (t.style.backgroundImage = "url(" + t.dataset.bgimg + ")", r.ready(a(t), n))
//     }), r.add(i)
// }(jQuery, wb),
// function(j, d, C) {
//     "use strict";
//     var e, p = "wb-data-json",
//         u = "wb-json",
//         t = ["[data-json-after]", "[data-json-append]", "[data-json-before]", "[data-json-prepend]", "[data-json-replace]", "[data-json-replacewith]", "[data-" + u + "]"],
//         f = ["after", "append", "before", "prepend", "val"],
//         h = /(href|src|data-*|pattern|min|max|step|low|high)/,
//         b = /(checked|selected|disabled|required|readonly|multiple|hidden)/,
//         a = t.length,
//         g = t.join(","),
//         m = p + "-queue",
//         r = C.doc,
//         v = function(e, t, a, r, n) {
//             var i, o = j(e),
//                 s = {
//                     url: t,
//                     refId: a,
//                     nocache: r,
//                     nocachekey: n
//                 },
//                 l = d[p];
//             !l || "http" !== t.substr(0, 4) && "//" !== t.substr(0, 2) || (i = C.getUrlParts(t), C.pageUrlParts.protocol === i.protocol && C.pageUrlParts.host === i.host || Modernizr.cors && !l.forceCorsFallback || "function" == typeof l.corsFallback && (s.dataType = "jsonp", s.jsonp = "callback", s = l.corsFallback(s))), o.trigger({
//                 type: "json-fetch.wb",
//                 fetch: s
//             })
//         },
//         E = function(e, t, a) {
//             var r, n, i, o, s, l, d, c, u, p, f, h, b, g = t.mapping || [{}],
//                 m = t.filter || [],
//                 v = t.filternot || [],
//                 w = t.queryall,
//                 y = t.tobeclone,
//                 x = e.className,
//                 k = e,
//                 A = t.source ? document.querySelector(t.source) : e.querySelector("template");
//             if (j.isArray(a) || (a = "object" != typeof a ? [a] : j.map(a, function(e, t) {
//                     return "object" != typeof e || j.isArray(e) ? e = {
//                         "@id": t,
//                         "@value": e
//                     } : e["@id"] || (e["@id"] = t), [e]
//                 })), i = a.length, j.isArray(g) || (g = [g]), r = g.length, "TABLE" === e.tagName && g && -1 !== x.indexOf("wb-tables-inited") && "string" == typeof g[0]) {
//                 for (b = j(e).dataTable({
//                         retrieve: !0
//                     }).api(), n = 0; n < i; n += 1)
//                     if (o = a[n], S(o, m, v)) {
//                         for (c = "/" + n, h = [], s = 0; s < r; s += 1) h.push(jsonpointer.get(a, c + g[s]));
//                         b.row.add(h)
//                     }
//                 b.draw()
//             } else if (A)
//                 for (A.content || C.tmplPolyfill(A), t.appendto && (k = j(t.appendto).get(0)), n = 0; n < i; n += 1)
//                     if (o = a[n], S(o, m, v)) {
//                         for (c = "/" + n, u = y ? A.content.querySelector(y).cloneNode(!0) : A.content.cloneNode(!0), w && (p = u.querySelectorAll(w)), s = 0; s < r || 0 === s; s += 1) l = g[s], f = p ? p[s] : l.selector ? u.querySelector(l.selector) : u, (d = l.attr) && (f.hasAttribute(d) || f.setAttribute(d, ""), f = f.getAttributeNode(d)), h = "string" == typeof o ? o : "string" == typeof l ? jsonpointer.get(a, c + l) : jsonpointer.get(a, c + l.value), l.placeholder && (h = (f.textContent || "").replace(l.placeholder, h)), j.isArray(h) ? E(f, l, h) : l.isHTML ? f.innerHTML = h : f.textContent = h;
//                         k.appendChild(u)
//                     }
//         },
//         S = function(e, t, a) {
//             var r, n, i, o = t.length,
//                 s = a.length,
//                 l = !1;
//             if (o || s) {
//                 for (r = 0; r < o; r += 1)
//                     if (n = t[r], i = c(jsonpointer.get(e, n.path), n.value), n.optional) l = l || i;
//                     else {
//                         if (!i) return !1;
//                         l = !0
//                     }
//                 if (o && !l) return !1;
//                 for (r = 0; r < s; r += 1)
//                     if (n = a[r], (i = c(jsonpointer.get(e, n.path), n.value)) && !n.optional || i && n.optional) return !1
//             }
//             return !0
//         },
//         c = function(e, t) {
//             switch (typeof e) {
//                 case "undefined":
//                     return !1;
//                 case "boolean":
//                 case "string":
//                 case "number":
//                     return e === t;
//                 case "object":
//                     if (null === e) return null === t;
//                     if (j.isArray(e)) {
//                         if (j.isArray(t) || e.length !== t.length) return !1;
//                         for (var a = 0, r = e.length; a < r; a++)
//                             if (!c(e[a], t[a])) return !1;
//                         return !0
//                     }
//                     var n = i(t).length;
//                     if (i(e).length !== n) return !1;
//                     for (a = 0; a < n; a++)
//                         if (!c(e[a], t[a])) return !1;
//                     return !0;
//                 default:
//                     return !1
//             }
//         },
//         i = function(e) {
//             if (j.isArray(e)) {
//                 for (var t = new Array(e.length), a = 0; a < t.length; a++) t[a] = "" + a;
//                 return t
//             }
//             if (Object.keys) return Object.keys(e);
//             t = [];
//             for (var r in e) e.hasOwnProperty(r) && t.push(r);
//             return t
//         };
//     r.on("json-failed.wb", g, function() {
//         throw "Bad JSON Fetched from url in " + p
//     }), Modernizr.load({
//         test: "content" in document.createElement("template"),
//         nope: "site!deps/template" + C.getMode() + ".js"
//     }), r.on("timerpoke.wb wb-init.wb-data-json wb-update.wb-data-json json-fetched.wb", g, function(e) {
//         if (e.currentTarget === e.target) switch (e.type) {
//             case "timerpoke":
//             case "wb-init":
//                 ! function(e) {
//                     var t, a = C.init(e, p, g);
//                     if (a) {
//                         var r, n, i, o, s, l = ["before", "replace", "replacewith", "after", "append", "prepend"],
//                             d = l.length,
//                             c = [];
//                         for (t = j(a), i = 0; i !== d; i += 1) r = l[i], null !== (s = a.getAttribute("data-json-" + r)) && c.push({
//                             type: r,
//                             url: s
//                         });
//                         if (C.ready(t, p), (n = C.getData(t, u)) && n.url) c.push(n);
//                         else if (n && j.isArray(n))
//                             for (d = n.length, i = 0; i !== d; i += 1) c.push(n[i]);
//                         for (t.data(m, c), d = c.length, i = 0; i !== d; i += 1) o = c[i], v(a, o.url, i, o.nocache, o.nocachekey)
//                     }
//                 }(e);
//                 break;
//             case "wb-update":
//                 ! function(e) {
//                     var t = e.target,
//                         a = j(t),
//                         r = a.data(m),
//                         n = r.length,
//                         i = e["wb-json"];
//                     if (!i.url || !i.type && !i.source) throw "Data JSON update not configured properly";
//                     r.push(i), a.data(m, r), v(t, i.url, n)
//                 }(e);
//                 break;
//             default:
//                 ! function(e) {
//                     var t, a = e.target,
//                         r = j(a),
//                         n = r.data(m),
//                         i = e.fetch,
//                         o = n[i.refId],
//                         s = o.type,
//                         l = o.prop || o.attr,
//                         d = o.showempty,
//                         c = i.response,
//                         u = typeof c;
//                     if (d || "undefined" != u) {
//                         if (d && "undefined" == u && (c = ""), t = jQuery.ajaxSettings.cache, jQuery.ajaxSettings.cache = !0, s)
//                             if ("replace" === s) r.html(c);
//                             else if ("replacewith" === s) r.replaceWith(c);
//                         else if ("addclass" === s) r.addClass(c);
//                         else if ("removeclass" === s) r.removeClass(c);
//                         else if ("prop" === s && l && b.test(l)) r.prop(l, c);
//                         else if ("attr" === s && l && h.test(l)) r.attr(l, c);
//                         else {
//                             if ("function" != typeof r[s] || -1 === f.indexOf(s)) throw p + " do not support type: " + s;
//                             r[s](c)
//                         } else s = "template", E(a, o, c), o.trigger && r.find(C.allSelectors).addClass("wb-init").filter(":not(#" + a.id + " .wb-init .wb-init)").trigger("timerpoke.wb");
//                         jQuery.ajaxSettings.cache = t, r.trigger("wb-contentupdated", {
//                             "json-type": s,
//                             content: c
//                         })
//                     }
//                 }(e)
//         }
//         return !0
//     });
//     for (e = 0; e !== a; e += 1) C.add(t[e])
// }(jQuery, window, wb),
// function(a, n, r) {
//     "use strict";

//     function i(e) {
//         if (!e.content) {
//             var t, a, r = e;
//             for (t = r.childNodes, a = n.createDocumentFragment(); t[0];) a.appendChild(t[0]);
//             r.content = a
//         }
//     }
//     var o = "wb-template",
//         s = "template",
//         e = r.doc;
//     r.tmplPolyfill = i, e.on("timerpoke.wb wb-init.wb-template", s, function(e) {
//         var t = r.init(e, o, s);
//         t && (i(t), r.ready(a(t), o))
//     }), r.add(s)
// }(jQuery, document, wb),
// function(r, e, n) {
//     "use strict";
//     var i = "wb-doaction",
//         o = "a[data-" + i + "],button[data-" + i + "]",
//         s = "do.wb-actionmng",
//         l = n.doc;
//     l.on("click", o, function(e) {
//         var t = e.target,
//             a = r(t);
//         if (e.currentTarget !== e.target && (t = (a = a.parentsUntil("main", o))[0]), "BUTTON" === t.nodeName || "A" === t.nodeName) return n.isReady ? a.trigger({
//             type: s,
//             actions: n.getData(a, i)
//         }) : l.one("wb-ready.wb", function() {
//             a.trigger({
//                 type: s,
//                 actions: n.getData(a, i)
//             })
//         }), !1
//     })
// }(jQuery, window, wb),
// function(q, T, N) {
//     "use strict";

//     function r(e, t) {
//         var a = t.$selElm,
//             r = t.name,
//             n = t.value;
//         r && t.provEvt.setAttribute("name", r), n && a.val(n), a.attr("data-" + o, o)
//     }

//     function n(e, t) {
//         var a = q(t.origin),
//             r = q(e.target).data($),
//             n = t.toggle;
//         n && "string" == typeof n && (n = {
//             selector: n
//         }), n = q.extend({}, n, r.toggle), a.addClass("wb-toggle"), a.trigger("toggle.wb-toggle", n), n.type = "off", a.one(P, function() {
//             a.addClass("wb-toggle"), a.trigger("toggle.wb-toggle", n), a.removeClass("wb-toggle")
//         })
//     }

//     function i(e, t) {
//         var a, r, n, i, o, s, l, d, c = t.outputctnrid,
//             u = t.actions,
//             p = t.lblselector,
//             f = !!t.required,
//             h = !t.noreqlabel,
//             b = t.items,
//             g = e.target,
//             m = q(g),
//             v = t.source,
//             w = m.data($).i18n,
//             y = t.attributes,
//             x = N.getId(),
//             k = "<legend class='h5 ",
//             A = "</span>",
//             j = "<fieldset id='" + x + "' data-" + W + "='" + g.id + "' " + G + "='" + v.id + "' class='" + D + " mrgn-bttm-md'",
//             C = "",
//             E = t.typeRadCheck,
//             S = t.inline,
//             I = O + x;
//         if (y && "object" == typeof y)
//             for (i in y) y.hasOwnProperty(i) && (j += " " + i + "='" + y[i] + "'");
//         for (a = q(j + "></fieldset>"), f && h && (k += " required", A += " <strong class='required'>(" + w.required + ")</strong>"), k += "'>", A += "</legend>", p ? (r = q("<div>" + t.label + "</div>").find(p), a.append(k + r.html() + A).append(r.nextAll()), n = r.prevAll()) : a.append(q(k + t.label + A)), i = 0, o = b.length; i !== o; i += 1)
//             if ((d = b[i]).group)
//                 for (C += "<p>" + d.label + "</p>", l = d.group.length, s = 0; s !== l; s += 1) C += X(d.group[s], I, E, S, f);
//             else C += X(d, I, E, S, f);
//         a.append(C), q("#" + c).append(a), n && a.before(n), u && 0 < u.length && a.data(V, u), Y(m, H, x)
//     }
//     var z = "wb-fieldflow",
//         u = "." + z,
//         p = z + "-form",
//         v = z + "-sub",
//         D = z + "-init",
//         e = "." + D,
//         O = z + N.getId(),
//         L = "[name^=" + O + "]",
//         w = z + "-label",
//         b = z + "-header",
//         t = "." + p,
//         a = "wb-init" + u,
//         M = "draw" + u,
//         B = "action" + u,
//         U = "submit" + u,
//         R = "submited" + u,
//         f = "ready" + u,
//         P = "clean" + u,
//         F = "reset" + u,
//         Q = "createctrl" + u,
//         H = z + "-register",
//         J = z + "-hdnfld",
//         $ = z + "-config",
//         V = z + "-push",
//         _ = z + "-submit",
//         K = z + "-action",
//         W = z + "-origin",
//         G = "data-" + z + "-source",
//         o = z + "-flagoptvalue",
//         s = N.doc,
//         h = {
//             toggle: {
//                 stateOn: "visible",
//                 stateOff: "hidden"
//             },
//             i18n: {
//                 en: {
//                     btn: "Continue",
//                     defaultsel: "Make your selection...",
//                     required: "required"
//                 },
//                 fr: {
//                     btn: "Allez",
//                     defaultsel: "SÃ©lectionnez dans la liste...",
//                     required: "obligatoire"
//                 }
//             },
//             action: "ajax",
//             prop: "url"
//         },
//         l = [
//             ["redir", "query", "ajax", "addClass", "removeClass", "removeClass", "append", "tblfilter", "toggle"].join("." + B + " ") + "." + B, ["ajax", "toggle", "redir", "addClass", "removeClass"].join("." + U + " ") + "." + U, ["tblfilter", z].join("." + M + " ") + "." + M, ["select", "checkbox", "radio"].join("." + Q + " ") + "." + Q
//         ].join(" "),
//         Y = function(e, t, a, r) {
//             var n = e.data(t);
//             return n && !r || (n = []), n.push(a), e.data(t, n)
//         },
//         y = function(e, t) {
//             var a, r, n, i, o, s, l, d, c, u, p, f, h, b = e.get(),
//                 g = b.length,
//                 m = [];
//             for (a = 0; a !== g; a += 1) {
//                 if (o = null, n = i = "", c = (r = b[a]).firstChild, l = (d = r.childNodes).length, !c) throw "You have a markup error, There may be an empyt <li> elements in your list.";
//                 for (h = [], "A" === c.nodeName && (i = c.getAttribute("href"), n = q(c).html(), l = 1, h.push({
//                         action: "redir",
//                         url: i
//                     })), s = 1; s !== l; s += 1) {
//                     if (u = d[s], (p = q(u)).hasClass(v)) {
//                         f = u.id || N.getId(), u.id = f, i = z + "-" + f, h.push({
//                             action: "append",
//                             srctype: z,
//                             source: "#" + f
//                         });
//                         break
//                     }
//                     if ("UL" === u.nodeName) {
//                         if (t) throw "Recursive error, please check your code";
//                         o = y(p.children(), !0)
//                     }
//                     p.hasClass(w) && (n = p.html())
//                 }
//                 n || (n = c.nodeValue), r.id || (r.id = N.getId()), m.push({
//                     bind: r.id,
//                     label: n,
//                     actions: h,
//                     group: o
//                 })
//             }
//             return m
//         },
//         S = function(e) {
//             var t = e.label,
//                 a = "<option value='" + t + "'";
//             return a += d(e), a += ">" + t + "</option>"
//         },
//         d = function(e) {
//             var t = "",
//                 a = {};
//             return a.bind = e.bind || "", a.actions = e.actions || [], t += " data-" + z + "='" + JSON.stringify(a) + "'"
//         },
//         X = function(e, t, a, r, n) {
//             var i = e.label,
//                 o = N.getId(),
//                 s = " for='" + o + "'><input id='" + o + "' type='" + a + "' name='" + t + "' value='" + i + "'";
//             return s = r ? "<label class='" + a + (r ? "-inline" : "") + "'" + s : "<div class='" + a + "'><label" + s, s += d(e), n && (s += " required='required'"), s += " /> " + i + "</label>", r || (s += "</div>"), s
//         };
//     s.on(F, u + ", ." + v, function(e) {
//         var t, a, r, n, i, o, s, l = e.target,
//             d = [];
//         if (l === e.currentTarget && (a = (t = q(l)).data($)) && a.reset)
//             for (r = a.reset, q.isArray(r) ? d = r : d.push(r), i = d.length, n = 0; n !== i; n += 1)(s = (o = d[n]).action) && (!1 !== o.live && (o.live = !0), t.trigger(s + "." + B, o))
//     }), s.on("change", t + " " + e, function(e) {
//         var t, a, r, n, i, o, s, l, d = e.currentTarget,
//             c = q(d),
//             u = c.nextAll(),
//             p = q("#" + d.getAttribute("data-" + W)),
//             f = q("#" + d.getAttribute(G)),
//             h = p.data(H),
//             b = c.find(":checked", c),
//             g = c.get(0).form;
//         if (n = u.length) {
//             for (r = n; 0 !== r; r -= 1)(s = u[r]) && (-1 < (l = h.indexOf(s.id)) && h.splice(l, 1), q("#" + s.getAttribute(G)).trigger(F).trigger(P), q(s).trigger(P));
//             p.data(H, h), u.remove()
//         }
//         f.trigger(F).trigger(P), c.trigger(P), c.data(_, []);
//         var m, v, w, y, x, k, A, j, C, E = [],
//             S = [],
//             I = [];
//         for (m = p.data($), (v = f.data($)) && m && (m = q.extend({}, m, v)), b.length && b.val() && m && m.default && (a = m.default, q.isArray(a) ? E = a : E.push(a)), x = m.action, k = m.prop, K = m.actionData || {}, (a = c.data(V)) && (E = E.concat(a)), r = 0, n = b.length; r !== n; r += 1)
//             if (t = b.get(r), (w = N.getData(t, z)) && (C = w.bind, E = E.concat(w.actions), C && (y = T.getElementById(C).getAttribute("data-" + z)))) {
//                 if (y.startsWith("{") || y.startsWith("[")) {
//                     try {
//                         a = JSON.parse(y)
//                     } catch (e) {
//                         q.error("Bad JSON object " + y)
//                     }
//                     q.isArray(a) || (a = [a])
//                 } else(a = {}).action = x, a[k] = y, a = [a = q.extend(!0, {}, K, a)];
//                 E = E.concat(a)
//             }
//         if (!E.length) return !0;
//         for (r = 0, n = E.length; r !== n; r += 1)(o = (i = E[r]).target) && o !== C ? I.push(i) : S.push(i);
//         for (A = m.base || {}, j = I.length, r = 0, n = S.length; r !== n; r += 1)(i = q.extend({}, A, S[r])).origin = f.get(0), i.provEvt = d, i.$selElm = b, i.form = g, j && (i.actions = I), p.trigger(i.action + "." + B, i);
//         return !0
//     }), s.on("submit", t + " form", function(e) {
//         var t, a, r, n, i, o, s, l, d, c, u, p, f, h, b, g = e.currentTarget,
//             m = q(g),
//             v = m.data(H),
//             w = m.data(J) || [],
//             y = v ? v.length : 0,
//             x = [],
//             k = [],
//             A = !1;
//         for (y && (n = (r = q("#" + v[y - 1])).data(H), q("#" + n[n.length - 1]).trigger(P), r.trigger(P)), a = 0; a !== y; a += 1)
//             for (c = (i = (r = q("#" + v[a])).data(H)).length, d = 0; d !== c; d += 1) {
//                 if (o = q("#" + i[d]), s = q("#" + o.data(W)), k.push(s), l = s.data($), !(h = o.data(_)) && l.defaultIfNone) {
//                     for (u = 0, p = (h = l.defaultIfNone).length; u !== p; u += 1)(f = h[u]).origin = s.get(0), f.$selElm = s.prev().find("input, select").eq(0), f.provEvt = f.$selElm.get(0), f.form = g, s.trigger(f.action + "." + B, f);
//                     h = o.data(_)
//                 }
//                 if (h)
//                     for (u = 0, p = h.length; u !== p; u += 1)(f = h[u]).form = g, r.trigger(f.action + "." + U, f), x.push({
//                         $elm: r,
//                         data: f
//                     }), A = A || f.preventSubmit, b = f.provEvt
//             }
//         if (!A) {
//             for (m.find(L).removeAttr("name"), y = w.length, a = 0; a !== y; a += 1) q(w[a]).remove();
//             var j, C, E, S, I, T;
//             if (w = [], (j = m.attr("action")) && 0 < (C = j.indexOf("?"))) {
//                 for (y = (T = j.substring(C + 1).split("&")).length, a = 0; a !== y; a += 1) 0 < (S = E = T[a]).indexOf("=") && (S = (I = E.split("=", 2))[0], E = I[1]), t = q("<input type='hidden' name='" + S + "' value='" + E + "' />"), m.append(t), w.push(t.get(0));
//                 m.data(J, w)
//             }
//         }
//         for (y = k.length, a = 0; a !== y; a += 1)(l = (s = k[a]).data($)).action && x.push({
//             $elm: s,
//             data: l
//         });
//         for (y = x.length, a = 0; a !== y; a += 1)(f = x[a]).data.lastProvEvt = b, f.$elm.trigger(f.data.action + "." + R, f.data);
//         if (A) return e.preventDefault(), e.stopPropagation ? e.stopImmediatePropagation() : e.cancelBubble = !0, !1
//     }), s.on("keyup", t + " select", function(e) {
//         if (-1 !== navigator.userAgent.indexOf("Gecko")) return e.keyCode && (1 === e.keyCode || 9 === e.keyCode || 16 === e.keyCode || e.altKey || e.ctrlKey) || q(e.target).trigger("change"), !0
//     }), s.on(l, u, function(e, t) {
//         var a = e.type;
//         switch (e.namespace) {
//             case M:
//                 switch (a) {
//                     case z:
//                         ! function(e, t) {
//                             if (e.namespace === M) {
//                                 var a, r, n, i, o, s, l, d = e.target,
//                                     c = q(d),
//                                     u = q(t.source),
//                                     p = u.get(0),
//                                     f = t.lblselector || "." + w,
//                                     h = t.itmselector || "ul:first() > li";
//                                 u.hasClass(v) && (a = N.getData(u, z), u.data($, a), t = q.extend({}, t, a)), s = t.actions || [], l = t.renderas ? t.renderas : "select", p.id || (p.id = N.getId()), (n = u.children().first()).hasClass(b) ? (i = n.html(), h = "." + b + " + " + h) : (i = (r = n.find(f)).length ? r.html() : u.find("> p").html(), f = null), o = y(u.find(h)), t.outputctnrid || (t.outputctnrid = t.provEvt.parentElement.id), c.trigger(l + "." + Q, {
//                                     actions: s,
//                                     source: p,
//                                     attributes: t.attributes,
//                                     outputctnrid: t.outputctnrid,
//                                     label: i,
//                                     lblselector: f,
//                                     defaultselectedlabel: t.defaultselectedlabel,
//                                     required: !t.isoptional,
//                                     noreqlabel: t.noreqlabel,
//                                     items: o,
//                                     inline: t.inline
//                                 })
//                             }
//                         }(e, t);
//                         break;
//                     case "tblfilter":
//                         ! function(e, t) {
//                             if (e.namespace === M) {
//                                 var a, r, n, i, o, s, l, d, c, u, p, f, h, b = t.column,
//                                     g = t.csvextract,
//                                     m = t.source,
//                                     v = q(m),
//                                     w = [],
//                                     y = t.label,
//                                     x = t.defaultselectedlabel,
//                                     k = t.lblselector,
//                                     A = t.fltrseq ? t.fltrseq : [],
//                                     j = t.limit ? t.limit : 10;
//                                 if (!v.hasClass("wb-tables-inited")) return v.one("wb-ready.wb-tables", function() {
//                                     q(e.target).trigger("tblfilter." + M, t)
//                                 });
//                                 if ((r = v.dataTable({
//                                         retrieve: !0
//                                     }).api()).rows({
//                                         search: "applied"
//                                     }).data().length <= j) return;
//                                 if (h = t.renderas ? t.renderas : "select", !b && A.length) {
//                                     if (!(c = A.shift()).column) throw "Column is undefined in the filter sequence";
//                                     b = c.column, g = c.csvextract, x = c.defaultselectedlabel, y = c.label, k = c.lblselector
//                                 }
//                                 if (a = r.column(b, {
//                                         search: "applied"
//                                     }), g)
//                                     for (o = 0, s = (n = a.data()).length; o !== s; o += 1) w = w.concat(n[o].split(","));
//                                 else
//                                     for (o = 0, s = (n = a.nodes()).length; o !== s; o += 1)
//                                         for (l = 0, d = (i = q(n[o]).find("li")).length; l !== d; l += 1) w.push(q(i[l]).text());
//                                 w = w.sort().filter(function(e, t, a) {
//                                     return !t || e !== a[t - 1]
//                                 });
//                                 var C = e.target,
//                                     E = q(C),
//                                     S = [],
//                                     I = t.actions ? t.actions : [];
//                                 for (A.length && (f = {
//                                         action: "append",
//                                         srctype: "tblfilter",
//                                         source: m,
//                                         renderas: (u = A[0]).renderas ? u.renderas : h,
//                                         fltrseq: A,
//                                         limit: j
//                                     }), o = 0, s = w.length; o !== s; o += 1) p = {
//                                     label: c = w[o],
//                                     actions: [{
//                                         action: "tblfilter",
//                                         source: m,
//                                         column: b,
//                                         value: c
//                                     }]
//                                 }, f && p.actions.push(f), S.push(p);
//                                 y || (y = a.header().textContent), t.outputctnrid || (t.outputctnrid = t.provEvt.parentElement.id), E.trigger(h + "." + Q, {
//                                     actions: I,
//                                     source: v.get(0),
//                                     outputctnrid: t.outputctnrid,
//                                     label: y,
//                                     defaultselectedlabel: x,
//                                     lblselector: k,
//                                     items: S,
//                                     inline: t.inline
//                                 })
//                             }
//                         }(e, t)
//                 }
//                 break;
//             case Q:
//                 switch (a) {
//                     case "select":
//                         ! function(e, t) {
//                             var a, r, n, i, o, s, l, d, c, u = t.outputctnrid,
//                                 p = q("#" + u),
//                                 f = t.actions,
//                                 h = t.lblselector,
//                                 b = !!t.required,
//                                 g = !t.noreqlabel,
//                                 m = t.items,
//                                 v = e.target,
//                                 w = q(v),
//                                 y = t.source,
//                                 x = t.attributes,
//                                 k = w.data($).i18n,
//                                 A = N.getId(),
//                                 j = "<label for='" + A + "'",
//                                 C = "</span>",
//                                 E = t.defaultselectedlabel ? t.defaultselectedlabel : k.defaultsel;
//                             if (b && g && (j += " class='required'", C += " <strong class='required'>(" + k.required + ")</strong>"), j += "><span class='field-name'>", C += "</label>", h ? (r = (a = q("<div>" + t.label + "</div>")).find(h)).html(j + r.html() + C) : a = q(j + t.label + C), n = "<select id='" + A + "' name='" + O + A + "' class='full-width form-control mrgn-bttm-md " + D + "' data-" + W + "='" + v.id + "' " + G + "='" + y.id + "'", b && (n += " required"), x && "object" == typeof x)
//                                 for (o in x) x.hasOwnProperty(o) && (n += " " + o + "='" + x[o] + "'");
//                             for (n += "><option value=''>" + E + "</option>", o = 0, s = m.length; o !== s; o += 1)
//                                 if ((c = m[o]).group) {
//                                     for (n += "<optgroup label='" + c.label + "'>", d = c.group.length, l = 0; l !== d; l += 1) n += S(c.group[l]);
//                                     n += "</optgroup>"
//                                 } else n += S(c);
//                             i = q(n += "</select>"), p.append(a).append(i), f && 0 < f.length && i.data(V, f), Y(w, H, A)
//                         }(e, t);
//                         break;
//                     case "checkbox":
//                         t.typeRadCheck = "checkbox", i(e, t);
//                         break;
//                     case "radio":
//                         t.typeRadCheck = "radio", i(e, t)
//                 }
//                 break;
//             case B:
//                 switch (a) {
//                     case "append":
//                         ! function(e, t) {
//                             if (e.namespace === B) {
//                                 var a = t.srctype ? t.srctype : z;
//                                 if (t.container = t.provEvt.parentNode.id, !t.source) throw "A source is required to append a field flow control.";
//                                 q(e.currentTarget).trigger(a + "." + M, t)
//                             }
//                         }(e, t);
//                         break;
//                     case "redir":
//                         Y(q(t.provEvt), _, t, !0);
//                         break;
//                     case "ajax":
//                         ! function(e, t) {
//                             var a, r = t.provEvt;
//                             t.live ? (t.container || (a = q("<div></div>"), q(r.parentNode).append(a), t.container = a.get(0)), q(e.target).trigger("ajax." + U, t)) : (t.preventSubmit = !0, Y(q(r), _, t))
//                         }(e, t);
//                         break;
//                     case "tblfilter":
//                         ! function(e, t) {
//                             if (e.namespace === B) {
//                                 var a, r = t.source,
//                                     n = q(r).dataTable({
//                                         retrieve: !0
//                                     }).api(),
//                                     i = t.column,
//                                     o = parseInt(i, 10),
//                                     s = !!t.regex,
//                                     l = !t.smart || !!t.smart,
//                                     d = !t.caseinsen || !!t.caseinsen;
//                                 i = !0 === o ? o : i, (a = n.column(i)).search(t.value, s, l, d).draw(), q(t.provEvt).one(P, function() {
//                                     a.search("").draw()
//                                 })
//                             }
//                         }(e, t);
//                         break;
//                     case "toggle":
//                         t.live ? n(e, t) : (t.preventSubmit = !0, Y(q(t.provEvt), _, t));
//                         break;
//                     case "addClass":
//                         if (!t.source || !t.class) return;
//                         t.live ? q(t.source).addClass(t.class) : (t.preventSubmit = !0, Y(q(t.provEvt), _, t));
//                         break;
//                     case "removeClass":
//                         if (!t.source || !t.class) return;
//                         t.live ? q(t.source).removeClass(t.class) : (t.preventSubmit = !0, Y(q(t.provEvt), _, t));
//                         break;
//                     case "query":
//                         r(0, t)
//                 }
//                 break;
//             case U:
//                 switch (a) {
//                     case "redir":
//                         ! function(e, t) {
//                             var a = t.form,
//                                 r = t.url;
//                             r && a.setAttribute("action", r)
//                         }(0, t);
//                         break;
//                     case "ajax":
//                         ! function(e, t) {
//                             var a, r, n, i = t.clean;
//                             t.container ? a = q(t.container) : (r = N.getId(), a = q("<div id='" + r + "'></div>"), q(t.form).append(a), i = "#" + r), i && q(t.origin).one(P, function() {
//                                 q(i).empty()
//                             }), t.trigger && a.attr("data-trigger-wet", "true"), n = t.type ? t.type : "replace", a.attr("data-ajax-" + n, t.url), a.one("wb-contentupdated", function(e, t) {
//                                 var a = e.currentTarget,
//                                     r = a.getAttribute("data-trigger-wet");
//                                 a.removeAttribute("data-ajax-" + t["ajax-type"]), r && (q(a).find(N.allSelectors).addClass("wb-init").filter(":not(#" + a.id + " .wb-init .wb-init)").trigger("timerpoke.wb"), a.removeAttribute("data-trigger-wet"))
//                             }), a.trigger("wb-update.wb-data-ajax")
//                         }(0, t);
//                         break;
//                     case "toggle":
//                         n(e, t);
//                         break;
//                     case "addClass":
//                         q(t.source).addClass(t.class);
//                         break;
//                     case "removeClass":
//                         q(t.source).removeClass(t.class);
//                         break;
//                     case "query":
//                         r(0, t)
//                 }
//         }
//     }), s.on("timerpoke.wb " + a, u, function(e) {
//         switch (e.type) {
//             case "timerpoke":
//             case "wb-init":
//                 ! function(e) {
//                     var t, a, r, n, i, o = N.init(e, z, u);
//                     if (o) {
//                         t = q(o), a = o.id, h.i18n[N.lang] && (h.i18n = h.i18n[N.lang]), (r = N.getData(t, z)) && r.i18n && (r.i18n = q.extend({}, h.i18n, r.i18n)), (n = q.extend({}, h, r)).defaultIfNone && !q.isArray(n.defaultIfNone) && (n.defaultIfNone = [n.defaultIfNone]), t.data($, n), i = n.i18n, String.prototype.startsWith || (String.prototype.startsWith = function(e, t) {
//                             return t = t || 0, this.substr(t, e.length) === e
//                         });
//                         var s, l, d, c = N.getId();
//                         if (n.noForm) {
//                             for (s = "<div class='mrgn-tp-md'><div id='" + c + "'></div></div>", l = o.parentElement;
//                                 "FORM" !== l.nodeName;) l = l.parentElement;
//                             q(l.parentElement).addClass(p)
//                         } else s = (s = "<div class='wb-frmvld " + p + "'><form><div id='" + c + "'>") + '</div><input type="submit" value="' + i.btn + '" class="btn btn-primary mrgn-bttm-md" /> </form></div>';
//                         t.addClass("hidden"), s = q(s), t.after(s), n.noForm || (l = s.find("form"), s.trigger("wb-init.wb-frmvld")), d = q(l), Y(d, H, a), n.outputctnrid || (n.outputctnrid = c), n.source || (n.source = o), n.srctype || (n.srctype = z), n.inline = !!n.inline, t.trigger(n.srctype + "." + M, n), n.unhideelm && q(n.unhideelm).removeClass("hidden"), n.hideelm && q(n.hideelm).addClass("hidden"), N.ready(t, z), n.ext && (n.form = d.get(0), t.trigger(n.ext + "." + f, n))
//                     }
//                 }(e)
//         }
//         return !0
//     }), N.add(u)
// }(jQuery, document, wb),! function(a, b) {
//     "use strict";
//     var c = b.doc,
//         d = "wb-actionmng",
//         e = "." + d,
//         f = "[data-" + d + "]",
//         g = d + "Rn",
//         h = "wb-init." + d,
//         i = d + e,
//         j = {},
//         k = {},
//         l = {},
//         m = ["patch", "ajax", "addClass", "removeClass", "tblfilter", "run"].join("." + i + " ") + "." + i,
//         n = function(c) {
//             var f, g, h, i, j, k, m = b.init(c, d, e);
//             if (m) {
//                 if (f = a(m), g = b.getData(f, d))
//                     for (a.isArray(g) || (g = [g]), i = g.length, h = 0; h !== i; h += 1) j = g[h], (k = j.trggroup) && j.action && o(k, l, j);
//                 b.ready(f, d)
//             }
//         },
//         o = function(a, b, c) {
//             b[a] || (b[a] = []), b[a].push(c)
//         },
//         p = function(a, b, c) {
//             var d, e, f;
//             for (d = c[b]; e = d.shift();)(f = e.action) && (a.trigger(f + "." + i, e), delete e.action)
//         },
//         q = function(b, c) {
//             var d = c.source,
//                 e = c.patches,
//                 f = !!c.cumulative;
//             e && (a.isArray(e) || (e = [e]), a(d).trigger({
//                 type: "patches.wb-jsonmanager",
//                 patches: e,
//                 fpath: c.fpath,
//                 filter: c.filter || [],
//                 filternot: c.filternot || [],
//                 cumulative: f
//             }))
//         },
//         r = function(c, d) {
//             var e, f, g;
//             d.container ? e = a(d.container) : (f = b.getId(), e = a("<div id='" + f + "'></div>"), a(c.target).after(e)), d.trigger && e.attr("data-trigger-wet", "true"), g = d.type ? d.type : "replace", e.attr("data-ajax-" + g, d.url), e.one("wb-contentupdated", function(c, d) {
//                 var e = c.currentTarget,
//                     f = e.getAttribute("data-trigger-wet");
//                 e.removeAttribute("data-ajax-" + d["ajax-type"]), f && (a(e).find(b.allSelectors).addClass("wb-init").filter(":not(#" + e.id + " .wb-init .wb-init)").trigger("timerpoke.wb"), e.removeAttribute("data-trigger-wet"))
//             }), e.trigger("wb-update.wb-data-ajax")
//         },
//         s = function(b, c) {
//             var d = a(c.source || b.target);
//             c.class && d.addClass(c.class)
//         },
//         t = function(b, c) {
//             var d = a(c.source || b.target);
//             c.class && d.removeClass(c.class)
//         },
//         u = function(b, c) {
//             var d, e = b.target,
//                 f = a(c.source || e),
//                 g = c.column,
//                 h = parseInt(g, 10),
//                 i = !!c.regex,
//                 j = !c.smart || !!c.smart,
//                 k = !c.caseinsen || !!c.caseinsen;
//             if ("TABLE" !== f.get(0).nodeName) throw "Table filtering can only applied on table";
//             d = f.dataTable({
//                 retrieve: !0
//             }).api(), g = !0 === h ? h : g, d.column(g).search(c.value, i, j, k).draw()
//         },
//         v = function(b, c) {
//             var d, e, f, h, j = b.target,
//                 k = a(j),
//                 m = l[c.trggroup];
//             if (m && !k.hasClass(g)) {
//                 for (k.addClass(g), e = m.length, d = 0; d !== e; d += 1) f = m[d], (h = f.action) && k.trigger(h + "." + i, f);
//                 k.removeClass(g)
//             }
//         };
//     c.on("do." + i, function(b) {
//         var c, e, f, g, h, m, n, q = b.element || b.target,
//             r = q.id,
//             s = b.actions || [];
//         if ((q === b.target || b.currentTarget === b.target) && -1 === q.className.indexOf(d)) {
//             for (a.isArray(s) || (s = [s]), f = s.length, f && (c = a(q), c.addClass(d)), r && j[r] && p(c, r, j), e = 0; e !== f; e += 1) g = s[e], (h = g.action) && (m = g.target, m ? (g.trgbefore ? o(m, j, g) : o(m, k, g), (n = g.trggroup) && o(n, l, g)) : c.trigger(h + "." + i, g));
//             r && k[r] && p(c, r, k), a(b.target).removeClass(d)
//         }
//     }), c.on("clean." + i, function(a) {
//         var b, c, d = a.element || a.target,
//             e = a.trggroup;
//         if ((d === a.target || a.currentTarget === a.target) && e && l[e])
//             for (b = l[e]; c = b.shift();) delete c.action
//     }), c.on(m, e, function(a, b) {
//         var c = a.type;
//         if (i === a.namespace) switch (c) {
//             case "run":
//                 v(a, b);
//                 break;
//             case "tblfilter":
//                 u(a, b);
//                 break;
//             case "addClass":
//                 s(a, b);
//                 break;
//             case "removeClass":
//                 t(a, b);
//                 break;
//             case "ajax":
//                 r(a, b);
//                 break;
//             case "patch":
//                 q(a, b)
//         }
//     }), c.on("timerpoke.wb " + h, f, n), b.add(f)
// }(jQuery, wb),
// function(a, b, c) {
//     "use strict";
//     var d, e = "wb-data-json",
//         f = "wb-json",
//         g = ["[data-json-after]", "[data-json-append]", "[data-json-before]", "[data-json-prepend]", "[data-json-replace]", "[data-json-replacewith]", "[data-" + f + "]"],
//         h = ["after", "append", "before", "prepend", "val"],
//         i = /(href|src|data-*|pattern|min|max|step|low|high)/,
//         j = /(checked|selected|disabled|required|readonly|multiple)/,
//         k = g.length,
//         l = g.join(","),
//         m = "wb-init." + e,
//         n = "wb-update." + e,
//         o = "wb-contentupdated",
//         p = e + "-queue",
//         q = c.doc,
//         r = function(b) {
//             var d, g = c.init(b, e, l);
//             if (g) {
//                 var h, i, j, k, m, n = ["before", "replace", "replacewith", "after", "append", "prepend"],
//                     o = n.length,
//                     q = [];
//                 for (d = a(g), j = 0; j !== o; j += 1) h = n[j], null !== (m = g.getAttribute("data-json-" + h)) && q.push({
//                     type: h,
//                     url: m
//                 });
//                 if (c.ready(d, e), (i = c.getData(d, f)) && i.url) q.push(i);
//                 else if (i && a.isArray(i))
//                     for (o = i.length, j = 0; j !== o; j += 1) q.push(i[j]);
//                 for (d.data(p, q), o = q.length, j = 0; j !== o; j += 1) k = q[j], s(g, k.url, j, k.nocache, k.nocachekey)
//             }
//         },
//         s = function(d, f, g, h, i) {
//             var j, k = a(d),
//                 l = {
//                     url: f,
//                     refId: g,
//                     nocache: h,
//                     nocachekey: i
//                 },
//                 m = b[e];
//             !m || "http" !== f.substr(0, 4) && "//" !== f.substr(0, 2) || (j = c.getUrlParts(f), c.pageUrlParts.protocol === j.protocol && c.pageUrlParts.host === j.host || Modernizr.cors && !m.forceCorsFallback || "function" == typeof m.corsFallback && (l.dataType = "jsonp", l.jsonp = "callback", l = m.corsFallback(l))), k.trigger({
//                 type: "json-fetch.wb",
//                 fetch: l
//             })
//         },
//         t = function(b) {
//             var d, f = b.target,
//                 g = a(f),
//                 k = g.data(p),
//                 l = b.fetch,
//                 m = k[l.refId],
//                 n = m.type,
//                 q = m.prop || m.attr,
//                 r = m.showempty,
//                 s = l.response,
//                 t = typeof s;
//             if (r || "undefined" !== t) {
//                 if (r && "undefined" === t && (s = ""), d = jQuery.ajaxSettings.cache, jQuery.ajaxSettings.cache = !0, n)
//                     if ("replace" === n) g.html(s);
//                     else if ("replacewith" === n) g.replaceWith(s);
//                 else if ("addclass" === n) g.addClass(s);
//                 else if ("removeclass" === n) g.removeClass(s);
//                 else if ("prop" === n && q && j.test(q)) g.prop(q, s);
//                 else if ("attr" === n && q && i.test(q)) g.attr(q, s);
//                 else {
//                     if ("function" != typeof g[n] || -1 === h.indexOf(n)) throw e + " do not support type: " + n;
//                     g[n](s)
//                 } else n = "template", u(f, m, s), m.trigger && g.find(c.allSelectors).addClass("wb-init").filter(":not(#" + f.id + " .wb-init .wb-init)").trigger("timerpoke.wb");
//                 jQuery.ajaxSettings.cache = d, g.trigger(o, {
//                     "json-type": n,
//                     content: s
//                 })
//             }
//         },
//         u = function(b, d, e) {
//             var f, g, h, i, j, k, l, m, n, o, p, q, r, s, t = d.mapping || [{}],
//                 w = d.filter || [],
//                 x = d.filternot || [],
//                 y = d.queryall,
//                 z = d.tobeclone,
//                 A = b.className,
//                 B = b,
//                 C = d.source ? document.querySelector(d.source) : b.querySelector("template");
//             if (a.isArray(e) || (e = "object" != typeof e ? [e] : a.map(e, function(b, c) {
//                     return "object" != typeof b || a.isArray(b) ? b = {
//                         "@id": c,
//                         "@value": b
//                     } : b["@id"] || (b["@id"] = c), [b]
//                 })), h = e.length, a.isArray(t) || (t = [t]), f = t.length, "TABLE" === b.tagName && t && -1 !== A.indexOf("wb-tables-inited") && "string" == typeof t[0]) {
//                 for (s = a(b).dataTable({
//                         retrieve: !0
//                     }).api(), g = 0; g < h; g += 1)
//                     if (i = e[g], v(i, w, x)) {
//                         for (m = "/" + g, r = [], j = 0; j < f; j += 1) r.push(jsonpointer.get(e, m + t[j]));
//                         s.row.add(r)
//                     }
//                 return void s.draw()
//             }
//             if (C)
//                 for (C.content || c.tmplPolyfill(C), d.appendto && (B = a(d.appendto).get(0)), g = 0; g < h; g += 1)
//                     if (i = e[g], v(i, w, x)) {
//                         for (m = "/" + g, n = z ? C.content.querySelector(z).cloneNode(!0) : C.content.cloneNode(!0), y && (o = n.querySelectorAll(y)), j = 0; j < f || 0 === j; j += 1) k = t[j], p = o ? o[j] : k.selector ? n.querySelector(k.selector) : n, l = k.attr, l && (p.hasAttribute(l) || p.setAttribute(l, ""), p = p.getAttributeNode(l)), r = "string" == typeof i ? i : "string" == typeof k ? jsonpointer.get(e, m + k) : jsonpointer.get(e, m + k.value), k.placeholder && (q = p.textContent || "", r = q.replace(k.placeholder, r)), a.isArray(r) ? u(p, k, r) : k.isHTML ? p.innerHTML = r : p.textContent = r;
//                         B.appendChild(n)
//                     }
//         },
//         v = function(a, b, c) {
//             var d, e, f, g = b.length,
//                 h = c.length,
//                 i = !1;
//             if (g || h) {
//                 for (d = 0; d < g; d += 1)
//                     if (e = b[d], f = w(jsonpointer.get(a, e.path), e.value), e.optional) i = i || f;
//                     else {
//                         if (!f) return !1;
//                         i = !0
//                     }
//                 if (g && !i) return !1;
//                 for (d = 0; d < h; d += 1)
//                     if (e = c[d], (f = w(jsonpointer.get(a, e.path), e.value)) && !e.optional || f && e.optional) return !1
//             }
//             return !0
//         },
//         w = function(b, c) {
//             switch (typeof b) {
//                 case "undefined":
//                     return !1;
//                 case "boolean":
//                 case "string":
//                 case "number":
//                     return b === c;
//                 case "object":
//                     if (null === b) return null === c;
//                     if (a.isArray(b)) {
//                         if (a.isArray(c) || b.length !== c.length) return !1;
//                         for (var d = 0, e = b.length; d < e; d++)
//                             if (!w(b[d], c[d])) return !1;
//                         return !0
//                     }
//                     var f = x(c),
//                         g = f.length;
//                     if (x(b).length !== g) return !1;
//                     for (var d = 0; d < g; d++)
//                         if (!w(b[d], c[d])) return !1;
//                     return !0;
//                 default:
//                     return !1
//             }
//         },
//         x = function(b) {
//             if (a.isArray(b)) {
//                 for (var c = new Array(b.length), d = 0; d < c.length; d++) c[d] = "" + d;
//                 return c
//             }
//             if (Object.keys) return Object.keys(b);
//             var c = [];
//             for (var e in b) b.hasOwnProperty(e) && c.push(e);
//             return c
//         },
//         y = function(b) {
//             var c = b.target,
//                 d = a(c),
//                 e = d.data(p),
//                 f = e.length,
//                 g = b["wb-json"];
//             if (!g.url || !g.type && !g.source) throw "Data JSON update not configured properly";
//             e.push(g), d.data(p, e), s(c, g.url, f)
//         };
//     q.on("json-failed.wb", l, function() {
//         throw "Bad JSON Fetched from url in " + e
//     }), Modernizr.load({
//         test: "content" in document.createElement("template"),
//         nope: "site!deps/template" + c.getMode() + ".js"
//     }), q.on("timerpoke.wb " + m + " " + n + " json-fetched.wb", l, function(a) {
//         if (a.currentTarget === a.target) switch (a.type) {
//             case "timerpoke":
//             case "wb-init":
//                 r(a);
//                 break;
//             case "wb-update":
//                 y(a);
//                 break;
//             default:
//                 t(a)
//         }
//         return !0
//     });
//     for (d = 0; d !== k; d += 1) c.add(g[d])
// }(jQuery, window, wb),
// function(a, b, c) {
//     "use strict";
//     var d = "wb-template",
//         e = "template",
//         f = "wb-init." + d,
//         g = c.doc,
//         h = function(a) {
//             if (!a.content) {
//                 var c, d, e = a;
//                 for (c = e.childNodes, d = b.createDocumentFragment(); c[0];) d.appendChild(c[0]);
//                 e.content = d
//             }
//         },
//         i = function(b) {
//             var f = c.init(b, d, e);
//             f && (h(f), c.ready(a(f), d))
//         };
//     c.tmplPolyfill = h, g.on("timerpoke.wb " + f, e, i), c.add(e)
// }(jQuery, document, wb),
// function(a, b, c) {
//     "use strict";
//     var d = "wb-doaction",
//         e = "a[data-" + d + "],button[data-" + d + "]",
//         f = "do.wb-actionmng",
//         g = c.doc;
//     g.on("click", e, function(b) {
//         var h = b.target,
//             i = a(h);
//         if (b.currentTarget !== b.target && (i = i.parentsUntil("main", e), h = i[0]), "BUTTON" === h.nodeName || "A" === h.nodeName) return c.isReady ? i.trigger({
//             type: f,
//             actions: c.getData(i, d)
//         }) : g.one("wb-ready.wb", function() {
//             i.trigger({
//                 type: f,
//                 actions: c.getData(i, d)
//             })
//         }), !1
//     })












// }(jQuery, window, wb),
// function(a, b, c) {
//     "use strict";
//     var componentName = "wb-fieldflow",
//     	selector = "." + componentName,
//     	formComponent = componentName + "-form",
//     	subComponentName = componentName + "-sub",
//     	crtlSelectClass = componentName + "-init",
//     	crtlSelectSelector = "." + crtlSelectClass,
//     	basenameInput = componentName + wb.getId(),
//     	basenameInputSelector = "[name^=" + basenameInput + "]",
//     	labelClass = componentName + "-label",
//     	headerClass = componentName + "-header",
//     	selectorForm = "." + formComponent,
//     	initEvent = "wb-init" + selector,
//     	drawEvent = "draw" + selector,
//     	actionEvent = "action" + selector,
//     	submitEvent = "submit" + selector,
//     	submitedEvent = "submited" + selector,
//     	readyEvent = "ready" + selector,
//     	cleanEvent = "clean" + selector,
//     	resetActionEvent = "reset" + selector,
//     	createCtrlEvent = "createctrl" + selector,
//     	registerJQData = componentName + "-register", // Data that contain all the component registered (to the form element and component), used for executing action upon submit
//     	registerHdnFld = componentName + "-hdnfld",
//     	configData = componentName + "-config",
//     	pushJQData =  componentName + "-push",
//     	submitJQData =  componentName + "-submit", // List of action to perform upon form submission
//     	actionData =  componentName + "-action", // temp for code transition
//     	originData =  componentName + "-origin", // To carry the plugin origin ID, any implementation of "createCtrlEvent" must set that option.
//     	sourceDataAttr =  "data-" + componentName + "-source",
//     	flagOptValueData =  componentName + "-flagoptvalue",
//     	$document = wb.doc,
//     	defaults = {
//     		toggle: {
//     			stateOn: "visible", // For toggle plugin
//     			stateOff: "hidden"  // For toggle plugin
//     		},
//     		i18n:
//     		{
//     			"en": {
//     				btn: "Continue", // Action button
//     				defaultsel: "Make your selection...", // text use for the first empty select
//     				required: "required"// text for the required label
//     			},
//     			"fr": {
//     				btn: "Allez",
//     				defaultsel: "Sélectionnez dans la liste...", // text use for the first empty select
//     				required: "obligatoire" // text for the required label
//     			}
//     		},
//     		action: "ajax",
//     		prop: "url"
//     	},
//     	fieldflowActionsEvents = [
//     		[
//     			"redir",
//     			"query",
//     			"ajax",
//     			"addClass",
//     			"removeClass",
//     			"removeClass",
//     			"append",
//     			"tblfilter",
//     			"toggle"
//     		].join( "." + actionEvent + " " ) + "." + actionEvent,
//     		[
//     			"ajax",
//     			"toggle",
//     			"redir",
//     			"addClass",
//     			"removeClass"
//     		].join( "." + submitEvent + " " ) + "." + submitEvent,
//     		[
//     			"tblfilter",
//     			componentName
//     		].join( "." + drawEvent + " " ) + "." + drawEvent,
//     		[
//     			"select",
//     			"checkbox",
//     			"radio"
//     		].join( "." + createCtrlEvent + " " ) + "." + createCtrlEvent
//     	].join( " " ),

//     	/**
//     	* @method init
//     	* @param {jQuery Event} event Event that triggered the function call
//     	*/
//     	init = function( event ) {
//     		var elm = wb.init( event, componentName, selector ),
//     			$elm, elmId,
//     			wbDataElm,
//     			config,
//     			i18n;

//     		if ( elm ) {
//     			$elm = $( elm );
//     			elmId = elm.id;

//     			// Set default i18n information
//     			if ( defaults.i18n[ wb.lang ] ) {
//     				defaults.i18n = defaults.i18n[ wb.lang ];
//     			}

//     			// Extend this data with the contextual default
//     			wbDataElm = wb.getData( $elm, componentName );
//     			if ( wbDataElm && wbDataElm.i18n ) {
//     				wbDataElm.i18n = $.extend( {}, defaults.i18n, wbDataElm.i18n );
//     			}
//     			config = $.extend( {}, defaults, wbDataElm );

//     			if ( config.defaultIfNone && !$.isArray( config.defaultIfNone ) ) {
//     				config.defaultIfNone = [ config.defaultIfNone ];
//     			}

//     			// Set the data to the component, if other event need to have access to it.
//     			$elm.data( configData, config );
//     			i18n = config.i18n;

//     			// Add startWith function (ref: https://developer.mozilla.org/fr/docs/Web/JavaScript/Reference/Objets_globaux/String/startsWith)
//     			if ( !String.prototype.startsWith ) {
//     				String.prototype.startsWith = function( searchString, position ) {
//     					position = position || 0;
//     					return this.substr( position, searchString.length ) === searchString;
//     				};
//     			}

//     			// Transform the list into a select, use the first paragrap content for the label, and extract for i18n the name of the button action.
//     			var bodyID = wb.getId(),
//     				stdOut,
//     				formElm, $form;

//     			if ( config.noForm ) {
//     				stdOut = "<div class='mrgn-tp-md'><div id='" + bodyID + "'></div></div>";

//     				// Need to add the class="formComponent" to the div that wrap the form element.
//     				formElm = elm.parentElement;
//     				while ( formElm.nodeName !== "FORM" ) {
//     					formElm = formElm.parentElement;
//     				}
//     				$( formElm.parentElement ).addClass( formComponent );
//     			} else {
//     				stdOut = "<div class='wb-frmvld " + formComponent + "'><form><div id='" + bodyID + "'>";
//     				stdOut = stdOut + "</div><input type=\"submit\" value=\"" + i18n.btn + "\" class=\"btn btn-primary mrgn-bttm-md\" /> </form></div>";
//     			}
//     			$elm.addClass( "hidden" );
//     			stdOut = $( stdOut );
//     			$elm.after( stdOut );

//     			if ( !config.noForm ) {
//     				formElm = stdOut.find( "form" );
//     				stdOut.trigger( "wb-init.wb-frmvld" );
//     			}

//     			$form = $( formElm );

//     			// Register this plugin within the form, this is to manage form submission
//     			pushData( $form, registerJQData, elmId );

//     			if ( !config.outputctnrid ) { // Output container ID
//     				config.outputctnrid = bodyID;
//     			}

//     			if ( !config.source ) {
//     				config.source = elm; // We assume th container have the source
//     			}

//     			if ( !config.srctype ) {
//     				config.srctype = componentName;
//     			}

//     			config.inline = !!config.inline;

//     			// Trigger the drop down loading
//     			$elm.trigger( config.srctype + "." + drawEvent, config );

//     			// Do requested DOM manipulation
//     			if ( config.unhideelm ) {
//     				$( config.unhideelm ).removeClass( "hidden" );
//     			}
//     			if ( config.hideelm ) {
//     				$( config.hideelm ).addClass( "hidden" );
//     			}

//     			// Identify that initialization has completed
//     			wb.ready( $elm, componentName );

//     			if ( config.ext ) {
//     				config.form = $form.get( 0 );
//     				$elm.trigger( config.ext + "." + readyEvent, config );
//     			}
//     		}
//     	},
//     	pushData = function( $elm, prop, data, reset ) {
//     		var dtCache = $elm.data( prop );
//     		if ( !dtCache || reset ) {
//     			dtCache = [];
//     		}
//     		dtCache.push( data );
//     		return $elm.data( prop, dtCache );
//     	},
//     	subRedir = function( event, data ) {

//     		var form = data.form,
//     			url = data.url;

//     		if ( url ) {
//     			form.setAttribute( "action", url );
//     		}
//     	},
//     	actQuery = function( event, data ) {
//     		var $selectElm = data.$selElm,
//     			fieldName = data.name,
//     			fieldValue = data.value;

//     		if ( fieldName ) {
//     			data.provEvt.setAttribute( "name", fieldName );
//     		}
//     		if ( fieldValue ) {
//     			$selectElm.val( fieldValue );
//     		}

//     		// Add a flag to know the option value was inserted
//     		$selectElm.attr( "data-" + flagOptValueData, flagOptValueData );
//     	},
//     	actAjax = function( event, data ) {
//     		var provEvt = data.provEvt,
//     			$container;

//     		if ( !data.live ) {
//     			data.preventSubmit = true;
//     			pushData( $( provEvt ), submitJQData, data );
//     		} else {
//     			if ( !data.container ) {

//     				// Create the container next to component
//     				$container = $( "<div></div>" );
//     				$( provEvt.parentNode ).append( $container );
//     				data.container = $container.get( 0 );
//     			}
//     			$( event.target ).trigger( "ajax." + submitEvent, data );
//     		}
//     	},
//     	subAjax = function( event, data ) {
//     		var $container, containerID, ajxType,
//     			cleanSelector = data.clean;

//     		if ( !data.container ) {
//     			containerID = wb.getId();
//     			$container = $( "<div id='" + containerID + "'></div>" );
//     			$( data.form ).append( $container );
//     			cleanSelector = "#" + containerID;
//     		} else {
//     			$container = $( data.container );
//     		}

//     		if ( cleanSelector ) {
//     			$( data.origin ).one( cleanEvent, function( ) {
//     				$( cleanSelector ).empty();
//     			} );
//     		}

//     		if ( data.trigger ) {
//     			$container.attr( "data-trigger-wet", "true" );
//     		}

//     		ajxType = data.type ? data.type : "replace";
//     		$container.attr( "data-ajax-" + ajxType, data.url );

//     		$container.one( "wb-contentupdated", function( event, data ) {
//     			var updtElm = event.currentTarget,
//     				trigger = updtElm.getAttribute( "data-trigger-wet" );

//     			updtElm.removeAttribute( "data-ajax-" + data[ "ajax-type" ] );
//     			if ( trigger ) {
//     				$( updtElm )
//     					.find( wb.allSelectors )
//     						.addClass( "wb-init" )
//     						.filter( ":not(#" + updtElm.id + " .wb-init .wb-init)" )
//     							.trigger( "timerpoke.wb" );
//     				updtElm.removeAttribute( "data-trigger-wet" );
//     			}
//     		} );
//     		$container.trigger( "wb-update.wb-data-ajax" );
//     	},
//     	subToggle = function( event, data ) {
//     		var $origin = $( data.origin ),
//     			config = $( event.target ).data( configData ),
//     			toggleOpts = data.toggle;


//     		// For simple toggle call syntax
//     		if ( toggleOpts && typeof toggleOpts === "string" ) {
//     			toggleOpts = { selector: toggleOpts };
//     		}
//     		toggleOpts = $.extend( {}, toggleOpts, config.toggle );

//     		// Doing an add and remove "wb-toggle" class in order to avoid the click event added by toggle plugin
//     		$origin.addClass( "wb-toggle" );
//     		$origin.trigger( "toggle.wb-toggle", toggleOpts );

//     		// Set the cleaning task
//     		toggleOpts.type = "off";
//     		$origin.one( cleanEvent, function( ) {
//     			$origin.addClass( "wb-toggle" );
//     			$origin.trigger( "toggle.wb-toggle", toggleOpts );
//     			$origin.removeClass( "wb-toggle" );
//     		} );
//     	},
//     	actAppend = function( event, data ) {
//     		if ( event.namespace === actionEvent ) {
//     			var srctype = data.srctype ? data.srctype : componentName;
//     			data.container = data.provEvt.parentNode.id;
//     			if ( !data.source ) {
//     				throw "A source is required to append a field flow control.";
//     			}
//     			$( event.currentTarget ).trigger( srctype + "." + drawEvent, data );
//     		}
//     	},
//     	actTblFilter = function( event, data ) {
//     		if ( event.namespace === actionEvent ) {
//     			var sourceSelector = data.source,
//     				$datatable = $( sourceSelector ).dataTable( { "retrieve": true } ).api(),
//     				$dtSelectedColumn,
//     				column = data.column,
//     				colInt = parseInt( column, 10 ),
//     				regex = !!data.regex,
//     				smart = ( !data.smart ) ? true : !!data.smart,
//     				caseinsen = ( !data.caseinsen ) ? true : !!data.caseinsen;

//     			column = ( colInt === true ) ? colInt : column;

//     			$dtSelectedColumn = $datatable.column( column );

//     			$dtSelectedColumn.search( data.value, regex, smart, caseinsen ).draw();

//     			// Add a clean up task
//     			$( data.provEvt ).one( cleanEvent, function( ) {
//     				$dtSelectedColumn.search( "" ).draw();
//     			} );

//     		}
//     	},
//     	drwTblFilter = function( event, data ) {
//     		if ( event.namespace === drawEvent ) {
//     			var selColumn = data.column, // (integer/datatable column selector)
//     				csvExtract = data.csvextract, // (true|false) assume items are in CSV format instead of being inside "li" elements
//     				$column,
//     				sourceSelector = data.source,
//     				$source = $( sourceSelector ),
//     				$datatable,
//     				arrData, $listItem,
//     				i, i_len,
//     				j, j_len,
//     				items = [ ],
//     				cur_itm,
//     				prefLabel = data.label,
//     				defaultSelectedLabel = data.defaultselectedlabel,
//     				lblselector = data.lblselector,
//     				filterSequence = data.fltrseq ? data.fltrseq : [ ],
//     				limit = data.limit ? data.limit : 10,
//     				firstFilterSeq,
//     				actionItm, filterItm,
//     				renderas;

//     			// Check if the datatable has been loaded, if not we will wait until it is.
//     			if ( !$source.hasClass( "wb-tables-inited" ) ) {
//     				$source.one( "wb-ready.wb-tables", function() {
//     					$( event.target ).trigger( "tblfilter." + drawEvent, data );
//     				} );
//     				return false;
//     			}
//     			$datatable = $source.dataTable( { "retrieve": true } ).api();

//     			if ( $datatable.rows( { "search": "applied" } ).data().length <= limit  ) {
//     				return true;
//     			}

//     			renderas = data.renderas ? data.renderas : "select"; // Default it will render as select

//     			if ( !selColumn && filterSequence.length ) {
//     				cur_itm = filterSequence.shift();
//     				if ( !cur_itm.column ) {
//     					throw "Column is undefined in the filter sequence";
//     				}
//     				selColumn = cur_itm.column;
//     				csvExtract = cur_itm.csvextract;
//     				defaultSelectedLabel = cur_itm.defaultselectedlabel;
//     				prefLabel = cur_itm.label;
//     				lblselector = cur_itm.lblselector;
//     			}

//     			$column = $datatable.column( selColumn, { "search": "applied" } );

//     			// Get the items
//     			if ( csvExtract ) {
//     				arrData = $column.data();
//     				for ( i = 0, i_len = arrData.length; i !== i_len; i += 1 ) {
//     					items = items.concat( arrData[ i ].split( "," ) );
//     				}
//     			} else {
//     				arrData = $column.nodes();
//     				for ( i = 0, i_len = arrData.length; i !== i_len; i += 1 ) {
//     					$listItem = $( arrData[ i ] ).find( "li" );
//     					for ( j = 0, j_len = $listItem.length; j !== j_len; j += 1 ) {
//     						items.push( $( $listItem[ j ] ).text() );
//     					}
//     				}
//     			}

//     			items = items.sort().filter( function( item, pos, ary ) {
//     				return !pos || item !== ary[ pos - 1 ];
//     			} );

//     			var elm = event.target,
//     				$elm = $( elm ),
//     				itemsToCreate = [ ],
//     				pushAction = data.actions ? data.actions : [ ];

//     			if ( filterSequence.length ) {
//     				firstFilterSeq = filterSequence[ 0 ];
//     				filterItm = {
//     					action: "append",
//     					srctype: "tblfilter",
//     					source: sourceSelector,
//     					renderas: firstFilterSeq.renderas ? firstFilterSeq.renderas : renderas,
//     					fltrseq: filterSequence,
//     					limit: limit
//     				};
//     			}
//     			for ( i = 0, i_len = items.length; i !== i_len; i += 1 ) {
//     				cur_itm = items[ i ];
//     				actionItm = {
//     					label: cur_itm,
//     					actions: [
//     						{ // Set an action upon item selection
//     							action: "tblfilter",
//     							source: sourceSelector,
//     							column: selColumn,
//     							value: cur_itm
//     						}
//     					]
//     				};
//     				if ( filterItm ) {
//     					actionItm.actions.push( filterItm );
//     				}
//     				itemsToCreate.push( actionItm );
//     			}

//     			if ( !prefLabel ) {
//     				prefLabel = $column.header().textContent;
//     			}

//     			if ( !data.outputctnrid ) {
//     				data.outputctnrid = data.provEvt.parentElement.id;
//     			}

//     			$elm.trigger( renderas + "." + createCtrlEvent, {
//     				actions: pushAction,
//     				source: $source.get( 0 ),
//     				outputctnrid: data.outputctnrid,
//     				label: prefLabel,
//     				defaultselectedlabel: defaultSelectedLabel,
//     				lblselector: lblselector,
//     				items: itemsToCreate,
//     				inline: data.inline
//     			} );

//     		}
//     	},
//     	drwFieldflow = function( event, data ) {
//     		if ( event.namespace === drawEvent ) {
//     			var elm = event.target,
//     				$elm = $( elm ),
//     				wbDataElm,
//     				$source = $( data.source ),
//     				source = $source.get( 0 ),
//     				$labelExplicit, $firstChild,
//     				labelSelector = data.lblselector || "." + labelClass,
//     				labelTxt,
//     				itmSelector = data.itmselector || "ul:first() > li", $items,
//     				actions,
//     				renderas;

//     			// Extend if it is a sub-component
//     			if ( $source.hasClass( subComponentName ) ) {
//     				wbDataElm = wb.getData( $source, componentName );
//     				$source.data( configData, wbDataElm );
//     				data = $.extend( {}, data, wbDataElm );
//     			}

//     			actions = data.actions || [ ];
//     			renderas = data.renderas ? data.renderas : "select"; // Default it will render as select

//     			// Check if the first node is a div and contain the label.
//     			if ( !source.id ) {
//     				source.id = wb.getId();
//     			}
//     			$firstChild = $source.children().first();

//     			if ( !$firstChild.hasClass( headerClass ) ) {

//     				// Only use what defined as the label, nothing else
//     				$labelExplicit = $firstChild.find( labelSelector );
//     				if ( $labelExplicit.length ) {
//     					labelTxt = $labelExplicit.html();
//     				} else {
//     					labelTxt = $source.find( "> p" ).html();
//     				}
//     				labelSelector = null; // unset the label selector because it not needed for the control creation
//     			} else {
//     				labelTxt = $firstChild.html();
//     				itmSelector = "." + headerClass + " + " + itmSelector;
//     			}

//     			$items = getItemsData( $source.find( itmSelector ) );

//     			if ( !data.outputctnrid ) {
//     				data.outputctnrid = data.provEvt.parentElement.id;
//     			}

//     			$elm.trigger( renderas + "." + createCtrlEvent, {
//     				actions: actions,
//     				source: source,
//     				attributes: data.attributes,
//     				outputctnrid: data.outputctnrid,
//     				label: labelTxt,
//     				lblselector: labelSelector,
//     				defaultselectedlabel: data.defaultselectedlabel,
//     				required: !!!data.isoptional,
//     				noreqlabel: data.noreqlabel,
//     				items: $items,
//     				inline: data.inline
//     			} );
//     		}
//     	},
//     	ctrlSelect = function( event, data ) {
//     		var bodyId = data.outputctnrid,
//     			$body = $( "#" + bodyId ),
//     			actions = data.actions,
//     			lblselector = data.lblselector,
//     			isReq = !!data.required,
//     			useReqLabel = !!!data.noreqlabel,
//     			items = data.items,
//     			elm = event.target,
//     			$elm = $( elm ),
//     			source = data.source,
//     			attributes = data.attributes,
//     			i18n = $elm.data( configData ).i18n,
//     			autoID = wb.getId(),
//     			labelPrefix = "<label for='" + autoID + "'",
//     			labelSuffix = "</span>",
//     			$out, $tmpLabel,
//     			selectOut, $selectOut,
//     			defaultSelectedLabel = data.defaultselectedlabel ? data.defaultselectedlabel : i18n.defaultsel,
//     			i, i_len, j, j_len, cur_itm;

//     		// Create the label
//     		if ( isReq && useReqLabel ) {
//     			labelPrefix += " class='required'";
//     			labelSuffix += " <strong class='required'>(" + i18n.required + ")</strong>";
//     		}
//     		labelPrefix += "><span class='field-name'>";
//     		labelSuffix += "</label>";

//     		if ( !lblselector ) {
//     			$out = $( labelPrefix + data.label + labelSuffix );
//     		} else {
//     			$out = $( "<div>" + data.label + "</div>" );
//     			$tmpLabel = $out.find( lblselector );
//     			$tmpLabel.html( labelPrefix + $tmpLabel.html() + labelSuffix );
//     		}

//     		// Create the select
//     		selectOut = "<select id='" + autoID + "' name='" + basenameInput + autoID + "' class='full-width form-control mrgn-bttm-md " + crtlSelectClass + "' data-" + originData + "='" + elm.id + "' " + sourceDataAttr + "='" + source.id + "'";
//     		if ( isReq ) {
//     			selectOut += " required";
//     		}
//     		if ( attributes && typeof attributes === "object" ) {
//     			for ( i in attributes ) {
//     				if ( attributes.hasOwnProperty( i ) ) {
//     					selectOut += " " + i + "='" + attributes[ i ] + "'";
//     				}
//     			}
//     		}
//     		selectOut += "><option value=''>" + defaultSelectedLabel + "</option>";
//     		for ( i = 0, i_len = items.length; i !== i_len; i += 1 ) {
//     			cur_itm = items[ i ];

//     			if ( !cur_itm.group ) {
//     				selectOut += buildSelectOption( cur_itm );
//     			} else {

//     				// We have a group of sub-items, the cur_itm are a group
//     				selectOut += "<optgroup label='" + cur_itm.label + "'>";
//     				j_len = cur_itm.group.length;
//     				for ( j = 0; j !== j_len; j += 1 ) {
//     					selectOut += buildSelectOption( cur_itm.group[ j ] );
//     				}
//     				selectOut += "</optgroup>";
//     			}
//     		}
//     		selectOut += "</select>";
//     		$selectOut = $( selectOut );

//     		$body.append( $out ).append( $selectOut );

//     		// Set post action if any
//     		if ( actions && actions.length > 0 ) {
//     			$selectOut.data( pushJQData, actions );
//     		}

//     		// Register this control
//     		pushData( $elm, registerJQData, autoID );
//     	},
//     	ctrlChkbxRad = function( event, data ) {
//     		var bodyId = data.outputctnrid,
//     			actions = data.actions,
//     			lblselector = data.lblselector,
//     			isReq = !!data.required,
//     			useReqLabel = !!!data.noreqlabel,
//     			items = data.items,
//     			elm = event.target,
//     			$elm = $( elm ),
//     			source = data.source,
//     			i18n = $elm.data( configData ).i18n,
//     			attributes = data.attributes,
//     			ctrlID = wb.getId(),
//     			fieldsetPrefix = "<legend class='h5 ",
//     			fieldsetSuffix = "</span>",
//     			fieldsetHTML = "<fieldset id='" + ctrlID + "' data-" + originData + "='" + elm.id + "' " + sourceDataAttr + "='" + source.id + "' class='" + crtlSelectClass + " mrgn-bttm-md'",
//     			$out,
//     			$tmpLabel, $cloneLbl, $prevContent,
//     			radCheckOut = "",
//     			typeRadCheck = data.typeRadCheck,
//     			isInline = data.inline,
//     			fieldName = basenameInput + ctrlID,
//     			i, i_len, j, j_len, cur_itm;

//     		if ( attributes && typeof attributes === "object" ) {
//     			for ( i in attributes ) {
//     				if ( attributes.hasOwnProperty( i ) ) {
//     					fieldsetHTML += " " + i + "='" + attributes[ i ] + "'";
//     				}
//     			}
//     		}
//     		$out = $( fieldsetHTML + "></fieldset>" );

//     		// Create the legend
//     		if ( isReq && useReqLabel ) {
//     			fieldsetPrefix += " required";
//     			fieldsetSuffix += " <strong class='required'>(" + i18n.required + ")</strong>";
//     		}
//     		fieldsetPrefix += "'>";
//     		fieldsetSuffix += "</legend>";
//     		if ( !lblselector ) {
//     			$out.append( $( fieldsetPrefix + data.label + fieldsetSuffix ) );
//     		} else {
//     			$cloneLbl = $( "<div>" + data.label + "</div>" );
//     			$tmpLabel = $cloneLbl.find( lblselector );
//     			$out.append( ( fieldsetPrefix + $tmpLabel.html() + fieldsetSuffix ) )
//     				.append( $tmpLabel.nextAll() );
//     			$prevContent = $tmpLabel.prevAll();
//     		}

//     		// Create radio
//     		for ( i = 0, i_len = items.length; i !== i_len; i += 1 ) {
//     			cur_itm = items[ i ];

//     			if ( !cur_itm.group ) {
//     				radCheckOut += buildCheckboxRadio( cur_itm, fieldName, typeRadCheck, isInline, isReq, i + 1 );
//     			} else {

//     				// We have a group of sub-items, the cur_itm are a group
//     				radCheckOut += "<p>" + cur_itm.label + "</p>";
//     				j_len = cur_itm.group.length;
//     				for ( j = 0; j !== j_len; j += 1 ) {
//     					radCheckOut += buildCheckboxRadio( cur_itm.group[ j ], fieldName, typeRadCheck, isInline, isReq );
//     				}
//     			}
//     		}
//         if ( isInline ) {
//           radCheckOut = "<div class='gc-form'>" + radCheckOut;
//         } else {
//           radCheckOut = "<ul class='list-unstyled gc-form'>" + radCheckOut;
//         }
//     		$out.append( radCheckOut );
//     		$( "#" + bodyId ).append( $out );
//     		if ( $prevContent ) {
//     			$out.before( $prevContent );
//     		}

//     		// Set post action if any
//     		if ( actions && actions.length > 0 ) {
//     			$out.data( pushJQData, actions );
//     		}

//     		// Register this control
//     		pushData( $elm, registerJQData, ctrlID );
//     	},
//     	getItemsData = function( $items, preventRecusive ) {
//     		var arrItems = $items.get(),
//     			i, i_len = arrItems.length, itmCached,
//     			itmLabel, itmValue, grpItem,
//     			j, j_len, childNodes, firstNode, childNode, $childNode, childNodeID,
//     			parsedItms = [],
//     			actions;

//     		for ( i = 0; i !== i_len; i += 1 ) {
//     			itmCached = arrItems[ i ];

//     			itmValue = "";
//     			grpItem = null;
//     			itmLabel = "";

//     			firstNode = itmCached.firstChild;
//     			childNodes = itmCached.childNodes;
//     			j_len = childNodes.length;

//     			if ( !firstNode ) {
//     				throw "You have a markup error, There may be an empyt <li> elements in your list.";
//     			}

//     			actions = [];

//     			// Is firstNode an anchor?
//     			if ( firstNode.nodeName === "A" ) {
//     				itmValue = firstNode.getAttribute( "href" );
//     				itmLabel = $( firstNode ).html();
//     				j_len = 1; // Force following elements to be ignored

//     				actions.push( {
//     					action: "redir",
//     					url: itmValue
//     				} );
//     			}

//     			// Iterate until we have found the labelClass or <ul> or element with subSelector or end of the array
//     			for ( j = 1; j !== j_len; j += 1 ) {
//     				childNode = childNodes[ j ];
//     				$childNode = $( childNode );

//     				// Sub plugin
//     				if ( $childNode.hasClass( subComponentName ) ) {
//     					childNodeID = childNode.id || wb.getId();
//     					childNode.id = childNodeID;
//     					itmValue = componentName + "-" + childNodeID;

//     					actions.push( {
//     						action: "append",
//     						srctype: componentName,
//     						source: "#" + childNodeID
//     					} );
//     					break;
//     				}

//     				// Grouping
//     				if ( childNode.nodeName === "UL" ) {
//     					if ( preventRecusive ) {
//     						throw "Recursive error, please check your code";
//     					}
//     					grpItem = getItemsData( $childNode.children(), true );
//     				}

//     				// Explicit label to use
//     				if ( $childNode.hasClass( labelClass ) ) {
//     					itmLabel = $childNode.html();
//     				}
//     			}

//     			if ( !itmLabel ) {
//     				itmLabel = firstNode.nodeValue;
//     			}

//     			// Set an id on the element
//     			if ( !itmCached.id ) {
//     				itmCached.id = wb.getId();
//     			}

//     			// Return the item parsed
//     			parsedItms.push( {
//     				"bind": itmCached.id,
//     				"label": itmLabel,
//     				"actions": actions,
//     				"group": grpItem
//     			} );
//     		}
//     		return parsedItms;
//     	},
//     	buildSelectOption = function( data ) {
//     		var label = data.label,
//     			out = "<option value='" + label + "'";

//     		out += buildDataAttribute( data );

//     		out += ">" + label + "</option>";

//     		return out;
//     	},
//     	buildDataAttribute = function( data ) {
//     		var out = "",
//     			dataFieldflow = {};

//     		dataFieldflow.bind = data.bind || "";
//     		dataFieldflow.actions = data.actions || [ ];

//     		out += " data-" + componentName + "='" + JSON.stringify( dataFieldflow ) + "'";

//     		return out;
//     	},

//       buildCheckboxRadio = function( data, fieldName, inputType, isInline, isReq, iLoopBuilder ) {
//         var label = data.label,
//           fieldID = wb.getId(),
//           inline = isInline ? "-inline" : "",
//           out = "<"
//         if ( isInline ) {
//           out += "span class='form-" + inputType + "'><input id='" + fieldID + "' type='" + inputType + "' name='" + fieldName + "' value='" + label + "'" ; //used to have + out - what is this inline thing?
//         } else {
//           out += "li class='" + inputType + "'><input id='" + fieldID + "' type='" + inputType + "' name='" + fieldName + "' value='" + label + "'";
//         }
//     		out += buildDataAttribute( data );

//     		if ( isReq ) {
//     			out += " required='required'";
//     		}

//         if ( isInline) {
//           out += "/><label class='form-" + inputType + " " + "form-" + inputType + "-inline'" + "for='" + fieldID + "'";
//           out += " >" + label + "</label></span>";


//         } else {
//         out += "/><label for='" + fieldID + "'" + "for='" + fieldID + "'";
//         out += " >" + label + "</label></li>";
//         }



//     		return out;
//     	};

//     $document.on( resetActionEvent, selector + ", ." + subComponentName, function( event ) {
//     	var elm = event.target,
//     		$elm,
//     		settings,
//     		settingsReset,
//     		resetAction = [],
//     		i, i_len, i_cache, action, isLive;

//     	if ( elm === event.currentTarget ) {
//     		$elm = $( elm );
//     		settings = $elm.data( configData );

//     		if ( settings && settings.reset ) {
//     			settingsReset = settings.reset;

//     			if ( $.isArray( settingsReset ) ) {
//     				resetAction = settingsReset;
//     			} else {
//     				resetAction.push( settingsReset );
//     			}

//     			i_len = resetAction.length;
//     			for ( i = 0; i !== i_len; i += 1 ) {
//     				i_cache = resetAction[ i ];
//     				action = i_cache.action;
//     				if ( action ) {
//     					isLive = i_cache.live;
//     					if ( isLive !== false ) {
//     						i_cache.live = true;
//     					}
//     					$elm.trigger( action + "." + actionEvent, i_cache );
//     				}
//     			}
//     		}
//     	}
//     } );

//     // Load content after the user have choosen an option
//     $document.on( "change", selectorForm + " " + crtlSelectSelector, function( event ) {

//     	var elm = event.currentTarget,
//     		$elm = $( elm ),
//     		selCurrentElm, cacheAction,
//     		i, i_len, dtCached, dtCachedTarget,
//     		itmToClean = $elm.nextAll(), itm, idxItem,
//     		$orgin = $( "#" + elm.getAttribute( "data-" + originData ) ),
//     		$source = $( "#" + elm.getAttribute( sourceDataAttr ) ),
//     		lstIdRegistered = $orgin.data( registerJQData ),
//     		$optSel = $elm.find( ":checked", $elm ),
//     		form = $elm.get( 0 ).form;

//     	//
//     	// 1. Cleaning
//     	//
//     	i_len = itmToClean.length;
//     	if ( i_len ) {
//     		for ( i = i_len; i !== 0; i -= 1 ) {
//     			itm = itmToClean[ i ];
//     			if ( itm ) {
//     				idxItem = lstIdRegistered.indexOf( itm.id );
//     				if ( idxItem > -1 ) {
//     					lstIdRegistered.splice( idxItem, 1 );
//     				}
//     				$( "#" + itm.getAttribute( sourceDataAttr ) ).trigger( resetActionEvent ).trigger( cleanEvent );
//     				$( itm ).trigger( cleanEvent );
//     			}
//     		}
//     		$orgin.data( registerJQData, lstIdRegistered );
//     		itmToClean.remove();
//     	}
//     	$source.trigger( resetActionEvent ).trigger( cleanEvent );
//     	$elm.trigger( cleanEvent );

//     	// Remove any action that is pending for form submission
//     	$elm.data( submitJQData, [] );

//     	//
//     	// 2. Get defined actions
//     	//

//     	var actions = [],
//     		settings, settingsSrc, selFieldFlowData,
//     		actionAttr,
//     		defaultAction,
//     		defaultProp,
//     		baseAction,
//     		nowActions = [],
//     		postActions = [], postAction_len,
//     		bindTo,
//     		bindToElm;

//     	// From the component, default action
//     	settings = $orgin.data( configData );
//     	settingsSrc = $source.data( configData );
//     	if ( settingsSrc && settings ) {
//     		settings = $.extend( {}, settings, settingsSrc );
//     	}
//     	if ( $optSel.length && $optSel.val() && settings && settings.default ) {
//     		cacheAction = settings.default;
//     		if ( $.isArray( cacheAction ) ) {
//     			actions = cacheAction;
//     		} else {
//     			actions.push( cacheAction );
//     		}
//     	}

//     	defaultAction = settings.action;
//     	defaultProp = settings.prop;
//     	actionData = settings.actionData || {};

//     	// From the component, action pushed for later
//     	cacheAction = $elm.data( pushJQData );
//     	if ( cacheAction ) {
//     		actions = actions.concat( cacheAction );
//     	}

//     	// For each the binded elements that are selected
//     	for ( i = 0, i_len = $optSel.length; i !== i_len; i += 1 ) {
//     		selCurrentElm = $optSel.get( i );
//     		selFieldFlowData = wb.getData( selCurrentElm, componentName );
//     		if ( selFieldFlowData ) {
//     			bindTo = selFieldFlowData.bind;
//     			actions = actions.concat( selFieldFlowData.actions );

//     			if ( bindTo ) {

//     				// Retreive action set on the binded element
//     				bindToElm = document.getElementById( bindTo );
//     				actionAttr = bindToElm.getAttribute( "data-" + componentName );
//     				if ( actionAttr ) {
//     					if ( actionAttr.startsWith( "{" ) || actionAttr.startsWith( "[" ) ) {
//     						try {
//     							cacheAction = JSON.parse( actionAttr );
//     						} catch ( error ) {
//     							$.error( "Bad JSON object " + actionAttr );
//     						}
//     						if ( !$.isArray( cacheAction ) ) {
//     							cacheAction = [ cacheAction ];
//     						}
//     					} else {
//     						cacheAction = {};
//     						cacheAction.action = defaultAction;
//     						cacheAction[ defaultProp ] = actionAttr;
//     						cacheAction = $.extend( true, {}, actionData, cacheAction );
//     						cacheAction = [ cacheAction ];
//     					}
//     					actions = actions.concat( cacheAction );
//     				}
//     			}
//     		}
//     	}

//     	// If there is no action, do nothing
//     	if ( !actions.length ) {
//     		return true;
//     	}

//     	//
//     	// 3. Sort action
//     	// 			array1 = Action to be executed now
//     	//			array2 = Action to be postponed for later use
//     	for ( i = 0, i_len = actions.length; i !== i_len; i += 1 ) {
//     		dtCached = actions[ i ];
//     		dtCachedTarget = dtCached.target;
//     		if ( !dtCachedTarget || dtCachedTarget === bindTo ) {
//     			nowActions.push( dtCached );
//     		} else {
//     			postActions.push( dtCached );
//     		}
//     	}

//     	//
//     	// 4. Execute action for the current item
//     	//
//     	baseAction = settings.base || {};
//     	postAction_len = postActions.length;
//     	for ( i = 0, i_len = nowActions.length; i !== i_len; i += 1 ) {
//     		dtCached = $.extend( {}, baseAction, nowActions[ i ] );
//     		dtCached.origin = $source.get( 0 );
//     		dtCached.provEvt = elm;
//     		dtCached.$selElm = $optSel;
//     		dtCached.form = form;
//     		if ( postAction_len ) {
//     			dtCached.actions = postActions;
//     		}
//     		$orgin.trigger( dtCached.action + "." + actionEvent, dtCached );
//     	}
//     	return true;
//     } );


//     // Load content after the user have choosen an option
//     $document.on( "submit", selectorForm + " form", function( event ) {

//     	var elm = event.currentTarget,
//     		$elm = $( elm ),
//     		wbFieldFlowRegistered = $elm.data( registerJQData ),
//     		wbRegisteredHidden = $elm.data( registerHdnFld ) || [],
//     		$hdnField,
//     		i, i_len = wbFieldFlowRegistered ? wbFieldFlowRegistered.length : 0,
//     		$wbFieldFlow, fieldOrigin,
//     		lstFieldFlowPostEvent = [],
//     		componentRegistered, $componentRegistered, $origin, lstOrigin = [],
//     		settings,
//     		j, j_len,
//     		m, m_len, m_cache,
//     		actions,
//     		preventSubmit = false, lastProvEvt;

//     	// Run the cleaning on the current items
//     	if ( i_len ) {
//     		$wbFieldFlow = $( "#" + wbFieldFlowRegistered[ i_len - 1 ] );
//     		fieldOrigin = $wbFieldFlow.data( registerJQData );
//     		$( "#" + fieldOrigin[ fieldOrigin.length - 1 ] ).trigger( cleanEvent );
//     		$wbFieldFlow.trigger( cleanEvent );
//     	}

//     	// For each wb-fieldflow component, execute submiting task.
//     	for ( i = 0; i !== i_len; i += 1 ) {
//     		$wbFieldFlow = $( "#" + wbFieldFlowRegistered[ i ] );
//     		componentRegistered = $wbFieldFlow.data( registerJQData );
//     		j_len = componentRegistered.length;
//     		for ( j = 0; j !== j_len; j += 1 ) {
//     			$componentRegistered = $( "#" + componentRegistered[ j ] );
//     			$origin = $( "#" + $componentRegistered.data( originData ) );
//     			lstOrigin.push( $origin );
//     			settings = $origin.data( configData );
//     			actions = $componentRegistered.data( submitJQData );

//     			// If there is If None setting
//     			if ( !actions && settings.defaultIfNone ) {
//     				actions = settings.defaultIfNone;
//     				for ( m = 0, m_len = actions.length; m !== m_len; m += 1 ) {
//     					m_cache = actions[ m ];
//     					m_cache.origin = $origin.get( 0 );
//     					m_cache.$selElm = $origin.prev().find( "input, select" ).eq( 0 );
//     					m_cache.provEvt = m_cache.$selElm.get( 0 );
//     					m_cache.form = elm;
//     					$origin.trigger( m_cache.action + "." + actionEvent, m_cache );
//     				}
//     				actions = $componentRegistered.data( submitJQData );
//     			}
//     			if ( actions ) {
//     				for ( m = 0, m_len = actions.length; m !== m_len; m += 1 ) {
//     					m_cache = actions[ m ];
//     					m_cache.form = elm;
//     					$wbFieldFlow.trigger( m_cache.action + "." + submitEvent, m_cache );
//     					lstFieldFlowPostEvent.push( {
//     						$elm: $wbFieldFlow,
//     						data: m_cache
//     					} );
//     					preventSubmit = preventSubmit || m_cache.preventSubmit;
//     					lastProvEvt = m_cache.provEvt;
//     				}
//     			}
//     		}
//     	}

//     	// Before to submit, remove jj-down accessesory control
//     	if ( !preventSubmit ) {
//     		$elm.find( basenameInputSelector ).removeAttr( "name" );

//     		// Fix an issue when clicking back with the mouse
//     		i_len = wbRegisteredHidden.length;
//     		for ( i = 0; i !== i_len; i += 1 ) {
//     			$( wbRegisteredHidden[ i ] ).remove();
//     		}
//     		wbRegisteredHidden = [];

//     		// Check the form action, if there is query string, do split it and create hidden field for submission
//     		// The following is may be simply caused by a cross-server security issue generated by the browser itself
//     		var frmAction, idxQueryDelimiter,
//     			queryString, cacheParam, cacheName,
//     			items, params;

//     		frmAction = $elm.attr( "action" );
//     		if ( frmAction ) {
//     			idxQueryDelimiter = frmAction.indexOf( "?" );
//     			if ( idxQueryDelimiter > 0 ) {

//     				// Split the query string and create hidden input.
//     				queryString = frmAction.substring( idxQueryDelimiter + 1 );
//     				params = queryString.split( "&" );

//     				i_len = params.length;
//     				for ( i = 0; i !== i_len; i += 1 ) {
//     					cacheParam = params[ i ];
//     					cacheName = cacheParam;
//     					if ( cacheParam.indexOf( "=" ) > 0 ) {
//     						items = cacheParam.split( "=", 2 );
//     						cacheName = items[ 0 ];
//     						cacheParam = items[ 1 ];
//     					}
//     					$hdnField = $( "<input type='hidden' name='" + cacheName + "' value='" + cacheParam + "' />" );
//     					$elm.append( $hdnField );
//     					wbRegisteredHidden.push( $hdnField.get( 0 ) );
//     				}
//     				$elm.data( registerHdnFld, wbRegisteredHidden );
//     			}
//     		}
//     	}

//     	// Add global action
//     	i_len = lstOrigin.length;
//     	for ( i = 0; i !== i_len; i += 1 ) {
//     		$origin = lstOrigin[ i ];
//     		settings = $origin.data( configData );
//     		if ( settings.action ) {
//     			lstFieldFlowPostEvent.push( {
//     				$elm: $origin,
//     				data: settings
//     			} );
//     		}
//     	}

//     	i_len = lstFieldFlowPostEvent.length;
//     	for ( i = 0; i !== i_len; i += 1 ) {
//     		m_cache = lstFieldFlowPostEvent[ i ];
//     		m_cache.data.lastProvEvt = lastProvEvt;
//     		m_cache.$elm.trigger( m_cache.data.action + "." + submitedEvent, m_cache.data );
//     	}
//     	if ( preventSubmit ) {
//     		event.preventDefault();
//     		if ( event.stopPropagation ) {
//     			event.stopImmediatePropagation();
//     		} else {
//     			event.cancelBubble = true;
//     		}
//     		return false;
//     	}
//     } );

//     $document.on( "keyup", selectorForm + " select", function( Ev ) {

//     	// Add the fix for the on change event - https://bugzilla.mozilla.org/show_bug.cgi?id=126379
//     	if ( navigator.userAgent.indexOf( "Gecko" ) !== -1 ) {

//     		// prevent tab, alt, ctrl keys from fireing the event
//     		if ( Ev.keyCode && ( Ev.keyCode === 1 || Ev.keyCode === 9 || Ev.keyCode === 16 || Ev.altKey || Ev.ctrlKey ) ) {
//     			return true;
//     		}
//     		$( Ev.target ).trigger( "change" );
//     		return true;
//     	}
//     } );

//     $document.on( fieldflowActionsEvents, selector, function( event, data ) {

//     	var eventType = event.type;

//     	switch ( event.namespace ) {
//     	case drawEvent:
//     		switch ( eventType ) {
//     		case componentName:
//     			drwFieldflow( event, data );
//     			break;
//     		case "tblfilter":
//     			drwTblFilter( event, data );
//     			break;
//     		}
//     		break;

//     	case createCtrlEvent:
//     		switch ( eventType ) {
//     		case "select":
//     			ctrlSelect( event, data );
//     			break;
//     		case "checkbox":
//     			data.typeRadCheck = "checkbox";
//     			ctrlChkbxRad( event, data );
//     			break;
//     		case "radio":
//     			data.typeRadCheck = "radio";
//     			ctrlChkbxRad( event, data );
//     			break;
//     		}
//     		break;

//     	case actionEvent:
//     		switch ( eventType ) {
//     		case "append":
//     			actAppend( event, data );
//     			break;
//     		case "redir":
//     			pushData( $( data.provEvt ), submitJQData, data, true );
//     			break;
//     		case "ajax":
//     			actAjax( event, data );
//     			break;
//     		case "tblfilter":
//     			actTblFilter( event, data );
//     			break;
//     		case "toggle":
//     			if ( data.live ) {
//     				subToggle( event, data );
//     			} else {
//     				data.preventSubmit = true;
//     				pushData( $( data.provEvt ), submitJQData, data );
//     			}
//     			break;
//     		case "addClass":
//     			if ( !data.source || !data.class ) {
//     				return;
//     			}
//     			if ( data.live ) {
//     				$( data.source ).addClass( data.class );
//     			} else {
//     				data.preventSubmit = true;
//     				pushData( $( data.provEvt ), submitJQData, data );
//     			}
//     			break;
//     		case "removeClass":
//     			if ( !data.source || !data.class ) {
//     				return;
//     			}
//     			if ( data.live ) {
//     				$( data.source ).removeClass( data.class );
//     			} else {
//     				data.preventSubmit = true;
//     				pushData( $( data.provEvt ), submitJQData, data );
//     			}
//     			break;
//     		case "query":
//     			actQuery( event, data );
//     			break;
//     		}
//     		break;

//     	case submitEvent:
//     		switch ( eventType ) {
//     		case "redir":
//     			subRedir( event, data );
//     			break;
//     		case "ajax":
//     			subAjax( event, data );
//     			break;
//     		case "toggle":
//     			subToggle( event, data );
//     			break;
//     		case "addClass":
//     			$( data.source ).addClass( data.class );
//     			break;
//     		case "removeClass":
//     			$( data.source ).removeClass( data.class );
//     			break;
//     		case "query":
//     			actQuery( event, data );
//     			break;
//     		}
//     		break;
//     	}
//     } );

//     // Bind the init event of the plugin
//     $document.on( "timerpoke.wb " + initEvent, selector, function( event ) {
//     	switch ( event.type ) {
//     	case "timerpoke":
//     	case "wb-init":
//     		init( event );
//     		break;
//     	}

//     	/*
//     	* Since we are working with events we want to ensure that we are being passive about our control,
//     	* so returning true allows for events to always continue
//     	*/
//     	return true;
//     } );

//     // Add the timer poke to initialize the plugin
//     wb.add( selector );
// }(jQuery, document, wb),
// function(x, k, A) {
//     "use strict";

//     function j(e) {
//         return e.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
//     }

//     function r(e) {
//         var t = e.target,
//             a = k.getElementById(t.getAttribute("list"));
//         Modernizr.load({
//             test: Modernizr.stringnormalize,
//             nope: ["site!deps/unorm" + A.getMode() + ".js"]
//         }), x(a).trigger({
//             type: "json-fetch.wb",
//             fetch: {
//                 url: a.dataset.wbSuggest
//             }
//         })
//     }

//     function n(e) {
//         var t = e.target,
//             a = k.getElementById(t.getAttribute("list")),
//             r = e.target.value,
//             n = e.which;
//         switch (C && clearTimeout(C), e.type) {
//             case "change":
//                 C = setTimeout(S.bind(a, r), 250);
//                 break;
//             case "keyup":
//                 e.ctrlKey || e.altKey || e.metaKey || (8 === n || 32 === n || 47 < n && n < 91 || 95 < n && n < 112 || 159 < n && n < 177 || 187 < n && n < 223) && (C = setTimeout(S.bind(a, r), 250))
//         }
//     }
//     var C, i = A.doc,
//         o = "wb-suggest",
//         s = "[data-" + o + "]",
//         E = 5,
//         S = function(e, t, a) {
//             var r, n, i, o, s, l, d, c, u, p = a || JSON.parse(this.dataset.wbSuggestions || []),
//                 f = this.dataset.wbFilterType || "any",
//                 h = p.length,
//                 b = [],
//                 g = this.childNodes,
//                 m = g.length - 1,
//                 v = x("[list=" + this.id + "]"),
//                 w = v.get(0);
//             if (!p.length && E && (E -= 1, C && clearTimeout(C), C = setTimeout(S(e, t, a), 250)), t || (t = parseInt(this.dataset.wbLimit || h)), e) {
//                 switch (f) {
//                     case "startWith":
//                         e = "^" + e;
//                         break;
//                     case "word":
//                         e = "^" + e + "|\\s" + e
//                 }
//                 r = new RegExp(e, "i")
//             }
//             if (!e || e.length < 2)(function() {
//                 var e, t, a = this.children;
//                 for (t = a.length - 1; 0 < t; t -= 1) 1 === (e = a[t]).nodeType && "TEMPLATE" !== e.nodeName && this.removeChild(e)
//             }).call(this), g = [];
//             else
//                 for (d = m; 0 !== d; d -= 1) 1 === (c = g[d]).nodeType && "OPTION" === c.nodeName && ((u = c.getAttribute("value")) && u.match(r) ? b.push(j(u)) : this.removeChild(c));
//             var y = this.querySelector("template");
//             for (y && !y.content && A.tmplPolyfill(y), n = 0; n < h && b.length < t; n += 1) i = p[n], o = j(i), -1 !== b.indexOf(o) || e && !i.match(r) || (b.push(o), y ? l = (s = y.content.cloneNode(!0)).querySelector("option") : (s = k.createDocumentFragment(), l = k.createElement("OPTION"), s.appendChild(l)), l.setAttribute("label", i), l.setAttribute("value", i), this.appendChild(s));
//             v.trigger("wb-update.wb-datalist"), w.value = w.value
//         };
//     i.on("timerpoke.wb wb-init.wb-suggest json-fetched.wb", s, function(e) {
//         var t = e.type,
//             a = e.target;
//         if (e.currentTarget === a) switch (t) {
//             case "timerpoke":
//             case "wb-init":
//                 ! function(e) {
//                     var t = A.init(e, o, s),
//                         a = "[list=" + e.target.id + "]";
//                     t && (Modernizr.addTest("stringnormalize", "normalize" in String), i.one("focus", a, r), (t.dataset.wbLimit || t.dataset.wbFilterType) && i.on("change keyup", a, n), A.ready(x(t), o))
//                 }(e);
//                 break;
//             case "json-fetched":
//                 (function(e) {
//                     this.dataset.wbSuggestions = JSON.stringify(e), delete this.dataset.wbSuggest, S.call(this, k.querySelector("[list=" + this.id + "]").value)
//                 }).call(a, e.fetch.response)
//         }
//         return !0
//     }), A.add(s)
// }(jQuery, document, wb),
// function(I, f, h, T) {
//     "use strict";

//     function d(e, t) {
//         for (var a = h.getElementById(e.getAttribute("aria-controls")), r = I(e).parent(), n = I(e); !n.hasClass("wb5React");) n = n.parent();
//         var i, o, s = C[n.get(0).id],
//             l = a.getAttribute("data-wb5-template");
//         if (l && (i = s.template(l) || h.getElementById(l) || s.tmplDefault(l)), !i || !l) throw "No template defined to show listbox options";
//         s.template(l, i), R(i), o = i.content.cloneNode(!0), s.data.filter = e.value, q = [], N(o, s, l), o.querySelectorAll("[role=option]").length ? (a.innerHTML = "", a.appendChild(o), I(a).removeClass("hidden"), c = r.get(0)) : c && P()
//     }

//     function r(e) {
//         if (l(e), document.activeElement !== e) {
//             for (var t = I(e).parent(), a = I(e); !a.hasClass("wb5React");) a = a.parent();
//             for (var r = C[a.get(0).id], n = e.value, i = {}, o = r.data.options, s = 0; s < o.length; s++)
//                 if (n === o[s].value) {
//                     i = o[s];
//                     break
//                 }
//             t.trigger("wb.change", {
//                 value: e.value,
//                 item: i
//             })
//         }
//     }
//     var b, c, u, g = "wb-combobox",
//         m = "." + g,
//         e = T.doc,
//         v = {
//             template: '<div class="combobox-wrapper"><div role="combobox" aria-expanded="false" aria-haspopup="listbox" data-wb5-bind="aria-owns@popupId"><input autocomplete="off" data-rule-fromListbox="true" data-wb5-bind="id@fieldId, aria-controls@popupId, value@filter" aria-autocomplete="list" aria-activedescendant="" /></div><div data-wb5-bind="id@popupId" role="listbox" class="hidden"><template data-slot-elm="" data-wb5-template="sub-template-listbox"><ul class="list-unstyled mrgn-bttm-0">\x3c!-- <li class="brdr-bttm" role="option" data-wb5-for="option in wbLoad" data-wb5-if="!parent.filter.length || parent.config.compareLowerCase(option,parent.filter)" data-wb5-on="select@select(option); live@parent.nbdispItem(wb-nbNode)" >{{ option }}</li> --\x3e<li class="" role="option" data-wb5-for="option in options" data-wb5-if="!parent.filter.length || parent.config.compareLowerCase(option.value,parent.filter)" data-wb5-on="select@select(option.value); live@parent.nbdispItem(wb-nbNode)" >{{ option.textContent }}</li></ul></template></div></div>',
//             i18n: {
//                 en: {
//                     errValid: "You need to choose a valid options."
//                 },
//                 fr: {
//                     errValid: "Veuillez choisir une option valide."
//                 }
//             },
//             compareLowerCase: function(e, t) {
//                 return -1 !== e.toLowerCase().indexOf(t.toLowerCase())
//             },
//             similarText: function(e, t, a) {
//                 function h(e, t) {
//                     for (var a = [], r = 0; r <= e.length; r++) {
//                         for (var n = r, i = 0; i <= t.length; i++)
//                             if (0 === r) a[i] = i;
//                             else if (0 < i) {
//                             var o = a[i - 1];
//                             e.charAt(r - 1) !== t.charAt(i - 1) && (o = Math.min(Math.min(o, n), a[i]) + 1), a[i - 1] = n, n = o
//                         }
//                         0 < r && (a[t.length] = n)
//                     }
//                     return a[t.length]
//                 }
//                 var r = function(e, t) {
//                     e = e.replace(/[\-\/]|_/g, " ").replace(/[^\w\s]|_/g, "").trim().toLowerCase(), t = t.replace(/[\-\/]|_/g, " ").replace(/[^\w\s]|_/g, "").trim().toLowerCase();
//                     var a = e.split(" "),
//                         r = t.split(" ");
//                     if (e.length > t.length && (a = t.split(" "), r = e.split(" ")), !r.length || !a.length) return 100;
//                     for (var n = 0, i = 0, o = "", s = "", l = 0; l < a.length; l++) {
//                         for (var d = 0, c = 0, u = !1, p = 0; p < r.length; p++)
//                             if (s = a[l], 0 <= (o = r[p]).indexOf(s)) {
//                                 var f = o.length;
//                                 (!u || f < d) && (d = o.length, c = o.length), u = !0
//                             } else u || d < (f = o.length - h(s, o)) && (d = f, c = o.length);
//                         n += d, i += c
//                     }
//                     return 0 === n ? 0 : n / i * 100
//                 }(e, t);
//                 return (a = parseInt(a)) <= r
//             }
//         },
//         p = 9,
//         w = 13,
//         y = 27,
//         x = 35,
//         k = 36,
//         A = 38,
//         j = 40,
//         C = {},
//         E = h.createDocumentFragment(),
//         q = [],
//         N = function(t, a, e) {
//             var r, n = t.childNodes,
//                 i = n.length,
//                 o = [];
//             for (m = 0; m < i; m++) {
//                 if (3 === (r = n[m]).nodeType && -1 != r.textContent.indexOf("{{")) {
//                     r.textContent = r.textContent.replace(/{{\s?([^}]*)\s?}}/g, function(e, t) {
//                         return U(a.data, t.trim())
//                     })
//                 }
//                 if ("TEMPLATE" !== r.nodeName) {
//                     if (1 === r.nodeType)
//                         if (r.hasAttribute("data-wb5-for")) {
//                             var s = z(r, "data-wb5-for"),
//                                 l = B(s),
//                                 d = U(a.data, l.for);
//                             if (!d) throw "Iterator not found";
//                             var c = d.length,
//                                 u = 0;
//                             for (d.wbLen = parseInt(c), I.isArray(d) && (d.active = u), m = 0; m < c; m++) {
//                                 var p = r.cloneNode(!0),
//                                     f = L(p),
//                                     h = {
//                                         "wb-idx": m,
//                                         "wb-nbNode": u,
//                                         parent: a.data
//                                     };
//                                 h[l.alias] = d[m], h = F(h), e && (a.data[e] = h), f.if && !D(f.if, h.data, a.data) || (u += 1, N(p, h, e), t.appendChild(p))
//                             }
//                             d.wbActive = u, o.push(r)
//                         } else r.hasAttribute("data-wb5-if") || r.hasAttribute("data-wb5-else") || r.hasAttribute("data-wb5-ifelse"), N(r, a, e)
//                 } else {
//                     R(r);
//                     var b = z(r, "data-wb5-template");
//                     b || (b = T.getId()), r.parentNode.hasAttribute("data-wb5-template") || r.parentNode.setAttribute("data-wb5-template", b), a.tmplDefault(b, r)
//                 }
//             }
//             for (i = o.length, m = 0; m !== i; m += 1) t.removeChild(o[m]);
//             if (1 === t.nodeType && t.hasAttribute("data-wb5-bind"))
//                 for (var g = z(t, "data-wb5-bind").split(", "), m = 0; m < g.length; m++) {
//                     var v = g[m].split("@");
//                     t[v[0]] ? (t[v[0]] = U(a.data, v[1]), a.observe(v[1], function(e) {
//                         return t[v[0]] = U(a.data, v[1]) || ""
//                     })) : (t.setAttribute(v[0], U(a.data, v[1])), a.observe(v[1], function(e) {
//                         return void 0 !== t[v[0]] ? t[v[0]] = U(a.data, v[1]) || "" : t.setAttribute(v[0], U(a.data, v[1])) || ""
//                     }))
//                 }
//             if (1 === t.nodeType && t.hasAttribute("data-wb5-text")) {
//                 var w = z(t, "data-wb5-text");
//                 t.textContent = U(a.data, w), a.observe(w, function(e) {
//                     return t.textContent = U(a.data, w) || ""
//                 })
//             }
//             if (1 === t.nodeType && t.hasAttribute("data-wb5-on")) {
//                 var y = z(t, "data-wb5-on").split("; ");
//                 i = y.length;
//                 for (m = 0; m < i; m++) {
//                     var x, k, A = y[m].split("@"),
//                         j = A[0],
//                         C = A[1],
//                         E = C.indexOf("("),
//                         S = C.lastIndexOf(")");
//                     if (E && S && (x = C.substring(0, E).trim(), k = C.substring(E + 1, S).trim()), !x) throw "Error, an event handler need to call a function";
//                     k && (k = O(k, a.data)), "live" === j ? U(a.data, x).call(a.data, k) : q.push({
//                         nd: t,
//                         evt: j,
//                         trigger: x,
//                         attr: k
//                     })
//                 }
//             }
//         },
//         z = function(e, t) {
//             var a = e.getAttribute(t);
//             return e.removeAttribute(t), a
//         },
//         D = function(e, t, a) {
//             return !!O(e, t, a)
//         },
//         O = function(e, n, i) {
//             var o = /{{-\s?([^}]*)\s?-}}/g,
//                 s = [];
//             return e = (e = (e = e.replace(/"([^"\\]*(\\.[^"\\]*)*)"|\'([^\'\\]*(\\.[^\'\\]*)*)\'/g, function(e, t) {
//                 var a = "{{-" + s.length + "-}}";
//                 return s.push(e), a
//             })).replace(/[a-zA-Z]([^\s]+)/g, function(e, t) {
//                 var a, r = e.trim();
//                 r = r.replace(o, function(e, t) {
//                     return s[t]
//                 });
//                 try {
//                     a = U(n, r)
//                 } catch (e) {
//                     try {
//                         a = U(i, r)
//                     } catch (e) {
//                         throw "Information in the DATA obj not found"
//                     }
//                 }
//                 return "object" == typeof a && (a = JSON.stringify(a)), "string" == typeof a ? '"' + a + '"' : a
//             })).replace(o, function(e, t) {
//                 return s[t]
//             }), new Function("return " + e)()
//         },
//         L = function(e) {
//             var t = {},
//                 a = z(e, "data-wb5-if");
//             if (a) t.if = a, n(t, {
//                 exp: a,
//                 block: e
//             });
//             else {
//                 null != z(e, "data-wb5-else") && (t.else = !0);
//                 var r = z(e, "data-wb5-elseif");
//                 r && (t.elseif = r)
//             }
//             return t
//         },
//         n = function(e, t) {
//             e.ifConditions || (e.ifConditions = []), e.ifConditions.push(t)
//         },
//         S = function(e, t) {
//             var a, r, n = e.options,
//                 i = n.length;
//             for (a = 0; a < i; a++) r = n[a], t.data.options.push({
//                 value: r.value,
//                 textContent: r.textContent
//             });
//             t.data.fieldId = e.id || T.getId(), t.data.fieldName = e.name, t.data.mustExist = !0
//         },
//         M = function(e, t) {
//             var a, r = node.childNodes,
//                 n = r.length;
//             for (a = 0; a < n; a++) r[a]
//         },
//         B = function(e) {
//             var t = /,([^,\}\]]*)(?:,([^,\}\]]*))?$/,
//                 a = e.match(/([^]*?)\s+(?:in|of)\s+([^]*)/);
//             if (a) {
//                 var r = {};
//                 r.for = a[2].trim();
//                 var n = a[1].trim().replace(/^\(|\)$/g, ""),
//                     i = n.match(t);
//                 return i ? (r.alias = n.replace(t, ""), r.iterator1 = i[1].trim(), i[2] && (r.iterator2 = i[2].trim())) : r.alias = n, r
//             }
//         },
//         U = function(e, t) {
//             var a = (t = t.trim()).substring(0, 1),
//                 r = t.substring(-1);
//             if ("'" === a || '"' === a || "'" === r || '"' === r) return t.substring(1, t.length - 1);
//             var n = t.indexOf("("),
//                 i = t.lastIndexOf(")"),
//                 o = [];
//             if (-1 !== n && -1 !== i && n + 1 !== i) {
//                 var s, l, d, c = t.substring(0, n);
//                 for (d = (o = t.substring(n + 1, i).split(",")).length, s = 0; s < d; s += 1) {
//                     l = o[s];
//                     var u = U(e, l);
//                     o[s] = u
//                 }
//                 t = c + "()"
//             }
//             var p, f, h, b = t.split("."),
//                 g = b.length;
//             for (p = 0; p < g; p += 1) {
//                 if (f = b[p], !e) return;
//                 e = -1 !== f.lastIndexOf("()") ? (h = f.substring(0, f.length - 2), "string" == typeof e ? String.prototype[h].apply(e, o) : e[h].apply(e, o)) : e[f]
//             }
//             return e
//         },
//         R = function(e) {
//             if (!e.content) {
//                 var t, a, r = e;
//                 for (t = r.childNodes, a = h.createDocumentFragment(); t[0];) a.appendChild(t[0]);
//                 r.content = a
//             }
//         },
//         P = function() {
//             if (c) {
//                 var e = c.getAttribute("aria-owns");
//                 I("#" + e).addClass("hidden"), c = null
//             }
//         },
//         l = function(e) {
//             var t = e.form && e.form.parentNode.classList.contains("wb-frmvld");
//             if (null === e.getAttribute("required") && "" === e.value || null === e.getAttribute("data-rule-mustExist")) return e.setCustomValidity(""), t && I(e).valid(), !0;
//             for (var a = I(e); !a.hasClass("wb5React");) a = a.parent();
//             var r, n, i = C[a.get(0).id],
//                 o = e.getAttribute("aria-controls"),
//                 s = (h.getElementById(o), i.data.options),
//                 l = e.value,
//                 d = s.length;
//             for (r = 0; r < d; r += 1)
//                 if (l === s[r].value) {
//                     n = !0;
//                     break
//                 }
//             return n ? (e.setCustomValidity(""), t && I(e).valid(), !0) : (e.setCustomValidity(b.errValid), t && I(e).valid(), !1)
//         };

//     function F(e) {
//         var a = {},
//             r = {},
//             n = {};
//         return function(e) {
//             for (var t in e) e.hasOwnProperty(t) && o(e, t)
//         }(e), {
//             data: e,
//             observe: t,
//             notify: i,
//             template: function(e, t) {
//                 {
//                     if (!t) return r[e] || !1;
//                     r[e] = t
//                 }
//             },
//             tmplDefault: function(e, t) {
//                 {
//                     if (!t) return n[e] || !1;
//                     n[e] = t
//                 }
//             },
//             debug_signals: a
//         };

//         function t(e, t) {
//             a[e] || (a[e] = []), a[e].push(t)
//         }

//         function i(e) {
//             !a[e] || a[e].length < 1 || a[e].forEach(function(e) {
//                 return e()
//             })
//         }

//         function o(e, t, a) {
//             var r = e[t];
//             if (Array.isArray(r)) return r.wbLen = parseInt(r.length), r.wbActive = 0, o(r, "wbLen", t), void o(r, "wbActive", t);
//             Object.defineProperty(e, t, {
//                 get: function() {
//                     return r
//                 },
//                 set: function(e) {
//                     r = e, i(a ? a + "." + t : t)
//                 }
//             })
//         }
//     }
//     e.on("wb-ready.wb", function(e) {
//         I.validator && I.validator.addMethod("fromListbox", function(e, t) {
//             return t.checkValidity()
//         }, "You need to choose a valid options")
//     }), e.on("json-fetched.wb", "[role=combobox]", function(e) {
//         for (var t = e.target, a = e.fetch.response, r = I(t); !r.hasClass("wb5React");) r = r.parent();
//         C[r.get(0).id].data.wbLoad = a
//     }), e.on("click vclick touchstart focusin", "body", function(e) {
//         c && !c.parentElement.contains(e.target) && setTimeout(function() {
//             P()
//         }, 1)
//     }), e.on("focus", "[role=combobox] input", function(e, t) {
//         c || setTimeout(function() {
//             d(e.target)
//         }, 1)
//     }), e.on("blur", "[role=combobox] input", function(e, t) {
//         r(e.target)
//     }), e.on("keyup", "[role=combobox] input", function(e) {
//         var t = e.which || e.keyCode,
//             a = e.target.classList.contains("error");
//         switch (t) {
//             case A:
//             case j:
//             case y:
//             case w:
//             case k:
//             case x:
//                 e.preventDefault();
//             case p:
//                 return void(a && setTimeout(function() {
//                     r(e.target)
//                 }, 100));
//             default:
//                 setTimeout(function() {
//                     d(e.target), a && r(e.target)
//                 }, 100)
//         }
//     }), e.on("keydown", "[role=combobox] input", function(e) {
//         var t = e.which || e.keyCode,
//             a = e.target;
//         if (t !== y) {
//             var r;
//             c || d(a);
//             var n = a.getAttribute("aria-activedescendant"),
//                 i = n ? h.getElementById(n) : null,
//                 o = h.getElementById(a.getAttribute("aria-controls")).querySelectorAll("[role=option]"),
//                 s = o.length,
//                 l = -1;
//             if (i) {
//                 for (l = 0; l < s && o[l].id !== i.id; l++);
//                 s <= l && (l = -1)
//             }
//             switch (t) {
//                 case A:
//                     -1 === l ? l = s - 1 : 0 !== l ? l-- : l = s - 1;
//                     break;
//                 case j:
//                     -1 === l ? l = 0 : l < s && l++;
//                     break;
//                 case k:
//                     l = 0;
//                     break;
//                 case x:
//                     l = s - 1;
//                     break;
//                 case w:
//                     return I(o[l]).trigger("wb.select"), P(), void e.preventDefault();
//                 case p:
//                     return u && I(o[l]).trigger("wb.select"), void P();
//                 default:
//                     return
//             }
//             e.preventDefault(), r = o[l], i && i.setAttribute("aria-selected", "false"), r ? (r.id || (r.id = T.getId()), a.setAttribute("aria-activedescendant", r.id), r.setAttribute("aria-selected", "true"), u = !0) : a.setAttribute("aria-activedescendant", "")
//         } else P()
//     }), e.on("mouseover", "[role=listbox] [role=option]", function(e, t) {
//         var a = c.querySelector("input"),
//             r = e.target,
//             n = a.getAttribute("aria-activedescendant"),
//             i = n ? h.getElementById(n) : null;
//         i && i.setAttribute("aria-selected", "false"), r.id || (r.id = T.getId()), i && i.id !== r.id && (u = !1), r.setAttribute("aria-selected", "true"), a.setAttribute("aria-activedescendant", r.id)
//     }), e.on("click", "[role=listbox] [role=option]", function(e, t) {
//         var a = I(c.querySelector("input"));
//         I(e.target).trigger("mouseover").trigger("wb.select"), a.trigger("focus"), P()
//     }), e.on("wb.select", "[role=listbox] [role=option]", function(e, t) {
//         for (var a = e.target, r = h.querySelector("[aria-activedescendant=" + a.id + "]"), n = I(r); !n.hasClass("wb5React");) n = n.parent();
//         var i, o, s = C[n.get(0).id];
//         for (i = 0; i < q.length; i++)
//             if ((o = q[i]).nd.isEqualNode(a)) {
//                 s.data[o.trigger].call(s.data, o.attr);
//                 break
//             }
//     }), e.on("timerpoke.wb wb-init.wb-combobox", m, function(e, t) {
//         var a, r, n = T.init(e, g, m);
//         if (n) {
//             a = I(n), r = I.extend(!0, {}, v, f[g], T.getData(a, g)), b || (b = r.i18n[T.lang]);
//             var i = F(t || function(e) {
//                 return {
//                     popupId: T.getId(),
//                     fieldId: !1,
//                     fieldName: "",
//                     mustExist: !1,
//                     filter: "",
//                     cntdisplayeditem: 0,
//                     options: [],
//                     config: e,
//                     i18n: {},
//                     horay: "testMe",
//                     select: function(e) {
//                         this.filter = e
//                     },
//                     nbdispItem: function(e) {
//                         this.cntdisplayeditem = e
//                     }
//                 }
//             }(r));
//             n.id || (n.id = T.getId()), r.parserUI && "function" == typeof r.parserUI ? r.parserUI(n, i) : r.parserUI && I.isArray(r.parserUI) && r.parserUI[n.id] ? r.parserUI[n.id].call(this, n, i) : function(e, t) {
//                 "SELECT" === e.nodeName ? S(e, t) : "INPUT" === e.nodeName && M(e, t)
//             }(n, i), i.data.fieldId || (i.data.fieldId = T.getId());
//             var o, s = function(e, t) {
//                     var a, r, n = t.templateID;
//                     if (n && (a = h.getElementById(n)), a) R(a), r = a.content.cloneNode(!0);
//                     else {
//                         var i = h.createElement("div");
//                         i.innerHTML = t.template, (r = h.createDocumentFragment()).appendChild(i)
//                     }
//                     return q = [], N(r, e), r
//                 }(i, r),
//                 l = s.childNodes,
//                 d = l.length;
//             for (p = 0; p < d; p++)
//                 if (1 === (o = l[p]).nodeType) {
//                     var c = o.id;
//                     c || (c = T.getId(), o.id = c), C[c] = i, o.classList.add("wb5React")
//                 }
//             var u = s.querySelector("[role=combobox]");
//             i.data.mustExist && s.querySelector("[role=combobox] input").setAttribute("data-rule-mustExist", "true");
//             n.parentNode.insertBefore(s, n);
//             r.hideSourceUI ? a.addClass("hidden") : (n.id = T.getId(), E.appendChild(n));
//             for (var p = 0; p < q.length; p++) q[p];
//             a = I(s), n.dataset.wbLoad && I(u).trigger({
//                 type: "json-fetch.wb",
//                 fetch: {
//                     url: n.dataset.wbLoad
//                 }
//             }), "function" != typeof E.getElementById && (E.getElementById = function(e) {
//                 var t, a, r = this.childNodes,
//                     n = r.length;
//                 for (t = 0; t < n; t += 1)
//                     if ((a = r[t]).id === e) return a;
//                 return !1
//             }), Modernizr.addTest("stringnormalize", "normalize" in String), Modernizr.load({
//                 test: Modernizr.stringnormalize,
//                 nope: ["site!deps/unorm" + T.getMode() + ".js"]
//             }), T.ready(a, g)
//         }
//     }), T.add(m)
// }(jQuery, window, document, wb),
// function(c, a, e, u) {
//     "use strict";

//     function r(e, t) {
//         e.id || (e.id = u.getId());
//         for (var a = 0; a < i.items.length; a++) {
//             var r = i.items[a],
//                 n = c.extend({}, r, {
//                     value: r.label,
//                     textContent: r.label
//                 });
//             n.source || (n.source = e.id), t.data.options.push(n)
//         }
//     }
//     var t = u.doc,
//         i = {};
//     c.expr[":"].checked;
//     t.on("combobox.createctrl.wb-fieldflow", ".wb-fieldflow", function(e, t) {
//         i = t, a["wb-combobox"] || (a["wb-combobox"] = {}), a["wb-combobox"].parserUI = [], a["wb-combobox"].parserUI[e.target.id] = r, a["wb-combobox"].hideSourceUI = !0, e.target.classList.add("wb-combobox"), c(e.target).trigger("wb-init.wb-combobox"), c(e.target).data().wbFieldflowRegister = [c(e.target).before().get(0).id], c(e.target).attr("data-wb-fieldflow-origin", c(e.target).before().get(0).id)
//     }), t.on("wb.change", "[role=combobox]:not(.wb-fieldflow-init)", function(e, t) {
//         var a = e.currentTarget,
//             r = (c(a), t.item);
//         a.id || (a.id = u.getId());
//         var n, i = c("#" + r.bind).parentsUntil(".wb-fieldflow").parent();
//         if (i.length) {
//             n = i.get(0).id, r.source || n;
//             var o = c("#" + r.bind).data().wbFieldflow;
//             c.isArray(o) || (o = [o]);
//             for (var s = 0; s < o.length; s++) {
//                 var l = o[s],
//                     d = l.action + ".action.wb-fieldflow";
//                 l.provEvt = "#" + n, c("#" + n).trigger(d, l)
//             }
//         }
//     })
// }(jQuery, window, document, wb),
// function(f, e, i, h) {
//     "use strict";
//     var b, g, m, v, w, y = "wb-steps",
//         x = "." + y,
//         t = h.doc,
//         k = function(e, t, a, r) {
//             var n = i.createElement(e);
//             return n.className = ("prev" === t ? "btn btn-md btn-default" : "btn btn-md btn-primary") + " " + a, n.href = "#", n.setAttribute("aria-labelby", r), n.setAttribute("rel", t), n.setAttribute("role", "button"), n.innerHTML = r, n
//         },
//         A = function(e) {
//             e.addEventListener("click", function(e) {
//                 e.preventDefault();
//                 var t = !!this.className && this.className,
//                     a = t && -1 < t.indexOf("btn-primary"),
//                     r = !0;
//                 a && jQuery.validator && "undefined" !== jQuery.validator && (r = f("#" + this.parentElement.parentElement.parentElement.id).valid()), r ? (n(this.parentElement, a), a && this.parentElement.previousElementSibling.classList.remove("wb-steps-error")) : a && !r && this.parentElement.previousElementSibling.classList.add("wb-steps-error")
//             })
//         },
//         n = function(e, t) {
//             var a, r;
//             e && (e.classList.add("hidden"), (r = !!e.previousElementSibling && e.previousElementSibling) && r.classList.remove("wb-steps-active"), (a = t ? e.parentElement.nextElementSibling : e.parentElement.previousElementSibling) && (r = a.getElementsByTagName("LEGEND")[0], e = a.getElementsByTagName("DIV")[0], r && r.classList.add("wb-steps-active"), e && e.classList.remove("hidden")))
//         };
//     t.on("timerpoke.wb wb-init.wb-steps", x, function(e) {
//         var t = h.init(e, y, x);
//         if (t) {
//             t.id || (t.id = h.getId()), g || (b = h.i18n, g = {
//                 prv: b("prv"),
//                 nxt: b("nxt")
//             });
//             var a, r = t.getElementsByTagName("FORM")[0],
//                 n = r ? f(r).children("fieldset") : 0;
//             m && v && w || (m = k("A", "prev", "mrgn-rght-sm mrgn-bttm-md", g.prv), v = k("A", "next", "mrgn-bttm-md", g.nxt), (w = r.querySelector("input[type=submit], button[type=submit]")).classList.add("mrgn-bttm-md"));
//             for (var i = 0, o = n.length; i < o; i++) {
//                 var s, l = n[i],
//                     d = 0 === i,
//                     c = i === o - 1,
//                     u = l.firstElementChild,
//                     p = !(!u || "LEGEND" !== u.tagName) && u.nextElementSibling;
//                 if (p && "DIV" === p.tagName) a = !0, d || (s = m.cloneNode(!0), A(s), p.appendChild(s)), c ? p.appendChild(w) : (s = v.cloneNode(!0), A(s), p.appendChild(s)), l.classList.add("wb-tggle-fildst"), p.classList.add("hidden"), d && (u.classList.add("wb-steps-active"), p.classList.remove("hidden"))
//             }
//             r && a && f(r).children("input").hide()
//         }
//     }), h.add(x)
// }(jQuery, window, document, wb),
// function(u, n, o) {
//     "use strict";
//     var p, f, h, b, g, m, c, v, i, w = "wb-chtwzrd",
//         y = "." + w,
//         s = w + "-replace",
//         x = o.doc,
//         k = {},
//         A = {},
//         j = {
//             en: {
//                 "chtwzrd-send": "Send<span class='wb-inv'> reply and continue</span>",
//                 "chtwzrd-toggle": "Switch to wizard",
//                 "chtwzrd-notification": "Close chat notification",
//                 "chtwzrd-open": "Open chat wizard",
//                 "chtwzrd-minimize": "Minimize chat wizard",
//                 "chtwzrd-history": "Conversation history",
//                 "chtwzrd-reply": "Reply",
//                 "chtwzrd-controls": "Controls",
//                 "chtwzrd-toggle-basic": "Switch to basic form",
//                 "chtwzrd-waiting": "Waiting for message",
//                 "chtwzrd-answer": "You have answered:"
//             },
//             fr: {
//                 "chtwzrd-send": "Envoyer<span class='wb-inv'> la rÃ©ponse et continuer</span>",
//                 "chtwzrd-toggle": "Basculer vers l&apos;assistant",
//                 "chtwzrd-notification": "Fermer la notification de discussion",
//                 "chtwzrd-open": "Ouvrir l&apos;assistant de discussion",
//                 "chtwzrd-minimize": "RÃ©duire l&apos;assistant de discussion",
//                 "chtwzrd-history": "Historique de discussion",
//                 "chtwzrd-reply": "RÃ©pondre",
//                 "chtwzrd-controls": "ContrÃ´les",
//                 "chtwzrd-toggle-basic": "Basculer vers le formulaire",
//                 "chtwzrd-waiting": "En attente d&apos;un message",
//                 "chtwzrd-answer": "Vous avez rÃ©pondu&nbsp;:"
//             }
//         },
//         r = function(t) {
//             if (t.data(w + "-src"), t.data(w + "-src")) {
//                 var e = t.data(w + "-src");
//                 u.getJSON(e, function(e) {
//                     d(t, k = e), a(t)
//                 })
//             } else k = l(t), a(t)
//         },
//         a = function(e) {
//             e.removeClass("hidden wb-inv").addClass(w + "-basic"), p = !(A = {
//                 shortDelay: 500,
//                 mediumDelay: 750,
//                 longDelay: 1250
//             }), b = k.header.first, g = k.header.instructions ? k.header.instructions : "", h = k.header.defaultDestination, m = k.questions[b], f = k.header.formType ? k.header.formType : "dynamic", j = {
//                 send: (j = j[u("html").attr("lang") || "en"])["chtwzrd-send"],
//                 toggle: j["chtwzrd-toggle"],
//                 notification: j["chtwzrd-notification"],
//                 trigger: j["chtwzrd-open"],
//                 minimize: j["chtwzrd-minimize"],
//                 conversation: j["chtwzrd-history"],
//                 reply: j["chtwzrd-reply"],
//                 controls: j["chtwzrd-controls"],
//                 toggleBasic: j["chtwzrd-toggle-basic"],
//                 waiting: j["chtwzrd-waiting"],
//                 answer: j["chtwzrd-answer"]
//             }, S(e, k.header.title);
//             var t, a = u(y + "-basic"),
//                 r = u(y + "-bubble-wrap"),
//                 n = u(y + "-container"),
//                 i = u(".body", n),
//                 o = u(".history", n),
//                 s = u(".minimize", n),
//                 l = u(".basic-link", n),
//                 d = s,
//                 c = l;
//             C(a), E(r), l.on("click", function(e) {
//                 e.preventDefault();
//                 var t = u("legend:first", a);
//                 t.attr("tabindex", "0"), o.attr("aria-live", ""), I(a, "form"), n.stop().hide(), a.stop().show(function() {
//                     t.focus(), t.removeAttr("tabindex")
//                 }), u("body").removeClass(w + "-noscroll")
//             }), u(y + "-link").on("click", function(e) {
//                 e.preventDefault(), a.stop().hide(), t = u(":focus"), u(this).hasClass(w + "-bubble") || I(n, "wizard"), u(".bubble", r).removeClass("trans-pulse"), u("p", r).hide().removeClass("trans-left"), n.stop().show(), r.stop().hide(), u("body").addClass(w + "-noscroll"), o.length && u(".conversation", n).scrollTop(o[0].scrollHeight), p || T(i)
//             }), n.on("keydown", function(e) {
//                 9 === e.keyCode && (e.shiftKey ? d.is(":focus") && (e.preventDefault(), c.focus()) : c.is(":focus") && (e.preventDefault(), d.focus())), 27 === e.keyCode && s.click()
//             }), x.on("click", y + "-container .btn-send", function(e) {
//                 if ("submit" != u(this).attr("type")) {
//                     e.preventDefault();
//                     var t = u("input:checked", i);
//                     t.length || (t = u("input:first", i)).attr("checked", !0), q(i, z(t), !1)
//                 }
//             }), s.on("click", function(e) {
//                 e.preventDefault(), n.stop().hide(), r.stop().show(), u("body").removeClass(w + "-noscroll"), t.focus()
//             })
//         },
//         C = function(e) {
//             var r = u("form", e),
//                 t = u("fieldset", e),
//                 a = t.first();
//             "dynamic" == f && (a.addClass(w + "-first-q"), t.not(y + "-first-q").hide()), e.hide(), u("input", r).prop("checked", !1), r.append('<button class="btn btn-sm btn-link ' + w + '-link mrgn-rght-sm">' + j.toggle + "</button>"), u("input", r).on("change", function() {
//                 var e = z(u(this)),
//                     t = u("#" + e.qNext, r);
//                 if ("dynamic" == f) {
//                     var a = u(this).closest("fieldset");
//                     !t.is(":hidden") && a.next().attr("id") == t.attr("id") && "none" != e.qNext || a.nextAll("fieldset").hide().find("input").prop("checked", !1), "none" != e.qNext && u("#" + e.qNext).show(), "" != e.url && r.attr("action", e.url)
//                 }
//             })
//         },
//         E = function(t) {
//             var a = u("#wb-info");
//             if (t.fadeIn("slow"), a.addClass(w + "-mrgn"), a.length) {
//                 var e = function(e) {
//                     u(n).scrollTop() >= u(document).outerHeight() - u(n).outerHeight() - a.outerHeight() ? e.css({
//                         bottom: a.outerHeight() - (u(document).outerHeight() - u(n).outerHeight() - u(n).scrollTop()) + 30
//                     }) : e.css({
//                         bottom: 30
//                     })
//                 };
//                 e(t), u(n).on("resize scroll", function() {
//                     e(t)
//                 })
//             }
//             u(".notif", t).on("click", function() {
//                 u(y + "-link", t).click()
//             }), u(".notif-close", t).on("click", function(e) {
//                 e.preventDefault(), u(this).parent().hide(), t.focus()
//             })
//         },
//         l = function(e) {
//             var t = u("form", e),
//                 a = u("h2", e).first(),
//                 r = u("p:not(" + y + "-greetings):not(" + y + "-farewell)", t).first(),
//                 n = "btn-former-send",
//                 i = {};
//             i.header = (e.data(w), e.data(w) ? e.data(w) : {}), i.header.defaultDestination = t.attr("action"), i.header.name = t.attr("name"), i.header.method = t.attr("method"), i.header.form = {}, i.header.form.title = a.html(), i.header.title = N(a, i.header.form.title), i.header.greetings = u("p" + y + "-greetings", t).html(), i.header.farewell = u("p" + y + "-farewell", t).html(), i.header.form.sendBtn = u("input[type=submit]", t).length ? u("input[type=submit]", t).addClass(n).val() : u("button[type=submit]", t).addClass(n).html(), i.header.sendBtn = N(u("." + n, t), i.header.form.sendBtn), r.length && (i.header.form.instructions = r.html(), i.header.instructions = N(r, i.header.form.instructions));
//             var o = u("fieldset", e);
//             return i.questions = {}, void 0 !== i.header.first && i.header.first || (i.header.first = o.first().attr("id")), o.each(function() {
//                 var e = u("legend", u(this)),
//                     t = u("label", u(this)),
//                     a = u(this).attr("id"),
//                     r = u("input[type=radio]", u(this)).length ? "radio" : "checkbox",
//                     o = [],
//                     s = "";
//                 t.each(function(e) {
//                     var t = u("input", u(this)),
//                         a = {},
//                         r = t.attr("name"),
//                         n = t.data(w + "-url"),
//                         i = t.siblings("span:not(.no-" + w + ")").html();
//                     e || (s = r), a.content = i, a.value = t.val(), a.next = t.data(w + "-next"), n && (a.url = n), o.push(a)
//                 }), i.questions[a] = {}, i.questions[a].name = s, i.questions[a].input = r, i.questions[a].formLabel = e.html(), i.questions[a].label = N(e, i.questions[a].formLabel), i.questions[a].choices = o
//             }), i
//         },
//         S = function(e, t) {
//             e.after('<div class="' + w + '-bubble-wrap"><p class="trans-left"><span class="notif">' + t + '</span> <a href="#" class="notif-close" title="' + j.notification + '" aria-label="' + j.notification + '" role="button">Ã—</a></p><a href="#' + w + '-container" aria-controls="' + w + '-container" class="' + w + '-link bubble trans-pulse" role="button">' + j.trigger + "</a></div>"), e.next(y + "-bubble-wrap").after('<aside id="' + w + '-container" class="modal-content overlay-def ' + w + '-container"></aside>');
//             var a = u(y + "-container");
//             a.append('<header class="modal-header header"><h2 class="modal-title title">' + t + '</h2><button type="button" class="minimize" title="' + j.minimize + '"><span class="glyphicon glyphicon-chevron-down"></span></button></header>'), a.append('<form class="modal-body body" method="GET"></form>');
//             var r = u(".body", a);
//             r.append('<div class="conversation"><section class="history" aria-live="assertive"><h3 class="wb-inv">' + j.conversation + '</h3></section><section class="reply"><h3 class="wb-inv">' + j.reply + '</h3><div class="inputs-zone"></div></section><div class="form-params"></div></div>'), r.append('<section class="controls"><h3 class="wb-inv">' + j.controls + '</h3><div class="row"><div class="col-xs-12"><button class="btn btn-primary btn-block btn-send" type="button">' + j.send + '</button></div></div><div class="row"><div class="col-xs-12 text-center mrgn-tp-sm"><a href="#' + w + '-basic" class="btn btn-sm btn-link basic-link" role="button">' + j.toggleBasic + "</a></div></div></section>"), r.attr("name", k.header.name + "-chat"), r.attr("method", k.header.method), i = u(".btn-send ", r).html()
//         },
//         d = function(e, t) {
//             e.html("");
//             var a = "<h2>" + t.header.title + "</h2>",
//                 r = "<p>" + t.header.instructions + "</p>",
//                 n = ">" + t.header.sendBtn + "</button>";
//             t.header.form.title, a = "<h2 data-" + s + '="' + t.header.title + '">' + t.header.form.title + "</h2>", e.append(a + '<form class="mrgn-bttm-xl" action="' + t.header.defaultDestination + '" name="' + t.header.name + '" method="' + (t.header.method ? t.header.method : "GET") + '"></form>');
//             var i = u("form", e);
//             t.header.form.instructions, r = "<p data-" + s + '="' + t.header.instructions + '">' + t.header.form.instructions + "</p>", i.append('<p class="wb-chtwzrd-greetings wb-inv">' + t.header.greetings + "</p>" + r), u.each(t.questions, function(e, a) {
//                 var r = o.getId(),
//                     t = "<legend>" + a.label + "</legend>";
//                 a.formLabel, a.formLabel && (t = "<legend data-" + s + '="' + a.label + '">' + a.formLabel + "</legend>"), i.append('<fieldset id="' + e + '" class="' + r + '">' + t + '<ul class="list-unstyled mrgn-tp-md"></ul></fieldset>');
//                 var n = u("." + r, i);
//                 u.each(a.choices, function(e, t) {
//                     r = o.getId(), u("ul", n).append('<li><label><input type="' + a.input + '" value="' + t.value + '" id ="' + r + '" name="' + a.name + '" data-value="' + t.content + '" /> <span>' + t.content + "</span>"), u("#" + r, n).attr("data-" + w + "-next", t.next), t.url, t.url && u("#" + r, n).attr("data-" + w + "-url", t.url)
//                 })
//             }), t.header.form.sendBtn, n = " data-" + s + '="' + t.header.sendBtn + '">' + t.header.form.sendBtn + "</button>", i.append('<p class="wb-chtwzrd-farewell wb-inv">' + t.header.farewell + '</p><br/><button type="submit" class="btn btn-sm btn-primary"' + n), void 0 !== k.header.first && k.header.first || (k.header.first = u("fieldset", i).first().attr("id"))
//         },
//         I = function(e, t) {
//             if ("wizard" == t) {
//                 var a = u(".conversation", e);
//                 n.clearTimeout(c), n.clearTimeout(v), p = !1, h = k.header.defaultDestination, b = k.header.first, g = k.header.instructions ? k.header.instructions : "", m = k.questions[b], u(".history, .form-params", a).html(""), u(".btn-send", e).attr("type", "button").html(i), u(".history", a).attr("aria-live", "assertive"), T(u(".body", e))
//             } else {
//                 var r = u("fieldset", e);
//                 "dynamic" == f && (r.not(":first").hide(), u("input", r).prop("checked", !1))
//             }
//         },
//         T = function(n) {
//             var i = u(".history", n),
//                 o = u(".inputs-zone", n),
//                 s = u(".conversation", n),
//                 l = u(".btn-send", n),
//                 e = "" != b || "" != g || "last" == m ? "p" : "h4";
//             p = !0, l.prop("disabled", !0), o.html(""), i.append('<div class="row mrgn-bttm-md"><div class="col-xs-9"><' + e + ' class="mrgn-tp-0 mrgn-bttm-0"><span class="avatar"></span><span class="question"></span></' + e + "></div></div>");
//             var d = u(".question:last", i);
//             t(d), c = setTimeout(function() {
//                 "" != b ? (d.html(k.header.greetings), b = "", T(n)) : "" != g ? (d.html(g), g = "", T(n)) : "last" == m ? (d.html(k.header.farewell), l.attr("type", "submit").prop("disabled", !1).html(k.header.sendBtn + '&nbsp;<span class="glyphicon glyphicon-chevron-right small"></span>'), n.attr("action", h)) : (d.html(m.label), m.input = "radio", v = setTimeout(function() {
//                     o.append('<fieldset><legend class="wb-inv">' + m.label + '</legend><div class="row"><div class="col-xs-12"><ul class="' + ("inline" == k.header.displayForm ? "list-inline" : "list-unstyled") + ' mrgn-tp-sm choices"></ul></div></div></fieldset>');
//                     for (var e = 0; e < m.choices.length; e++) {
//                         var t = m.choices[e];
//                         u(".choices", o).append('<li><label><input type="' + m.input + '" value="' + t.value + '" name="' + m.name + '" data-' + w + '-next="' + t.next + '"' + (void 0 === t.url ? "" : " data-" + w + '-url="' + t.url + '"') + (e ? "" : "checked ") + "/> <span>" + t.content + "</span></label></li>")
//                     }
//                     l.prop("disabled", !1);
//                     var a = s[0].scrollHeight,
//                         r = u(".reply", n);
//                     r.length && r.outerHeight() + d.outerHeight() > s.innerHeight() && (a = i[0].scrollHeight - d.outerHeight() - 42), s.scrollTop(a)
//                 }, A.mediumDelay)), s.scrollTop(s[0].scrollHeight)
//             }, A.longDelay)
//         },
//         q = function(e, t) {
//             var a = o.getId(),
//                 r = u(".history", e);
//             r.append('<div class="row mrgn-bttm-md"><div class="col-xs-9 col-xs-offset-3"><div class="message text-right pull-right" id="' + a + '"><p class="mrgn-bttm-0"><span class="wb-inv">' + j.answer + " </span>" + t.value + "</p></div></div></div>"), u(".form-params", e).append('<input type="hidden" name="' + t.name + '" value="' + t.val + '" data-value="' + t.value + '" />'), p = !1, "" != t.url && (h = t.url);
//             var n = t.qNext,
//                 i = u("#" + a, r);
//             m = "none" == n ? "last" : k.questions[n], u(".btn-send", e).prop("disabled", !0), i.attr("tabindex", "0"), c = setTimeout(function() {
//                 u(".inputs-zone", e).remove("fieldset"), i.focus(), i.removeAttr("tabindex"), T(e)
//             }, A.shortDelay)
//         },
//         t = function(e) {
//             e.html('<span class="loader-typing" aria-label="' + j.waiting + '"><span class="loader-dot dot1"></span><span class="loader-dot dot2"></span><span class="loader-dot dot3"></span></span>')
//         },
//         N = function(e, t) {
//             var a = e.data(s);
//             return a || t
//         },
//         z = function(e) {
//             var t = e.data(w + "-next"),
//                 a = e.data(w + "-url");
//             return {
//                 qNext: t,
//                 name: e.attr("name"),
//                 val: e.val(),
//                 url: a || "",
//                 value: e.next().html()
//             }
//         };
//     x.on("timerpoke.wb wb-init.wb-chtwzrd", y, function(e) {
//         var t, a = o.init(e, w, y);
//         a && (t = u(a), r(t), o.ready(t, w))
//     }), o.add(y)
// }(jQuery, window, wb), $(document).on("do.wb-actionmng", "table[data-wb-urlmapping][data-wb5-bind]", function(e) {
//     var t = $(e.currentTarget);
//     t.one("draw.dt", function() {
//         t.trigger("refreshCtrl.wbtbl")
//     })
// });


        

//  })(window.jQuery, window.Drupal, document)

