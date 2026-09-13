(function($) {
    "use strict";
    var $wrapper = $('.main-wrapper');
    var $pageWrapper = $('.page-wrapper');
    var $slimScrolls = $('.slimscroll');
    var Sidemenu = function() {
        this.$menuItem = $('#sidebar-menu a');
    };

    function init() {
        var $this = Sidemenu;
        $('#sidebar-menu a').on('click', function(e) {
            var $link = $(this);
            var $submenu = $link.next('ul');
            var isSubmenuToggle = $link.parent().hasClass('submenu') && $submenu.length > 0;

            // Only block navigation when this link actually opens a nested submenu.
            // Leaf links (Dashboard, Reports, etc.) must navigate normally.
            if (!isSubmenuToggle) {
                return;
            }

            e.preventDefault();
            var $parent = $link.parent('li.submenu');
            var isOpen = $parent.hasClass('is-open');

            $link.closest('ul').children('li.submenu.is-open').not($parent).each(function () {
                $(this).removeClass('is-open').children('a').removeClass('subdrop');
            });

            if (!isOpen) {
                $parent.addClass('is-open');
                $link.addClass('subdrop');
            } else {
                $parent.removeClass('is-open');
                $link.removeClass('subdrop');
            }
        });
        var path = window.location.pathname.replace(/\/$/, '');
        $('#sidebar-menu a[href]').each(function () {
            var href = $(this).attr('href');
            if (!href || href.indexOf('javascript') === 0) {
                return;
            }
            try {
                var linkPath = new URL(href, window.location.origin).pathname.replace(/\/$/, '');
                if (linkPath.length > 1 && path === linkPath) {
                    $(this).addClass('active');
                    var $submenuLi = $(this).closest('li.submenu');
                    if ($submenuLi.length) {
                        $submenuLi.addClass('active is-open')
                            .children('a').addClass('subdrop');
                    }
                }
            } catch (e) {}
        });
        $('#sidebar-menu li.submenu.active').each(function () {
            var $li = $(this);
            $li.addClass('is-open');
            $li.children('a').addClass('subdrop');
        });
    }
    init();
    $(document).on('click', '#mobile_btn', function() {
        $wrapper.toggleClass('slide-nav');
        $('.sidebar-overlay').toggleClass('opened');
        if ($wrapper.hasClass('slide-nav')) {
            $('html').addClass('menu-opened');
        } else {
            $('html').removeClass('menu-opened');
        }
        return false;
    });
    // Fix: Close sidebar when overlay is clicked
    $(document).on('click', '.sidebar-overlay', function() {
        $wrapper.removeClass('slide-nav');
        $('.sidebar-overlay').removeClass('opened');
        $('html').removeClass('menu-opened');
    });
    if ($('.toggle-password').length > 0) {
        $(document).on('click', '.toggle-password', function() {
            $(this).toggleClass("feather-eye feather-eye-off");
            var input = $(".pass-input");
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    if ($('.reg-toggle-password').length > 0) {
        $(document).on('click', '.reg-toggle-password', function() {
            $(this).toggleClass("feather-eye feather-eye-off");
            var input = $(".pass-confirm");
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    $(document).on("click", ".logo-hide-btn", function() {
        $(this).parent().hide();
    });
    if ($('.page-wrapper').length > 0) {
        var height = $(window).height();
        $(".page-wrapper").css("min-height", height);
    }
    $(window).resize(function() {
        if ($('.page-wrapper').length > 0) {
            var height = $(window).height();
            $(".page-wrapper").css("min-height", height);
        }
    });
    if ($('.select').length > 0 && $.fn.select2) {
        $('.select').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });
    }
    if ($('#editor').length > 0) {
        ClassicEditor.create(document.querySelector('#editor'), {
            toolbar: {
                items: ['heading', '|', 'fontfamily', 'fontsize', '|', 'alignment', '|', 'fontColor', 'fontBackgroundColor', '|', 'bold', 'italic', 'strikethrough', 'underline', 'subscript', 'superscript', '|', 'link', '|', 'outdent', 'indent', '|', 'bulletedList', 'numberedList', 'todoList', '|', 'code', 'codeBlock', '|', 'insertTable', '|', 'uploadImage', 'blockQuote', '|', 'undo', 'redo'],
                shouldNotGroupWhenFull: true
            }
        }).then(editor => {
            window.editor = editor;
        }).catch(err => {
            console.error(err.stack);
        });
    }
    $(".settings-form").on('click', '.trash', function() {
        $(this).closest('.links-cont').remove();
        return false;
    });

    if ($('.datetimepicker').length > 0 && $.fn.datetimepicker) {
        $('.datetimepicker').datetimepicker({
            format: 'DD-MM-YYYY',
            icons: {
                up: "fas fa-angle-up",
                down: "fas fa-angle-down",
                next: 'fas fa-angle-right',
                previous: 'fas fa-angle-left'
            }
        });
        $('.datetimepicker').on('dp.show', function() {
            $(this).closest('.table-responsive').removeClass('table-responsive').addClass('temp');
        }).on('dp.hide', function() {
            $(this).closest('.temp').addClass('table-responsive').removeClass('temp')
        });
    }
    if ($('[data-toggle="tooltip"]').length > 0) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    if ($('.datatable').length > 0 && $.fn.DataTable) {
        $('.datatable').DataTable({
            "bFilter": false,
        });
    }
    if ($('.datatables').length > 0 && $.fn.DataTable) {
        $('.datatables').DataTable({
            "bFilter": true,
        });
    }
    if ($('.zoom-screen .header-nav-list').length > 0) {
        $('.zoom-screen .header-nav-list').on('click', function(e) {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        })
    }
    $(document).on('click', '#check_all', function() {
        $('.checkmail').click();
        return false;
    });
    if ($('.checkmail').length > 0) {
        $('.checkmail').each(function() {
            $(this).on('click', function() {
                if ($(this).closest('tr').hasClass('checked')) {
                    $(this).closest('tr').removeClass('checked');
                } else {
                    $(this).closest('tr').addClass('checked');
                }
            });
        });
    }
    $(document).on('click', '.mail-important', function() {
        $(this).find('i.fa').toggleClass('fa-star').toggleClass('fa-star-o');
    });
    if ($('.summernote').length > 0) {
        $('.summernote').summernote({
            height: 200,
            minHeight: null,
            maxHeight: null,
            focus: false
        });
    }
    if ($slimScrolls.length > 0) {
        $slimScrolls.slimScroll({
            height: 'auto',
            width: '100%',
            position: 'right',
            size: '7px',
            color: '#ccc',
            allowPageScroll: false,
            wheelStep: 10,
            touchScrollStep: 100
        });
        var wHeight = $(window).height() - 60;
        $slimScrolls.height(wHeight);
        $('.sidebar .slimScrollDiv').height(wHeight);
        $(window).resize(function() {
            var rHeight = $(window).height() - 60;
            $slimScrolls.height(rHeight);
            $('.sidebar .slimScrollDiv').height(rHeight);
        });
    }
    $(document).on('click', '#toggle_btn', function() {
        if ($(window).width() > 991) {
            if ($('body').hasClass('mini-sidebar')) {
                $('body').removeClass('mini-sidebar');
                $('li.submenu .subdrop').parent('li.submenu').addClass('is-open');
            } else {
                $('body').addClass('mini-sidebar');
                $('li.submenu.is-open').removeClass('is-open');
            }
        } else {
            // Mobile: fallback to mobile sidebar logic
            $wrapper.toggleClass('slide-nav');
            $('.sidebar-overlay').toggleClass('opened');
            if ($wrapper.hasClass('slide-nav')) {
                $('html').addClass('menu-opened');
            } else {
                $('html').removeClass('menu-opened');
            }
        }
        setTimeout(function() {}, 300);
        return false;
    });
    // Show submenu tooltips on hover in mini-sidebar mode
    $(document).on('mouseenter', '.mini-sidebar .sidebar-menu > ul > li', function() {
        if ($('body').hasClass('mini-sidebar')) {
            $(this).find('ul').css({display: 'block', position: 'absolute', left: '100%', top: 0, zIndex: 2000});
        }
    });
    $(document).on('mouseleave', '.mini-sidebar .sidebar-menu > ul > li', function() {
        if ($('body').hasClass('mini-sidebar')) {
            $(this).find('ul').css({display: 'none'});
        }
    });
    $(document).on('mouseover', function(e) {
        e.stopPropagation();
        if ($('body').hasClass('mini-sidebar') && $('#toggle_btn').is(':visible')) {
            var targ = $(e.target).closest('.sidebar').length;
            if (targ) {
                $('body').addClass('expand-menu');
                $('.subdrop + ul').slideDown();
            } else {
                $('body').removeClass('expand-menu');
                $('.subdrop + ul').slideUp();
            }
            return false;
        }
    });

    function animateElements() {
        $('.circle-bar1').each(function() {
            var elementPos = $(this).offset().top;
            var topOfWindow = $(window).scrollTop();
            var percent = $(this).find('.circle-graph1').attr('data-percent');
            var animate = $(this).data('animate');
            if (elementPos < topOfWindow + $(window).height() - 30 && !animate) {
                $(this).data('animate', true);
                $(this).find('.circle-graph1').circleProgress({
                    value: percent / 100,
                    size: 400,
                    thickness: 30,
                    fill: {
                        color: '#6e6bfa'
                    }
                });
            }
        });
        $('.circle-bar2').each(function() {
            var elementPos = $(this).offset().top;
            var topOfWindow = $(window).scrollTop();
            var percent = $(this).find('.circle-graph2').attr('data-percent');
            var animate = $(this).data('animate');
            if (elementPos < topOfWindow + $(window).height() - 30 && !animate) {
                $(this).data('animate', true);
                $(this).find('.circle-graph2').circleProgress({
                    value: percent / 100,
                    size: 400,
                    thickness: 30,
                    fill: {
                        color: '#6e6bfa'
                    }
                });
            }
        });
        $('.circle-bar3').each(function() {
            var elementPos = $(this).offset().top;
            var topOfWindow = $(window).scrollTop();
            var percent = $(this).find('.circle-graph3').attr('data-percent');
            var animate = $(this).data('animate');
            if (elementPos < topOfWindow + $(window).height() - 30 && !animate) {
                $(this).data('animate', true);
                $(this).find('.circle-graph3').circleProgress({
                    value: percent / 100,
                    size: 400,
                    thickness: 30,
                    fill: {
                        color: '#6e6bfa'
                    }
                });
            }
        });
    }
    if ($('.circle-bar').length > 0) {
        animateElements();
    }
    $(window).scroll(animateElements);
    $(window).on('load', function() {
        if ($('#loader').length > 0) {
            $('#loader').delay(350).fadeOut('slow');
            $('body').delay(350).css({
                'overflow': 'visible'
            });
        }
    })
    $('.app-listing .selectBox').on("click", function() {
        $(this).parent().find('#checkBoxes').fadeToggle();
        $(this).parent().parent().siblings().find('#checkBoxes').fadeOut();
    });
    $('.invoices-main-form .selectBox').on("click", function() {
        $(this).parent().find('#checkBoxes-one').fadeToggle();
        $(this).parent().parent().siblings().find('#checkBoxes-one').fadeOut();
    });
    if ($('.SortBy').length > 0) {
        var show = true;
        var checkbox1 = document.getElementById("checkBox");
        $('.selectBoxes').on("click", function() {
            if (show) {
                checkbox1.style.display = "block";
                show = false;
            } else {
                checkbox1.style.display = "none";
                show = true;
            }
        });
    }

    $(".links-info-one").on('click', '.service-trash', function() {
        $(this).closest('.links-cont').remove();
        return false;
    });
    
   
    if ($('#summernote').length > 0) {
        $('#summernote').summernote({
            height: 300,
            minHeight: null,
            maxHeight: null,
            focus: true
        });
    }
    if ($('.counter').length > 0) {
        $('.counter').counterUp({
            delay: 20,
            time: 2000
        });
    }
    if ($('#timer-countdown').length > 0) {
        $('#timer-countdown').countdown({
            from: 180,
            to: 0,
            movingUnit: 1000,
            timerEnd: undefined,
            outputPattern: '$day Day $hour : $minute : $second',
            autostart: true
        });
    }
    if ($('#timer-countup').length > 0) {
        $('#timer-countup').countdown({
            from: 0,
            to: 180
        });
    }
    if ($('#timer-countinbetween').length > 0) {
        $('#timer-countinbetween').countdown({
            from: 30,
            to: 20
        });
    }
    if ($('#timer-countercallback').length > 0) {
        $('#timer-countercallback').countdown({
            from: 10,
            to: 0,
            timerEnd: function() {
                this.css({
                    'text-decoration': 'line-through'
                }).animate({
                    'opacity': .5
                }, 500);
            }
        });
    }
    if ($('#timer-outputpattern').length > 0) {
        $('#timer-outputpattern').countdown({
            outputPattern: '$day Days $hour Hour $minute Min $second Sec..',
            from: 60 * 60 * 24 * 3
        });
    }
    if ($('[data-bs-toggle="tooltip"]').length > 0) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    }
    if ($('.popover-list').length > 0) {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        })
    }
    if ($('.clipboard').length > 0) {
        var clipboard = new Clipboard('.btn');
    }
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
})(jQuery);