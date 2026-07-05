<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('location.choose_location') }} - {{ config('app.name', 'Speeda') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/New_logo.png') }}">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --card-hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /* Modal z-index fix */
        .modal {
            z-index: 10000 !important;
        }

        .modal-backdrop {
            z-index: 9999 !important;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark-text);
            line-height: 1.6;
            background-color: #f8f9fa;
        }

        /* Navigation */
        .navbar-brand img {
            width: 120px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        /* City Cards */
        .city-card {
            text-decoration: none;
            color: inherit;
            transition: all 0.4s ease;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            position: relative;
            height: 300px;
            display: block;
            margin-bottom: 30px;
        }

        .city-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
            color: inherit;
            text-decoration: none;
        }

        .city-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .city-card:hover img {
            transform: scale(1.1);
        }

        .city-card h2 {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 20px;
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }

        .city-card:hover h2 {
            padding-bottom: 25px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0.75rem 0;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #0056b3;
        }

        /* Page Header */
        .page-header {
            padding: 2rem 0;
            text-align: center;
        }

        .page-header h1 {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-brand img {
                width: 100px;
            }

            .city-card {
                height: 250px;
                margin-bottom: 20px;
            }

            .city-card h2 {
                font-size: 1.5rem;
                padding: 15px;
            }

            .btn-primary {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .city-card {
                height: 200px;
            }

            .city-card h2 {
                font-size: 1.2rem;
                padding: 10px;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .page-header p {
                font-size: 1rem;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .city-card {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
@include('components.main-nav')

@php
use Illuminate\Support\Facades\Storage;
@endphp

<div class="container mt-4">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>{{ __('general.home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-map-marker-alt me-1"></i>{{ __('location.choose_location_breadcrumb') }}</li>
        </ol>
    </nav>

    <!-- Cities Grid (dynamic from DB) -->
    <div class="row">
        @forelse($cities as $index => $loc)
            <div class="col-lg-6 mb-4">
                @php
                    $img = null;
                    try {
                        $img = $loc->image ? Storage::url($loc->image) : null;
                    } catch (\Exception $e) {
                        $img = null;
                    }
                    $bg = $img ? $img : ('https://via.placeholder.com/1200x800?text=' . urlencode($loc->city));
                @endphp

                <a href="{{ route('categories', ['city' => $loc->city]) }}"
                   class="city-card"
                   style="background-image: url('{{ $bg }}'); background-size: cover; background-position: center;">
                    <div style="position:absolute; inset:0; background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.55) 100%);"></div>
                    <h2 style="position:absolute; left:1rem; bottom:1rem; color:#fff; margin:0;">{{ $loc->localized_name }}</h2>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">{{ __('location.no_cities_available') ?? 'No cities available.' }}</p>
            </div>
        @endforelse
    </div>

    <!-- Call to Action Section -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="bg-primary-light rounded p-5">
                <h3 class="mb-3">{{ __('location.cant_find_city') }}</h3>
                <p class="mb-4">{{ __('location.expanding_message') }}</p>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#locationModal">
                    <i class="fas fa-map-marker-alt me-2"></i> {{ __('location.request_new_location') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Location Suggestion Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="locationModalLabel">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    {{ __('location.suggest_new_location') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="locationForm">
                    <div class="mb-3">
                        <label for="locationName" class="form-label fw-bold">
                            <i class="fas fa-city me-1"></i>
                            {{ __('location.location_name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="locationName" placeholder="{{ __('location.location_name_placeholder') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="locationDetails" class="form-label fw-bold">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('location.additional_details') }}
                            <span class="text-muted small">({{ __('general.optional') }})</span>
                        </label>
                        <textarea class="form-control" id="locationDetails" rows="3" placeholder="{{ __('location.details_placeholder') }}"></textarea>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fab fa-facebook-messenger me-2"></i>
                        <strong>{{ __('categories.note') ?? 'Note' }}:</strong>
                        {{ __('location.messenger_redirect_info') ?? 'You will be redirected to Messenger to send your suggestion.' }}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('general.cancel') }}
                </button>
                <button type="button" class="btn btn-primary" onclick="submitLocationSuggestion()">
                    <i class="fab fa-facebook-messenger me-1"></i>
                    {{ __('location.send_via_messenger') ?? 'Send via Messenger' }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Location page loaded successfully');

        // Add loading animation to city cards
        const cityCards = document.querySelectorAll('.city-card');
        cityCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Add image loading error handling
        const images = document.querySelectorAll('.city-card img');
        images.forEach(img => {
            img.addEventListener('error', function() {
                this.src = 'https://via.placeholder.com/600x400/007bff/ffffff?text=City+Image';
            });
        });
    });

    function submitLocationSuggestion() {
        const locationName = document.getElementById('locationName').value;
        const locationDetails = document.getElementById('locationDetails').value;

        if (!locationName) {
            alert('{{ __("validation.fill_required_fields") ?? "Please fill in all required fields" }}');
            return;
        }

        // Build Messenger message
        const message = `{{ __('location.new_location_suggestion') ?? 'New Location Suggestion' }}:\n${locationName}${locationDetails ? '\n' + locationDetails : ''}`;

        // Open Messenger with pre-filled message
        const messengerUrl = `https://m.me/61583422931690?text=${encodeURIComponent(message)}`;
        window.open(messengerUrl, '_blank');

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('locationModal'));
        modal.hide();

        // Reset form
        document.getElementById('locationForm').reset();
    }
</script>

@include('layouts.footer')

</body>
</html>
