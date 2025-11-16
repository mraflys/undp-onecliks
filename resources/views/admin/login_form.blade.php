<div class="kt-grid__item kt-grid__item--fluid  kt-grid__item--order-tablet-and-mobile-1  kt-login__wrapper">

  <!--begin::Head-->
  <div class="kt-login__head">
    <span class="kt-login__signup-label">Don't have an account yet?</span>&nbsp;&nbsp;
    <a href="{{ route('register') }}" class="kt-link kt-login__signup-link">Sign Up!</a>
  </div>
  <!--end::Head-->
  <!--begin::Body-->
  <div class="kt-login__body">

    <!--begin::Signin-->
    <div class="kt-login__form">
        <div class="kt-divider" style="margin-bottom: 25px">
          <span></span>
          <span>Please provide your <b>username</b> and <b> password </b> to Sign-in.</span>
          <span></span>
        </div>
      @if (session('message_error'))
        <p class="text-center text-danger">Username or Password is not valid!</p>
      @endif
      @if (session('message_success'))
        <p class="text-center text-success">{{ session('message_success') }}</p>
      @endif
      <!--begin::Form-->
      <form class="kt-form" action="{{ route('auth.login') }}" method="POST" id="kt_login_form">
        @csrf
        <div class="form-group">
          <input class="form-control" type="text" placeholder="Username" name="username" autocomplete="off">
        </div>
        <div class="form-group pb-3">
          <input class="form-control" type="password" placeholder="Password" name="password" autocomplete="off">
        </div>
        <div class="pt-1 pb-1 overflow-auto" style="width: auto; height: 150px;">
          <b>DISCLAIMER</b>
          <p style="font-size: 11px">
            This is a United Nations Development Programme (UNDP) computer system, the use of which is governed by UNDP’s Policy on Use of ICT Resources. This computer system, including all related equipment, networks, and network devices (specifically including Internet access) are provided only for authorized UNDP use. UNDP computer systems may be monitored for all lawful purposes. This includes ensuring that their use is authorized, for management of the system, to facilitate protection against unauthorized access, and to verify security procedures, survivability, and operational security. During monitoring, information may be examined, recorded, copied and used for authorized purposes. All information, including personal information, placed or sent over this system may be monitored. Use of this UNDP computer system, authorized or unauthorized, constitutes consent to monitoring of this system. Unauthorized use may subject you to criminal prosecution. Evidence of unauthorized use collected during monitoring may be used for administrative, criminal, or other adverse action. Use of this system constitutes consent to monitoring for these purposes.
          </p>
        </div>
        <div class="pt-3">
          <label for="disclaimer-agree">
            <input type="checkbox" id="disclaimer-agree" required>
            I have read and agree to comply with the above disclaimer.
          </label>
        </div>
        <!--begin::Action-->
        <div class="kt-login__actions">
          <a href="#" class="kt-link kt-login__link-forgot" data-toggle="modal" data-target="#exampleModal">
            Forgot Password ?
          </a>
          <button type="submit" class="btn btn-primary btn-elevate kt-login__btn-primary">Sign In</button>
        </div>
        <!--end::Action-->
      </form>

      <!--end::Form-->
    <!--end::Signin-->
  </div>

  <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Forgot Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('forgot_password') }}" method="POST">
          {{ csrf_field() }}
          <input type="email" name="email" class="form-control" placeholder="input your email"><br>
          <button type="submit" class="btn btn-danger btn-block">Send Request</button>
        </form>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

  <!--end::Body-->
</div>