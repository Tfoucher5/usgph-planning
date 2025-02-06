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
                <h1>Mot de passe oublié</h1>

                <form method="POST" action="{{ route('password.email') }}">
                  @csrf

                  <x-inputs.input-email property="email" label="<i class='fa-light fa-at me-3'></i>Email" :entity="null" required="true" autofocus="true" />

                  <div class="row">
                    <div class="col-12">
                      <button class="btn btn-primary px-4" type="submit ">{{ __('Email Password Reset Link') }}</button>
                    </div>
                  </div>

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
