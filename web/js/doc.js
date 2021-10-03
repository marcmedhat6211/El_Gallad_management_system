(function ($) {
	"use strict";
	var DOC = {
		init: function () {
			this.anchors();
			this.clipboard();
			this.initScrollSpy();
			this.documentSearch();
		},
		anchors: function () {
			anchors.options = {
				icon: '#'
			};

			anchors.add('.bd-content > h2, .bd-content > h3, .bd-content > h4, .bd-content > h5');

			$('.bd-content').children('h2, h3, h4, h5').wrapInner('<span class="bd-content-title"></span>');
		},
		clipboard: function () {
			// Insert copy to clipboard button before .highlight
			$('figure.highlight, div.highlight').each(function () {
				var btnHtml = '<div class="bd-clipboard"><button type="button" class="btn-clipboard" title="Copy to clipboard">Copy</button></div>';
				$(this).before(btnHtml);
				$('.btn-clipboard')
					.tooltip()
					.on('mouseleave', function () {
						// Explicitly hide tooltip, since after clicking it remains
						// focused (as it's a button), so tooltip would otherwise
						// remain visible until focus is moved away
						$(this).tooltip('hide');
					});
			});

			var clipboard = new ClipboardJS('.btn-clipboard', {
				target: function (trigger) {
					return trigger.parentNode.nextElementSibling;
				}
			});

			clipboard.on('success', function (e) {
				$(e.trigger)
					.attr('title', 'Copied!')
					.tooltip('_fixTitle')
					.tooltip('show')
					.attr('title', 'Copy to clipboard')
					.tooltip('_fixTitle');

				e.clearSelection();
			});

			clipboard.on('error', function (e) {
				var modifierKey = /mac/i.test(navigator.userAgent) ? '\u2318' : 'Ctrl-';
				var fallbackMsg = 'Press ' + modifierKey + 'C to copy';

				$(e.trigger)
					.attr('title', fallbackMsg)
					.tooltip('_fixTitle')
					.tooltip('show')
					.attr('title', 'Copy to clipboard')
					.tooltip('_fixTitle');
			});
		},
		initScrollSpy: function () {
			var $toc_nav = $('.toc-nav');
			if ($toc_nav.length < 1) {
				return;
			}
			$('.toc-nav .nav-item > a').addClass('nav-link');
			$('body').scrollspy({
				target: '.toc-nav',
				offset: 200
			})

		},
		documentSearch: function () {
			var $input = $(".search-input");
			if ($input.length < 1) {
				return;
			}
			var $liResult;
			var liSelected;
			$input.on("keyup", function (e) {
				var $ul = $('.bd-sidenav');
				var $li = $ul.find("li");
				var i;
				var array = [];
				var filter = $(this).val().toUpperCase();
				var $result = $(".search-result");

				if (e.which !== 40 && e.which !== 38) {
					$result.addClass("show");
					for (i = 0; i < $li.length; i++) {
						var a = $li[i].getElementsByTagName("a")[0];
						var txtValue = a.textContent || a.innerText;
						if (txtValue.toUpperCase().indexOf(filter) > -1 && filter !== "" && a.href.indexOf('#') === -1) {
							array.push({link: a.href, text: txtValue});
							$result.html("");
						}
					}
					for (i = 0; i < array.length; i++) {
						$result.append("<li class='list-group-item'><a href='" + array[i].link + "' class='text-dark'>" + array[i].text + "</a> </li>")
					}

				}

				var selected;
				$liResult = $result.find('li');
				if (e.which === 13) {
					window.location.href = liSelected.find('a').attr('href');
				}
				if (e.which === 40) {
					if (!liSelected) {
						liSelected = $liResult.eq(0);
						liSelected.addClass('active');
						selected = $liResult.eq(0).text();
					} else {
						liSelected.removeClass('active');
						var next = liSelected.next();
						if (next.length > 0) {
							liSelected = next.addClass('active');
							selected = next.text();

						} else {
							liSelected = $liResult.eq(0).addClass('active');
							selected = $liResult.eq(0).text();
						}
					}
					if (selected !== '') {
						$(this).val(selected.replace(/[ {4}\t\n\r]/gm, ' ').replace(/\s+/g, " "));
					}
				} else if (e.which === 38) {
					if (liSelected) {
						liSelected.removeClass('active');
						next = liSelected.prev();
						if (next.length > 0) {
							liSelected = next.addClass('active');
							selected = next.text();

						} else {

							liSelected = $liResult.last().addClass('active');
							selected = $liResult.last().text()
						}
					} else {

						liSelected = $liResult.last().addClass('active');
						selected = $liResult.last().text()
					}
					if (selected !== '') {
						$(this).val(selected.replace(/[ {4}\t\n\r]/gm, ' ').replace(/\s+/g, " "));
					}
				}


			});
			$(document).on('click', function () {
				$('.search-result').removeClass("show");
			});


		},
	};
	$(document).ready(function () {
		DOC.init();
	});
})(jQuery);
