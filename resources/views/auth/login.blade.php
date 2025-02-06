<x-guest-layout>
  <x-slot:title>Planning - Login</x-slot:title>

  <body>
    <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="card-group d-block d-md-flex row">
              <div class="card col-md-7 p-4 mb-0">
                <div class="card-body">
                  <h1>{{ __('Login') }}</h1>
                  <x-inputs.form action="{{ route('login') }}">
                    <x-inputs.input-email property="email" label="<i class='fa-light fa-at me-3'></i>Email" :entity="null" required="true" autofocus="true" />

                    <x-inputs.input-password property="password" label="<i class='fa-light fa-unlock-keyhole me-3'></i>Mot de passe" :entity="null" required="true" />

                    <x-inputs.input-checkbox property="remember_me" label="{{ __('Remember me') }}" :entity="null" />

                    <div class="row">
                      <div class="col-6">
                        <button class="btn btn-primary px-4" type="submit ">{{ __('Log in') }}</button>
                      </div>
                      <div class="col-6 text-end">
                        <a href="{{ route('password.request') }}" class="px-0">{{ __('Forgot your password?') }}</a>
                      </div>
                    </div>
                  </x-inputs.form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</x-guest-layout>
