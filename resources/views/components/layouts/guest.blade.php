<x-layouts.base>
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-75">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                            <div class="card card-plain mt-8">
                                <div class="card-header pb-0 text-left bg-transparent">
                                    <div class="text-center mb-3">
                                        <h3 class="text-primary text-gradient font-weight-bolder">Control Finance</h3>
                                        <p class="mb-0">Sistema de Control de Productos Financieros</p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if (session()->has('message'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <span class="alert-text text-white">{{ session('message') }}</span>
                                        </div>
                                    @endif

                                    @if (session()->has('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <span class="alert-text text-white">{{ session('error') }}</span>
                                        </div>
                                    @endif

                                    {{ $slot }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('/assets/img/curved-images/curved6.jpg')"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-layouts.base>
