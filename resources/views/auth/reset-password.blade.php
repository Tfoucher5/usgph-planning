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

                <form method="POST" action="{{ route('password.store') }}">
                  @csrf

                  <!-- Password Reset Token -->
                  <input type="hidden" name="token" value="{{ $request->route('token') }}">

                  <h1>Définir votre nouveau mot de passe</h1>

                  <x-inputs.input-email property="email" label="<i class='fa-light fa-at me-3'></i>Email" :entity="null" required="true" autofocus="true" />

                  <x-inputs.input-password property="password" label="<i class='fa-light fa-unlock-keyhole me-3'></i>Mot de passe" :entity="null" required="true" />

                  <x-inputs.input-password property="password_confirmation" label="<i class='fa-light fa-unlock-keyhole me-3'></i>Mot de passe" :entity="null" required="true" />

                  <div class="row">
                    <div class="col-12">
                      <button class="btn btn-primary px-4" type="submit ">{{ __('Reset Password') }}</button>
                    </div>
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
