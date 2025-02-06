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

                <form method="POST" action="{{ route('password.confirm') }}">
                  @csrf

                  <!-- Password -->
                  <div>
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                  </div>

                  <div class="flex justify-end mt-4">
                    <x-primary-button>
                      {{ __('Confirm') }}
                    </x-primary-button>
                  </div>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>

</html>
