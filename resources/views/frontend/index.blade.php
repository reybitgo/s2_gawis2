@extends('layouts.frontend')

@section('content')

    <!-- Hero Section Start -->
    <div class="hero dark-section">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- Hero Content Start -->
                    <div class="hero-content">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h3 class="wow fadeInUp">Welcome to Gawis iHerbal</h3>
                            <h1 class="text-anime-style-3" data-cursor="-opaque">
                                Your Path to <span>Financial Wellness</span>
                            </h1>
                        </div>
                        <!-- Section Title End -->

                        <!-- Hero List Start -->
                        <div class="hero-list wow fadeInUp" data-wow-delay="0.2s">
                            <ul>
                                <li>
                                    Unlock your earning potential with our Unilevel MLM plan.
                                </li>
                                <li>
                                    High-quality products and a rewarding compensation plan.
                                </li>
                            </ul>
                        </div>
                        <!-- Hero List End -->

                        <!-- Hero Button Start -->
                        <div class="hero-btn wow fadeInUp" data-wow-delay="0.4s">
                            <a href="/opportunity" class="btn-default btn-highlighted">Learn More</a>
                            <a href="/contact" class="btn-default border-btn">Contact Us</a>
                        </div>
                        <!-- Hero Button End -->
                    </div>
                    <!-- Hero Content End -->
                </div>

                <div class="col-lg-6">
                    <!-- Hero Image Start -->
                    <div class="hero-image">
                        <figure>
                            <img src="{{ asset('frontend/images/hero-image.png') }}" alt="" />
                        </figure>
                    </div>
                    <!-- Hero Image End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Section End -->

    <!-- Our Scrolling Ticker Section Start -->
    <div class="our-scrolling-ticker">
        <!-- Scrolling Ticker Start -->
        <div class="scrolling-ticker-box">
            <div class="scrolling-content">
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />5-Level Commission Structure</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Unilevel Bonus System</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Real-Time Processing</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Integrated E-Wallet</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Premium Quality Products</span>
            </div>

            <div class="scrolling-content">
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />5-Level Commission Structure</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Unilevel Bonus System</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Real-Time Processing</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Integrated E-Wallet</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" />Premium Quality Products</span>
            </div>
        </div>
    </div>
    <!-- Our Scrolling Ticker Section End -->

    <!-- About Us Section Start -->
    <div class="about-us">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- About Images Start -->
                    <div class="about-images">
                        <figure>
                            <img src="{{ asset('frontend/images/about-img-3.png') }}" alt="" />
                        </figure>
                    </div>
                    <!-- About Images End -->
                </div>

                <div class="col-lg-6">
                    <!-- About us Content Start -->
                    <div class="about-us-content">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h3 class="wow fadeInUp">about us</h3>
                            <h2 class="text-anime-style-3" data-cursor="-opaque">
                                Committed to your <span>health and success!</span>
                            </h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">
                                We provide a unique opportunity for individuals to build their own business through our Unilevel MLM platform, offering high-quality health and wellness products.
                            </p>
                        </div>
                        <!-- Section Title End -->

                        <!-- About Us Body Start -->
                        <div class="about-us-body wow fadeInUp" data-wow-delay="0.4s">
                            <!-- About Us List Start -->
                            <div class="about-us-list">
                                <ul>
                                    <li>Pure & Natural Ingredients</li>
                                    <li>Rewarding Compensation Plan</li>
                                </ul>
                            </div>
                            <!-- About Us List End -->

                            <!-- About Body Item Start -->
                            <div class="about-body-item">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-about-body.svg') }}" alt="" />
                                </div>
                                <div class="about-body-item-title">
                                    <h3>100% Natural & Pure Ingredients</h3>
                                </div>
                            </div>
                            <!-- About Body Item End -->
                        </div>
                        <!-- About Us Body End -->

                        <!-- About Us Footer Start -->
                        <div class="about-us-footer wow fadeInUp" data-wow-delay="0.6s">
                            <!-- About Us Button Start -->
                            <div class="about-us-btn">
                                <a href="/about" class="btn-default">more about us</a>
                            </div>
                            <!-- About Us Button End -->

                            <!-- About Contact Box Start -->
                            <div class="about-contact-box">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-mail.svg') }}" alt="" />
                                </div>
                                <div class="about-contact-box-content">
                                    <p>Support Any Time</p>
                                    <h3>
                                        <a href="mailto:support@gawisherbal.com"><small>support@gawisherbal.com</small></a>
                                    </h3>
                                </div>
                            </div>
                            <!-- About Contact Box End -->
                        </div>
                        <!-- About Us Footer End -->
                    </div>
                    <!-- About us Content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- About Us Section End -->

    <!-- Pricing Section Start -->
    <section id="pricing" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-3 fw-bold mb-3">The Gawis Package</h2>
                <p class="lead text-muted mb-4">Your first step to financial wellness.</p>
            </div>

            <div class="row g-4 mb-5 justify-content-center">
                @foreach ($packages as $package)
                <div class="col-lg-4">
                    <div class="pricing-card">
                        <div class="pricing-header @if ($package->image_path && strpos($package->image_url, 'package-placeholder.svg') === false) has-image @endif" style="height: 200px; overflow: hidden; @if ($package->image_path && strpos($package->image_url, 'package-placeholder.svg') === false) padding: 0; background: none; @endif">
                            @if ($package->image_path && strpos($package->image_url, 'package-placeholder.svg') === false)
                                <img src="{{ $package->image_url }}" alt="{{ $package->name }}" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                            @else
                                <div class="pricing-icon">
                                    <i class="fas fa-seedling"></i>
                                </div>
                                <h3 class="pricing-title">{{ $package->name }}</h3>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            @if ($package->image_path && strpos($package->image_url, 'package-placeholder.svg') === false)
                                <h3 class="pricing-title text-center">{{ $package->name }}</h3>
                            @endif
                            <div class="pricing-price">
                                {{ $package->getFormattedPriceAttribute() }}
                            </div>
                            @if (isset($package->meta_data['features']))
                            <ul class="pricing-features">
                                @foreach ($package->meta_data['features'] as $feature)
                                    <li><i class="fas fa-check-circle"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                            @endif
                            <a href="#" class="btn btn-outline-gawis w-100 mt-3 view-details-btn-package" 
                                data-package-name="{{ $package->name }}" 
                                data-mlm-settings='{{ json_encode($package->mlmSettings) }}'>View Details</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- Pricing Section End -->

    <!-- Our Products Section Start -->
    <div class="our-products">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-6">
                    <!-- Section Title Start -->
                    <div class="section-title">
                        <h3 class="wow fadeInUp">our products</h3>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">
                            Powerful supplements <span>for a healthier you!</span>
                        </h2>
                    </div>
                    <!-- Section Title End -->
                </div>

                <div class="col-lg-6">
                    <!-- Section Button Start -->
                    <div class="section-btn wow fadeInUp" data-wow-delay="0.2s">
                        <a href="{{ route('frontend.our-products') }}" class="btn-default">view all products</a>
                    </div>
                    <!-- Section Button End -->
                </div>
            </div>

            <div class="row">
                @foreach ($topProducts as $index => $product)
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-item wow fadeInUp h-100 d-flex flex-column">
                            <div class="product-image" style="height: 150px; overflow: hidden; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#productCarouselModal" data-bs-slide-to="{{ $index }}" data-bs-long-description="{{ $product->formatted_long_description }}">
                                <figure class="image-anime h-100">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover; object-position: center;" />
                                </figure>
                            </div>
                            <div class="product-title mb-0">
                                <h3>{{ $product->name }}</h3>
                            </div>
                            <div class="product-item-body d-flex flex-column flex-grow-1">
                                <ul class="pricing-features mb-2">
                                    <li><i class="fas fa-check-circle"></i> Price: {{ currency($product->price) }}</li>
                                    <li><i class="fas fa-check-circle"></i> Points: {{ number_format($product->points_awarded) }}</li>
                                    @if($product->total_unilevel_bonus > 0)
                                    <li><i class="fas fa-check-circle"></i> Bonus: {{ currency($product->total_unilevel_bonus) }}</li>
                                    @endif
                                </ul>
                                @if(!$product->unilevelSettings->isEmpty())
                                <div class="mt-auto">
                                    <a href="#" class="btn btn-outline-gawis w-100 view-details-btn-product" 
                                        data-product-name="{{ $product->name }}"
                                        data-product-short-description="{{ $product->short_description }}"
                                        data-unilevel-settings='{{ json_encode($product->unilevelSettings) }}'>View Details</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <!-- Our Product Section End -->

    <!-- Product Carousel Modal -->
    <div class="modal fade" id="productCarouselModal" tabindex="-1" aria-labelledby="productCarouselModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productCarouselModalLabel">Product Images</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <a href="#" id="learnMoreCarousel" class="btn btn-link float-end ms-auto">Learn More</a>
                </div>
                <div class="modal-body">
                    <div id="productCarousel" class="carousel slide">
                        <div class="carousel-indicators">
                            @foreach ($topProducts as $index => $product)
                                <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" aria-current="{{ $index == 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                        <div class="carousel-inner">
                            @foreach ($topProducts as $index => $product)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}" data-bs-long-description="{{ $product->formatted_long_description }}">
                                    <img src="{{ $product->image_url }}" class="d-block w-100" alt="{{ $product->name }}">
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5 style="color: #f8f9fa;">{{ $product->name }}</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    <div id="carouselProductDescription" class="mt-3 p-3 bg-light rounded d-none prose">
                        <!-- Long description will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel"><i class="fas fa-info-circle me-2"></i>Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-7">
                                <h3 id="modalProductName"></h3>
                                <p id="modalProductShortDescription" class="text-muted mb-3"></p>
                                <hr>
                                <h4><i class="fas fa-sitemap me-2"></i>Unilevel Bonus Settings</h4>
                                <p class="text-muted">This table shows the unilevel bonus you can earn from this product.</p>
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Level</th>
                                            <th>Bonus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalUnilevelSettings">
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-lg-5">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h4><i class="fas fa-shopping-cart me-2"></i>How to Purchase</h4>
                                        <hr>
                                        <div class="alert alert-success">
                                            <i class="fas fa-gift me-2"></i>
                                            <strong>Your dashboard and system access are completely free!</strong>
                                            <p class="mt-2">Simply fund your e-wallet to purchase products and start your journey to financial wellness.</p>
                                        </div>
                                        <ol class="list-group list-group-numbered">
                                            <li class="list-group-item"><strong>Register for a Free Account:</strong> <a href="{{ route('register') }}">Click here to register</a>.</li>
                                            <li class="list-group-item"><strong>Fund Your E-Wallet:</strong> Once registered, log in to your dashboard and add funds to your secure e-wallet.</li>
                                            <li class="list-group-item"><strong>Purchase Products:</strong> With a funded e-wallet, you can purchase your desired products directly from your dashboard.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default border-btn" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('register') }}" class="btn-default btn-highlighted"><i class="fas fa-user-plus me-2"></i>Register Now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Details Modal -->
    <div class="modal fade" id="packageDetailsModal" tabindex="-1" aria-labelledby="packageDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="packageDetailsModalLabel"><i class="fas fa-info-circle me-2"></i>Package Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-7">
                                <h3 id="modalPackageName"></h3>
                                <hr>
                                <h4><i class="fas fa-sitemap me-2"></i>MLM Commission Settings</h4>
                                <p class="text-muted">This table shows the commission you can earn from your network for this package.</p>
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Level</th>
                                            <th>Commission</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalMlmSettings">
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-lg-5">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h4><i class="fas fa-shopping-cart me-2"></i>How to Purchase</h4>
                                        <hr>
                                        <div class="alert alert-success">
                                            <i class="fas fa-gift me-2"></i>
                                            <strong>Your dashboard and system access are completely free!</strong>
                                            <p class="mt-2">Simply fund your e-wallet to purchase a package and start your journey to financial wellness.</p>
                                        </div>
                                        <ol class="list-group list-group-numbered">
                                            <li class="list-group-item"><strong>Register for a Free Account:</strong> <a href="{{ route('register') }}">Click here to register</a>.</li>
                                            <li class="list-group-item"><strong>Fund Your E-Wallet:</strong> Once registered, log in to your dashboard and add funds to your secure e-wallet.</li>
                                            <li class="list-group-item"><strong>Purchase Your Package:</strong> With a funded e-wallet, you can purchase your desired package directly from your dashboard.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default border-btn" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('register') }}" class="btn-default btn-highlighted"><i class="fas fa-user-plus me-2"></i>Register Now</a>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var productDetailsModal = new bootstrap.Modal(document.getElementById('productDetailsModal'));

    document.querySelectorAll('.view-details-btn-product').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            var productName = this.dataset.productName;
            var productShortDescription = this.dataset.productShortDescription;
            var unilevelSettings = JSON.parse(this.dataset.unilevelSettings);

            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductShortDescription').textContent = productShortDescription;

            var unilevelSettingsTbody = document.getElementById('modalUnilevelSettings');
            unilevelSettingsTbody.innerHTML = '';

            if (unilevelSettings && unilevelSettings.length > 0) {
                unilevelSettings.forEach(function(setting) {
                    var row = `<tr>
                        <td>Level ${setting.level}</td>
                        <td>${currency(setting.bonus_amount)}</td>
                    </tr>`;
                    unilevelSettingsTbody.innerHTML += row;
                });
            } else {
                var row = `<tr>
                    <td colspan="2" class="text-center">No unilevel settings configured for this product.</td>
                </tr>`;
                unilevelSettingsTbody.innerHTML = row;
            }

            productDetailsModal.show();
        });
    });

    function currency(amount) {
        return '₱' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var packageDetailsModal = new bootstrap.Modal(document.getElementById('packageDetailsModal'));

    document.querySelectorAll('.view-details-btn-package').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            var packageName = this.dataset.packageName;
            var mlmSettings = JSON.parse(this.dataset.mlmSettings);

            document.getElementById('modalPackageName').textContent = packageName;

            var mlmSettingsTbody = document.getElementById('modalMlmSettings');
            mlmSettingsTbody.innerHTML = '';

            mlmSettings.forEach(function(setting) {
                var row = `<tr>
                    <td>Level ${setting.level}</td>
                    <td>${setting.commission_amount}</td>
                </tr>`;
                mlmSettingsTbody.innerHTML += row;
            });

            packageDetailsModal.show();
        });
    });
});

var productCarouselModalElement = document.getElementById('productCarouselModal');
var productCarouselElement = document.getElementById('productCarousel');
var carouselProductDescription = document.getElementById('carouselProductDescription');
var learnMoreCarouselLink = document.getElementById('learnMoreCarousel');

// Function to toggle description visibility
function toggleDescription(descriptionElement) {
    descriptionElement.classList.toggle('d-none');
}

// Initialize carousel instance once
var productCarouselInstance = new bootstrap.Carousel(productCarouselElement, {
    interval: false // Disable auto-play
});

productCarouselModalElement.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget; // Button that triggered the modal
    var slideTo = button.getAttribute('data-bs-slide-to'); // Extract info from data-bs-slide-to attribute
    
    productCarouselInstance.to(slideTo); // Go to the slide

    // Get the long description from the clicked product and display it
    var longDescription = button.getAttribute('data-bs-long-description');
    carouselProductDescription.innerHTML = longDescription;

    // Ensure description is hidden when modal opens
    carouselProductDescription.classList.add('d-none');
});

productCarouselElement.addEventListener('slid.bs.carousel', function () {
    var activeItem = productCarouselElement.querySelector('.carousel-item.active');
    var longDescription = activeItem.getAttribute('data-bs-long-description');
    carouselProductDescription.innerHTML = longDescription;
});

// Event listener for "Learn More" link
learnMoreCarouselLink.addEventListener('click', function(event) {
    event.preventDefault();
    toggleDescription(carouselProductDescription);
});

// Event listener for clicks on carousel images
productCarouselElement.addEventListener('click', function(event) {
    // Check if the click was on an image within the carousel item
    if (event.target.tagName === 'IMG' && event.target.closest('.carousel-item')) {
        toggleDescription(carouselProductDescription);
    }
});
</script>
@endpush

@endsection