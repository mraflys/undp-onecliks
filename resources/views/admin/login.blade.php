<html lang="en">
  <head>
    <?php 
      $base_url = URL::to('theme/demo4/src/');
    ?>
    <meta charset="utf-8" />
    <title>{{ \App\GeneralHelper::app_configs()->name }} | Login</title>
    <meta name="description" content="{{ \App\GeneralHelper::app_configs()->short_description }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!--begin::Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">

    <!--end::Fonts -->

    <!--begin::Page Custom Styles(used by this page) -->
    <link href="<?=$base_url;?>/assets/css/pages/login/login-1.css" rel="stylesheet" type="text/css" />

    <!--end::Page Custom Styles -->

    <!--begin::Global Theme Styles(used by all pages) -->
    <link href="<?=$base_url;?>/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?=$base_url;?>/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />

    <!--end::Global Theme Styles -->

    <!--begin::Layout Skins(used by all pages) -->

    <!--end::Layout Skins -->
    <link rel="shortcut icon" href="<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->logo }}" />
    <script src="<?=asset('js/jquery-3.3.1.js');?>" type="text/javascript"></script>
  </head>

  <!-- end::Head -->

  <!-- begin::Body -->
  <body style="background-image: url(<?=$base_url;?>/assets/media/demos/demo4/header.jpg); background-position: center top; background-size: 100% 350px;" class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-page--loading">

    <!-- begin::Page loader -->

    <!-- end::Page Loader -->

    <!-- begin:: Page -->
    <div class="kt-grid kt-grid--ver kt-grid--root kt-page">
      <div class="kt-grid kt-grid--hor kt-grid--root  kt-login kt-login--v1" id="kt_login">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--desktop kt-grid--ver-desktop kt-grid--hor-tablet-and-mobile">

          <!--begin::Aside-->
          <div class="kt-grid__item kt-grid__item--order-tablet-and-mobile-2 kt-grid kt-grid--hor kt-login__aside" style="background-image: url(<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->banner }}); width:1010px">
            <div class="kt-grid__item">
              <a href="#" class="kt-login__logo">
                <img src="<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->logo }}">
              </a>
            </div>
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver">
              <div class="kt-grid__item kt-grid__item--middle">
                <h3 class="kt-login__title" style="background-color: #000">{{ \App\GeneralHelper::app_configs()->short_description }}</h3>
                <!-- <h4 class="kt-login__subtitle" style="background-color: #333">The ultimate Bootstrap & Angular 6 admin theme framework for next generation web apps.</h4> -->
              </div>
            </div>
            <div class="kt-grid__item">
              <div class="kt-login__info">
                <div class="kt-login__copyright">
                  &copy 2019 {{ \App\GeneralHelper::app_configs()->name }}
                </div>
                <div class="kt-login__menu">
                  <a href="{{ route('static_page.privacy') }}" target="_blank" class="kt-link">Privacy</a>
                  <a href="{{ route('static_page.legal') }}" target="_blank" class="kt-link">Legal</a>
                  <a href="{{ route('static_page.contact') }}" target="_blank" class="kt-link">Contact</a>
                </div>
              </div>
            </div>
          </div>

          <!--begin::Aside-->

          <!--begin::Content-->
          @include($mainpage)
        </div>
        <!--end::Content-->
      </div>
    </div>

    <!-- end:: Page -->

    <!-- begin::Global Config(global config for global JS sciprts) -->
    <script>
      var KTAppOptions = {
        "colors": {
          "state": {
            "brand": "#366cf3",
            "light": "#ffffff",
            "dark": "#282a3c",
            "primary": "#5867dd",
            "success": "#34bfa3",
            "info": "#36a3f7",
            "warning": "#ffb822",
            "danger": "#fd3995"
          },
          "base": {
            "label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
            "shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
          }
        }
      };
    </script>

    <!-- end::Global Config -->

    <!--begin::Global Theme Bundle(used by all pages) -->
    <script src="<?=$base_url;?>/assets/plugins/global/plugins.bundle.js" type="text/javascript"></script>
    <script src="<?=$base_url;?>/assets/js/scripts.bundle.js" type="text/javascript"></script>

    <!--end::Global Theme Bundle -->

    <!--begin::Page Scripts(used by this page) -->
    <script src="<?=$base_url;?>/assets/js/pages/custom/login/login-1.js" type="text/javascript"></script>

    <!--end::Page Scripts -->
  </body>

  <!-- end::Body -->
</html>