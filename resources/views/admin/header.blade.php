<div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
  <div class="kt-header-mobile__logo">
    <a href="{{ (session('user_role_id') != 3) ? route('myrequests.home') : route('auth_page_test') }}">
      <img alt="Logo" src="<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->logo }}" />
    </a>
  </div>
  <div class="kt-header-mobile__toolbar">
    <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
    <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more-1"></i></button>
  </div>
</div>
  
    <?php 
      $is_login = \Auth::user() ? true : false;
    ?>
    <!-- end:: Header Mobile -->
    <div class="kt-grid kt-grid--hor kt-grid--root">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

          <!-- begin:: Header -->
          <div id="kt_header" class="kt-header  kt-header--fixed " data-ktheader-minimize="on">
            <div class="kt-container ">
              <!-- begin:: Brand -->
              <div class="kt-header__brand   kt-grid__item" id="kt_header_brand">
                <a class="kt-header__brand-logo" href="{{ (session('user_role_id') != 3) ? route('myrequests.home') : route('auth_page_test') }}">
                  <img alt="Logo" src="<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->logo }}" style="height: 90px; margin-top: 5px" class="kt-header__brand-logo-default"/>

                  <img alt="Logo" src="<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->logo }}" style="height: 90px; margin-top: 5px" class="kt-header__brand-logo-sticky" />
                </a>
              </div>

              <!-- end:: Brand -->

              <!-- begin: Header Menu -->
              <button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
              <div class="kt-header-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_header_menu_wrapper">
                <div id="kt_header_menu" class="kt-header-menu kt-header-menu-mobile ">
                  @if ($is_login)
                  <ul class="kt-menu__nav ">
                    <li class="kt-menu__item  kt-menu__item--open kt-menu__item--here kt-menu__item--submenu kt-menu__item--rel kt-menu__item--open kt-menu__item--here" data-ktmenu-submenu-toggle="click" aria-haspopup="true">
                      <a href="{{ (session('user_role_id') != 3) ? route('myrequests.home') : route('auth_page_test') }}" class="kt-menu__link"><span class="kt-menu__link-text">Home</span><i class="kt-menu__ver-arrow la la-angle-right"></i></a>
                    </li>
                    <?php
                      $global_master_menus = ['agency_units' => "Agency Unit", 'countries'=> 'Country', 
                      'currencies' => 'Currency', 'holidays' => 'Holiday Calendar', 'coas' => 'COA', 'expenditur' => 'Expenditure'];
                      $service_master_menus = ['service_units' => 'Service Unit', 'service_list' => 'Service List', 'projects' => 'Payroll Expenditure'];
                      $services_admin_menus = ['myservices' => 'My List'];
                      $security_master_menus = ['users' => 'User', 'soft_deleted_users' => 'Deleted User', 'app_configs' => 'App Config'];
                      $admin_menus = ['Global Master Data' => $global_master_menus, 'Agency Master Data' => $service_master_menus, 
                      'Security' => $security_master_menus,'My Service' => $services_admin_menus];

                      $my_request_menus = ['myrequests.create'=> 'New Request', 
                      'myrequests.draft' => 'My Draft', 
                      'myrequests.ongoing' => 'My On Going', 
                      'myrequests.history' => 'My History', 
                      'myrequests.tracking' => 'My Tracking'];
                      
                      $billing_menus = ['mybillings.index' => 'Billing'];

                      $report_menus = ['myreport.index' => 'Report'];
                      $profile_menus = ['myprofile.show' => 'Profile'];
                      $pricelist_menus = ['mypricelist.index' => 'Pricelist'];
                      $services_menus = ['myservices.index' => 'My List', 'myservices.tracking' => "My Tracking"];

                      switch (session('user_menu')) {
                        case 'service_unit':
                          $member_menus = ['My Request' => $my_request_menus, 'My Service' => $services_menus, 'Billing' => $billing_menus, 
                          'Pricelist' => $pricelist_menus, 'Report' => $report_menus, 'Profile' => $profile_menus];
                          break;
                        case 'finance':
                          $billing_menus['mybillings.glje_index'] = 'GLJE';
                          $member_menus = ['My Request' => $my_request_menus, 'My Service' => $services_menus, 'Billing' => $billing_menus, 
                          'Pricelist' => $pricelist_menus, 'Report' => $report_menus, 'Profile' => $profile_menus];
                          break;
                        case 'management':
                          $service_master_menus = ['service_units.index' => 'My Service Unit', 'service_list.index' => 'My Service List'];
                          $member_menus = ['UNDP Service' => $service_master_menus, 'Pricelist' => $pricelist_menus, 
                          'Report' => $report_menus, 'Profile' => $profile_menus];
                          break;
                        case 'requester':
                          $member_menus = ['My Request' => $my_request_menus, 'Pricelist' => $pricelist_menus, 'Report' => $report_menus, 
                          'Profile' => $profile_menus];
                          break;
                        default:
                          $member_menus = ['Profile' => $profile_menus];
                          break;
                      }
                      
                    ?>
                    @if (session('user_role_id') == 3)
                      @foreach($admin_menus as $master_name => $menus)
                        <li class="kt-menu__item  kt-menu__item--open kt-menu__item--here kt-menu__item--submenu kt-menu__item--rel kt-menu__item--open kt-menu__item--here" data-ktmenu-submenu-toggle="click" aria-haspopup="true">
                          <a href="javascript:;" class="kt-menu__link kt-menu__toggle"><span class="kt-menu__link-text">{{ $master_name }}</span><i class="kt-menu__ver-arrow la la-angle-right"></i></a>
                          <div class="kt-menu__submenu kt-menu__submenu--classic kt-menu__submenu--left">
                            <ul class="kt-menu__subnav">
                              @foreach($menus as $key => $menu)
                                <li class="kt-menu__item " aria-haspopup="true"><a href="{{ route($key.'.index') }}" class="kt-menu__link "><i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span class="kt-menu__link-text">{{ $menu }}</span></a></li>
                              @endforeach 
                            </ul>
                          </div>
                        </li>
                      @endforeach
                    @else
                      @foreach($member_menus as $master_name => $menus)
                        <li class="kt-menu__item  kt-menu__item--open kt-menu__item--here kt-menu__item--submenu kt-menu__item--rel kt-menu__item--open kt-menu__item--here" data-ktmenu-submenu-toggle="click" aria-haspopup="true">
                        @if (count($menus) == 1)
                          <?php 
                            $menu_name = array_values($menus)[0];
                            $menu_url = array_keys($menus)[0];
                          ?>
                          <a href="{{ route($menu_url) }}" class="kt-menu__link"><span class="kt-menu__link-text">{{ $menu_name }}</span><i class="kt-menu__ver-arrow la la-angle-right"></i></a>
                        @else
                            <a href="javascript:;" class="kt-menu__link kt-menu__toggle"><span class="kt-menu__link-text">{{ $master_name }}</span><i class="kt-menu__ver-arrow la la-angle-right"></i></a>
                            <div class="kt-menu__submenu kt-menu__submenu--classic kt-menu__submenu--left">
                              <ul class="kt-menu__subnav">
                                @foreach($menus as $key => $menu)
                                  <li class="kt-menu__item " aria-haspopup="true"><a href="{{ route($key) }}" class="kt-menu__link "><i class="kt-menu__link-bullet kt-menu__link-bullet--dot"><span></span></i><span class="kt-menu__link-text">{{ $menu }}</span></a></li>
                                @endforeach 
                              </ul>
                            </div>
                        @endif
                        </li>
                      @endforeach                                                 
                    @endif 
                  </ul>
                  @endif
                </div>
              </div>
              <!-- end: Header Menu -->

              <!-- begin:: Header Topbar -->
              <div class="kt-header__topbar kt-grid__item">
                <!--begin: User bar -->
                <div class="kt-header__topbar-item kt-header__topbar-item--user">
                  @if ($is_login)
                  <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                    <span class="kt-header__topbar-welcome">Hi,</span>
                    <span class="kt-header__topbar-username">{{ (\Auth::user()) ? \Auth::user()->first_name  : '' }}</span>
                    <span class="kt-header__topbar-icon"><b>{{ (\Auth::user()) ? ucwords(substr(\Auth::user()->first_name, 0, 1)) : '' }}</b></span>
                    <img alt="Pic" src="<?=$base_url_theme;?>/assets/media/users/300_21.jpg" class="kt-hidden" />
                  </div>
                  @endif
                  <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim dropdown-menu-xl">
                    <!--begin: Head -->
                    <div class="kt-user-card kt-user-card--skin-dark kt-notification-item-padding-x" 
                      style="background-image: url(<?=$base_url_theme;?>/assets/media/misc/bg-1.jpg)">
                      <div class="kt-user-card__avatar">
                        <img class="kt-hidden" alt="Pic" src="<?=$base_url_theme;?>/assets/media/users/300_25.jpg" />
                        <!--use below badge element instead the user avatar to display username's first letter(remove kt-hidden class to display it) -->
                      </div>
                      <div class="kt-user-card__name">
                        {{ (\Auth::user()) ? \Auth::user()->person_name : '' }}
                      </div>
                      <div class="kt-user-card__badge">
                        <a href="{{ route('myprofile.show') }}" class="btn btn-success btn-sm btn-bold btn-font-md">My Profile</a>
                        <a href="{{ route('logout') }}" class="btn btn-danger btn-sm btn-bold btn-font-md">Sign out</a>
                      </div>
                    </div>
                    <div>
                      <span class="alert alert-info text-bold" style="margin-bottom: 0"><b> 
                        <i class='fa fa-user'></i>  &nbsp; {{ session('user_agency_unit_name') }}</b></span>
                    </div>
                    <!--end: Head -->
                  </div>
                </div>
                <!--end: User bar -->
              </div>
                <!-- end:: Header Topbar -->
            </div>
          </div>