!function(t) {
	t.getUrlParam = function(t) {
		var i = new RegExp("(^|&)" + t + "=([^&]*)(&|$)"),
			e = window.location.search.substr(1).match(i);
		return null !== e ? unescape(e[2]) : null
	}, t.setUrlParam = function(t, i) {
		var e, a = window.location.href,
			n = new RegExp("([?|&]" + t + "=)(.+?)(&|$)");
		e = n.test(a) ? a.replace(n, "$1" + encodeURIComponent(i) + "$3") : -1 !== a.indexOf("?") ? a + "&" + t + "=" + i : a + "?" + t + "=" + i, window.location.href = e
	}, t.fn.hasAttr = function(t) {
		return void 0 !== this.attr(t)
	}, t.setCookie = function(t, i, e) {
		var a;
		if (e) {
			var n = new Date;
			n.setTime(n.getTime() + 24 * e * 60 * 60 * 1e3), a = "; expires=" + n.toGMTString()
		} else a = "";
		document.cookie = encodeURIComponent(t) + "=" + encodeURIComponent(i) + a + "; path=/"
	}, t.getCookie = function(t) {
		for (var i = encodeURIComponent(t) + "=", e = document.cookie.split(";"), a = 0; a < e.length; a++) {
			for (var n = e[a];
			" " === n.charAt(0);) n = n.substring(1, n.length);
			if (0 === n.indexOf(i)) return decodeURIComponent(n.substring(i.length, n.length))
		}
		return null
	}
}(jQuery), $(function() {
	function t(t, i) {
		i || (i = window.location.href), t = t.replace(/[[\]]/g, "\\$&");
		var e = new RegExp("[?&]" + t + "(=([^&#]*)|&|#|$)"),
			a = e.exec(i);
		return a ? a[2] ? decodeURIComponent(a[2].replace(/\+/g, " ")) : "" : null
	}
	var i = {
		disableAnimation: 0,
		init: function() {
			this.deviceType = /Mobile/.test(navigator.userAgent) || "mobile" === t("type") ? "mobile" : "pc", this.clientWidth = document.body.clientWidth, this.MAX_WIDTH = "pc" === this.deviceType ? 980 : this.clientWidth, this.initSetHome(), this.initFavorite(), this.initForward(), this.initBack(), this.initBackTop(), this.initRefresh(), this.initVideo(), this.initVideoBackground(), this.initShare(), this.initGoTop(), this.initViewHeight(), window.gallerys || (window.gallerys = {});
		},
		initSlideshow: function() {
			var t = document.querySelectorAll("[uk-slideshow]");
			t.forEach(function(t) {
				window.UIkit.slideshow(t).stopAutoplay()
			})
		},
		initShare: function() {
			window.socialShare('[data-item="share"]')
		},
		initGoTop: function() {
			$(window).scrollTop() >= 150 && $(".to-top").css({
				visibility: "visible"
			}), $(window).scroll(function() {
				// $(".to-top").css($(window).scrollTop() >= 150 ? {
				// 	visibility: "visible"
				// } : {
				// 	visibility: "hidden"
				// })
			}), $(document).on("click", ".to-top", function() {
				$("html, body").animate({
					scrollTop: 0
				}, "slow")
			})
		},
		initSetHome: function() {
			$(".win-homepage").click(function() {
				if (document.all) {
					document.body.style.behavior = "url(#default#homepage)";
					document.body.setHomePage(document.URL)
				} else {
					alert("???????????????????????????????????????")
				}
			})
		},
		initFavorite: function() {
			$(".win-favorite").click(function() {
				var sURL = document.URL;
				var sTitle = document.title;
				try {
					window.external.addFavorite(sURL, sTitle)
				} catch (e) {
					try {
						window.sidebar.addPanel(sTitle, sURL, "")
					} catch (e) {
						alert("??????????????????????????????Ctrl+D????????????")
					}
				}
			})
		},
		initForward: function() {
			$(".win-forward").click(function() {
				window.history.forward(1)
			})
		},
		initBack: function() {
			$(".win-back").click(function() {
				window.history.back(-1)
			})
		},
		initBackTop: function() {
			$(".win-backtop").click(function() {
				$("body,html").animate({
					scrollTop: 0
				}, 1000);
				return false
			})
		},
		initRefresh: function() {
			$(".win-refresh").click(function() {
				window.location.reload()
			})
		},
		initViewHeight: function() {
			var t = window.screen.availHeight;
			$("[data-viewport]").each(function() {
				var i = $(this).attr("data-viewport"),
					e = i > t ? i : t;
				$(this).css({
					height: e + "px"
				})
			})
		},
		initVideo: function() {
			var t = this;
			window.UIkit.util.on(document, "show", ".uk-lightbox", function(i) {
				var e = $(i.target).find(".uk-lightbox-iframe");
				e && !e.attr("src") && t.iframeSrc && e.attr("src", t.iframeSrc)
			}), window.UIkit.util.on(document, "hidden", ".uk-lightbox", function(i) {
				var e = $(i.target).find(".uk-lightbox-iframe");
				e && e.attr("src") && (t.iframeSrc = e.attr("src"), e.attr("src", ""))
			}), window.UIkit.util.on(document, "show", ".uk-modal", function(t) {
				var i = $(t.target).find(".uk-modal-iframe");
				i && i.attr("data-src") && i.attr("src", i.attr("data-src"))
			}), window.UIkit.util.on(document, "hidden", ".uk-modal", function(t) {
				var i = $(t.target).find(".uk-modal-iframe");
				i && i.attr("data-src") && i.attr("src", "")
			})
		},
		initVideoBackground: function() {
			var t = $("[data-video-background]");
			t.each(function() {
				var t = $(this),
					i = t.data("video-background"),
					e = i.split(","),
					a = (e[0], e[1], e[2]);
				t[0].playbackRate = a || 1, t[0].defaultPlaybackRate = a || 1
			})
		}
	};
	i.init(), window.page = i
});

$(function() {
	/* ???????????? */
	$(document).on('click','#msg',function() {
		if(!$(':input[name="username"]').val()) {
			layer.msg('?????????????????????'); return false;
		}
		const mobile = $(':input[name="mobile"]').val();
		if(!mobile) {
			layer.msg('?????????????????????'); return false;
		} else if (!/^1[3456789]\d{9}$/.test(mobile)) {
			layer.msg('?????????????????????'); return false;
		}
		if(!$(':input[name="company"]').val()) {
			layer.msg('?????????????????????'); return false;
		}
		const email = $(':input[name="email"]').val();
		if(!email) {
			layer.msg('?????????????????????'); return false;
		} else if (!/^([a-zA-Z]|\d)(\w|-)+@[a-zA-Z\d]+\.([a-zA-Z]{2,4})$/.test(email)) {
			layer.msg('??????????????????'); return false;
		}
		if(!$(':input[name="message"]').val()) {
			layer.msg('???????????????????????????'); return false;
		}
		$.post('/console/public/submit',
			$('#gbookform').serialize(),
			function(res) {
			if (res.success) {
				layer.alert(res.msg);
				$('#gbookform')[0].reset();
			} else {
				layer.msg(res.msg);
			}
		}, 'json');
	})

	/* ???????????? */
	const search = $('#search-btn')

	/* ????????????????????? */
	$(':input[name="keyword"]').bind('input porpertychange', function () {
		const keyword = $(this).val();
		/* ???????????????????????? */
		keyword ? search.removeAttr('disabled') : search.attr('disabled', 'disabled')
	})
})

/* ??????????????? */
$(function () {
	let html = ''
	const city = $('select[name="city"]')
	const district = $('select[name="district"]')
	const province = $('select[name="province"]')
	city.append(html)
	district.append(html)
	$.each(getRegion(), (idx, item) => {
		html += "<option data-code=" +item.cid+ " value=" +item.cid+ ">" +item.name+ "</options>"
	})
	province.append(html)
	province.change(function () {
		if ($(this).val() === '') return
		$("#inputCity option").remove()
		$("#inputDistrict option").remove()
		let code = $(this).find("option:selected").data('code').toString()
		let html = "<option value=''>--?????????--</option>"
		district.append(html)
		$.each(getRegion(code),function(idx, item){
			html += "<option data-code=" +item.cid+ " value=" +item.cid+ ">" +item.name+ "</options>"
		})
		city.append(html)
	})
	city.change(function () {
		if ($(this).val() === "") return
		$("#inputDistrict option").remove()
		let code = $(this).find("option:selected").data('code').toString()
		let html = "<option value=''>--?????????--</option>"
		$.each(getRegion(code),function(idx, item) {
			html += "<option data-code=" +item.cid+ " value=" +item.cid+ ">" +item.name+ "</options>"
		})
		district.append(html)
	})
})
/* ?????????????????? */
function getRegion(pid = 0) {
	let region
	$.ajax({
		async: false,
		url: '/region',
		data: { pid: pid },
		success: res => region = res?.data?.list ?? []
	})
	return region
}
