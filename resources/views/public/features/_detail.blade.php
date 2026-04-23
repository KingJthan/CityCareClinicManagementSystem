<section class="public-band public-band-soft">
    <div class="container-fluid px-3 px-lg-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <div class="panel panel-pad h-100">
                    <p class="eyebrow">{{ $eyebrow }}</p>
                    <h1 class="section-title">{{ $title }}</h1>
                    <p class="section-copy mb-0">{{ $lead }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-dark btn-lg" href="{{ $primaryRoute }}">{{ $primaryLabel }}</a>
                        <a class="btn btn-outline-secondary btn-lg" href="{{ route('services') }}">All services</a>
                        <a class="btn btn-outline-secondary btn-lg" href="{{ route('home') }}">Back home</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <article class="media-card h-100">
                    <img src="{{ asset($image) }}" alt="{{ $imageAlt }}">
                    <div class="media-card-body">
                        <p class="eyebrow">CityCare focus</p>
                        <h2 class="h4">{{ $imageTitle }}</h2>
                        <p class="mb-0">{{ $imageCopy }}</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="public-band">
    <div class="container-fluid px-3 px-lg-4">
        <div class="row g-4 align-items-start mb-2">
            <div class="col-lg-6">
                <p class="eyebrow">What this supports</p>
                <h2 class="section-title">Built for real CityCare workflows</h2>
            </div>
            <div class="col-lg-6">
                <p class="section-copy mb-0">{{ $supportCopy }}</p>
            </div>
        </div>

        <div class="row g-4 mt-1">
            @foreach($points as $point)
                <div class="col-md-6">
                    <div class="feature-point h-100">
                        <div class="fw-bold mb-2">{{ $point['title'] }}</div>
                        <div>{{ $point['copy'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="public-band public-band-soft">
    <div class="container-fluid px-3 px-lg-4">
        <div class="row g-4">
            @foreach($details as $detail)
                <div class="col-lg-4">
                    <article class="media-card h-100">
                        <div class="media-card-body">
                            <p class="eyebrow">{{ $detail['eyebrow'] }}</p>
                            <h3>{{ $detail['title'] }}</h3>
                            <p class="mb-0">{{ $detail['copy'] }}</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="public-band public-cta-band">
    <div class="container-fluid px-3 px-lg-4">
        <div class="public-cta">
            <div>
                <p class="eyebrow text-white-50">CityCare next step</p>
                <h2>{{ $ctaTitle }}</h2>
                <p class="mb-0">{{ $ctaCopy }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-light btn-lg" href="{{ $primaryRoute }}">{{ $primaryLabel }}</a>
                <a class="btn btn-outline-light btn-lg" href="{{ route('contact') }}">Book or inquire</a>
            </div>
        </div>
    </div>
</section>
