<div class="kt-footer  kt-footer--extended  kt-grid__item" id="kt_footer" style="background-image: url('<?=$base_url_theme;?>/assets/media/bg/bg-2.jpg');">
            <div class="kt-footer__top">
              <div class="kt-container ">
                <div class="row">
                  <div class="col-lg-4">&nbsp;</div>
                  <div class="col-lg-4 text-center">
                    <div class="kt-footer__section">
                      <h3 class="kt-footer__title">About</h3>
                      <div class="kt-footer__content">
                        {{ \App\GeneralHelper::app_configs()->description }}
                      </div>
                    </div>
                  </div>
                 <!--  <div class="col-lg-4">
                    <div class="kt-footer__section">
                      <h3 class="kt-footer__title">Quick Links</h3>
                      <div class="kt-footer__content">
                        <div class="kt-footer__nav">
                          <div class="kt-footer__nav-section">
                            <a href="#">General Reports</a>
                            <a href="#">Dashboart Widgets</a>
                            <a href="#">Custom Pages</a>
                          </div>
                          <div class="kt-footer__nav-section">
                            <a href="#">User Setting</a>
                            <a href="#">Custom Pages</a>
                            <a href="#">Intranet Settings</a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div> -->
                  <!-- <div class="col-lg-4">
                    <div class="kt-footer__section">
                      <h3 class="kt-footer__title">Get In Touch</h3>
                      <div class="kt-footer__content">
                        <form action="" class="kt-footer__subscribe">
                          <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter Your Email">
                            <div class="input-group-append">
                              <button class="btn btn-brand" type="button">Join</button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div> -->
              </div>
            </div>
            <div class="kt-footer__bottom">
              <div class="kt-container ">
                <div class="kt-footer__wrapper">
                  <div class="kt-footer__logo">
                    <a class="kt-header__brand-logo" href="?page=index&amp;demo=demo2">
                      <img alt="Logo" src="<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->logo }}" style="height: 90px; margin-top: 5px" class="kt-header__brand-logo-default"/>
                    </a>
                    <div class="kt-footer__copyright">
                      <i>UNITED NATION DEVELOPMENT PROGRAM</i><br>
                      2019 &nbsp;&copy;&nbsp; UNDP
                    </div>
                  </div>
                  <div class="kt-footer__menu">
                    <a href="{{ route('static_page.help') }}" target="_blank">Help</a>
                    <a href="{{ route('static_page.faq') }}" target="_blank">FAQ</a>
                    <a href="{{ route('static_page.contact') }}" target="_blank">Contact</a>
                  </div>
                </div>
              </div>
            </div>
          </div>