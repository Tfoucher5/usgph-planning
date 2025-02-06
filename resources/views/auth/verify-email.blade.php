<!DOCTYPE html>

<html lang="fr">

<head>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card-group d-block d-md-flex row">
            <div class="card col-md-7 p-4 mb-0">
              <div class="card-body">
                <div class="mb-4 text-sm text-gray-600">
                  {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                @if (session('status') == 'verification-link-sent')
                  <div class="mb-4 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                  </div>
                @endif

                <div class="mt-4 flex items-center justify-between">
                  <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <div>
                      <x-primary-button>
                        {{ __('Resend Verification Email') }}
                      </x-primary-button>
                    </div>
                  </form>

                  <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                      class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                      {{ __('Log Out') }}
                    </button>
                  </form>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>

</html>
