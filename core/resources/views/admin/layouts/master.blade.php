<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ gs()->siteName($pageTitle ?? '') }}</title>

    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">

    @stack('style-lib')

    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
    @stack('style')
</head>

<body>
    @yield('content')




    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/bootstrap-toggle.min.js') }}"></script>


    @include('partials.notify')
    @stack('script-lib')

    <script src="{{ asset('assets/global/js/nicEdit.js') }}"></script>

    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>

    {{-- LOAD NIC EDIT --}}
    <script>
        "use strict";
        bkLib.onDomLoaded(function() {
            $(".nicEdit").each(function(index) {
                $(this).attr("id", "nicEditor" + index);
                new nicEditor({
                    fullPanel: true
                }).panelInstance('nicEditor' + index, {
                    hasPanel: true
                });
            });
        });
        (function($) {
            $(document).on('mouseover ', '.nicEdit-main,.nicEdit-panelContain', function() {
                $('.nicEdit-main').focus();
            });

            $('.breadcrumb-nav-open').on('click', function() {
                $(this).toggleClass('active');
                $('.breadcrumb-nav').toggleClass('active');
            });

            $('.breadcrumb-nav-close').on('click', function() {
                $('.breadcrumb-nav').removeClass('active');
            });

            if ($('.topTap').length) {
                $('.breadcrumb-nav-open').removeClass('d-none');
            }
        })(jQuery);
    </script>

    @stack('script')



    <script>
        (function() {
            function upgradeUrl(u) {
                if (!u || typeof u !== 'string') return u;
                if (window.location.protocol === 'https:' && u.indexOf('http://') === 0) {
                    return 'https://' + u.substring(7);
                }
                return u;
            }
            if (window.jQuery) {
                $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                    if (options.url) {
                        options.url = upgradeUrl(options.url);
                    }
                    var token = $('meta[name="csrf-token"]').attr('content');
                    if (token && (!options.headers || !options.headers['X-CSRF-TOKEN'])) {
                        jqXHR.setRequestHeader('X-CSRF-TOKEN', token);
                    }
                });
            }
            function fixForms() {
                if (window.location.protocol === 'https:') {
                    document.querySelectorAll('form[action^="http://"]').forEach(function(form) {
                        form.setAttribute('action', upgradeUrl(form.getAttribute('action')));
                    });
                }
            }
            document.addEventListener('DOMContentLoaded', fixForms);
            window.addEventListener('load', fixForms);
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.tagName === 'FORM') {
                    var act = e.target.getAttribute('action');
                    if (act && act.indexOf('http://') === 0 && window.location.protocol === 'https:') {
                        e.target.setAttribute('action', upgradeUrl(act));
                    }
                }
            }, true);
        })();
    </script>

</body>

</html>
